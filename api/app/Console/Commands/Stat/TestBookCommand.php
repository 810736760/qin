<?php

namespace App\Console\Commands\Stat;

use App\Helper\Tool;
use App\Models\Facebook\FacebookDayAdSetData;
use App\Models\Facebook\FacebookSet;
use App\Models\Stat\AbroadLink;
use App\Models\Stat\DayAdSetsSummaryData;
use App\Models\TestBook\BookTestList;
use App\Models\TestBook\BookTestRecord;
use App\Services\CompanyService;
use App\Services\Facebook\BMService;
use App\Services\QN\BaseService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TestBookCommand extends Command
{
    protected $signature = 'testBook';

    protected $description = '测试统计书籍';


    public function handle()
    {
        $args = $this->arguments();
        Log::info($this->description . "开始执行", $args);
        $startTime = microtime(true);
        foreach (CompanyService::coList(1) as $row) {
            $GLOBALS['co'] = $row['id'];
            $packageInfo = BMService::getAllPackage();
            $changeTimezone = array_column($packageInfo, 'stat_timezone', 'platform');
            $curTimeZone = config('app.timezone_offset');
            // 1、抓取最新link数据 将book存到新书表
            $this->fetchBookFromLink();
            // 2.1、检测新书表未检测过的书籍 判定书的级别
            // 2.2、检测之前新书无消耗的消耗情况
            $this->checkBook($changeTimezone, $curTimeZone);
            // 3、检测之前新书在消耗的消耗 是否到结算时间 默认14天 若到了结算时间，重新统计消耗 置为已结算
            $this->statBook($changeTimezone, $curTimeZone);
        }

        Log::info($this->description . "use time:" . (microtime(true) - $startTime), $args);
    }

    // 今天新建的 由于有时区问题 就拉两天内的数据
    public function fetchBookFromLink()
    {
        $today = date("Ymd", strtotime('-1 days'));
        $rs = AbroadLink::getIns()->listByCond(['updated_at' => ['>', $today]], ['book_id', 'platform']);
        if (empty($rs)) {
            return;
        }

        $bookRs = BookTestList::getIns()->listByCond(['book_id' => ['in', array_column($rs, 'book_id')]]);
        $bookMap = [];
        foreach ($bookRs as $row) {
            $bookMap[$row['book_id'] . '_' . $row['platform']] = 1;
        }

        $list = array_filter($rs, function ($row) use (&$bookMap) {
            if ($bookMap && Tool::get($bookMap, $row['book_id'] . '_' . $row['platform'])) {
                return false;
            }
            $bookMap[$row['book_id'] . '_' . $row['platform']] = 1;
            return true;
        });
        if ($list) {
            BookTestList::getIns()->insert($list);
        }
    }

    public function checkBook($changeTimezone, $curTimeZone)
    {
        $rs = BookTestList::getIns()->listByCond(['book_type' => ['in', [BookTestList::TYPE_BOOK_UNKNOWN, BookTestList::TYPE_BOOK_NEW_WITHOUT_SPEND]]]);
        if (empty($rs)) {
            return;
        }


        $bookIds = array_column($rs, 'book_id');
        $dataRs = DayAdSetsSummaryData::getIns()
            ->select(
                'book_id',
                'event_date',
                'platform',
                // DB::raw('sum(spend) as price'),
                DB::raw("concat(book_id,'_', platform) as sign"),
                'spend'
            )
            ->whereIn('book_id', $bookIds)
            ->where('spend', '>', 0)
            ->where('from', BaseService::PLATFORM_TYPE_FACEBOOK)
            ->orderBy('event_date')
            ->groupBy(['book_id', 'platform'])
            ->get()->toArray();


        $dataMap = array_column($dataRs, 'event_date', 'sign');
        // 处理每一本书籍
        foreach ($rs as $one) {
            $update = [];
            $date = Tool::get($dataMap, $one['book_id'] . '_' . $one['platform']);
            if ($date) {
                // 校验是不是今天的花费
                $userDate = Tool::getTodayDateWithTimeZone(
                    Tool::get($changeTimezone, $one['platform']),
                    $curTimeZone,
                    'Ymd'
                );
                if ($date < $userDate) {
                    $update['book_type'] = BookTestList::TYPE_BOOK_OLD;
                } else {
                    $update['book_type'] = BookTestList::TYPE_BOOK_NEW;
                    $update['start_date'] = $date;
                    $update['end_date'] = date("Ymd", strtotime($date) + BookTestList::TEST_RANGE * 86400);
                }
            } else {
                $update['book_type'] = BookTestList::TYPE_BOOK_NEW_WITHOUT_SPEND;
            }
            BookTestList::getIns()->updateByCond(['book_id' => $one['book_id'], 'platform' => $one['platform']], $update);
        }
    }

    public function statBook($changeTimezone, $curTimeZone)
    {
        $rs = BookTestList::getIns()->listByCond(['book_type' => BookTestList::TYPE_BOOK_NEW, 'is_finish' => BookTestList::TYPE_IS_NOT_FINISH]);
        if (empty($rs)) {
            return;
        }
        foreach ($rs as $row) {
            // 判断当前是否是最后一天
            $userDate = Tool::getTodayDateWithTimeZone(
                Tool::get($changeTimezone, $row['platform']),
                $curTimeZone,
                'Ymd'
            );

            // 结束测试
            if ($row['end_date'] <= $userDate) {
                $this->reStat($row);
            } else {
                $this->record($row['book_id'], $row['platform'], $userDate, $userDate - intval($row['start_date']));
            }
        }
    }

    // 一本书周期结束后 重新统计一边 封存
    public function reStat($row)
    {
        BookTestList::getIns()->updateByCond(['book_id' => $row['book_id'], 'platform' => $row['platform']], [
            'is_finish' => BookTestList::TYPE_IS_FINISH
        ]);
    }

    public function record($bookId, $platform, $date, $indexDate)
    {
        BookTestRecord::getIns()->delByCond(
            ['book_id' => $bookId, 'platform' => $platform, 'event_date' => $date]
        );

        $rs = FacebookDayAdSetData::getIns()
            ->from(FacebookDayAdSetData::getIns()->getTableName() . ' as setsData')
            ->leftJoin(FacebookSet::getIns()->getTableName() . ' as sets', 'sets.sid', 'setsData.sid')
            ->select(
                'sets.book_id',
                'sets.link_id',
                'sets.union_link_id',
                'sets.user',
                'sets.platform',
                'setsData.event_date',
                DB::raw('sum(spend) as spend'),
                DB::raw('sum(revenue) as revenue'),
                DB::raw('sum(purchase) as purchase'),
                DB::raw('sum(install) as install'),
                DB::raw('sum(uniq_purchase) as uniq_purchase'),
                DB::raw('sum(impressions) as impressions'),
                DB::raw('sum(clicks) as clicks'),
                DB::raw('sum(revenue_0) as revenue_0')
            )
            ->where('sets.book_id', $bookId)
            ->where('sets.platform', $platform)
            ->where('event_date', $date)
            ->groupBy(['sets.book_id', 'sets.union_link_id'])
            ->get()
            ->toArray();
        if (empty($rs)) {
            return;
        }
        foreach ($rs as &$row) {
            $row['index_date'] = $indexDate;
        }
        BookTestRecord::getIns()->insert($rs);
    }
}
