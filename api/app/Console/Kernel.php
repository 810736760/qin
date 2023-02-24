<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    const WORK_RANGE = ["8:00", "23:55"]; // 工作时间 - +8时区

    const SLEEP_RANGE = ["0:01", "7:59"]; // 静默时间 - +8时区

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // 同步FB数据
        // 主页
        'App\Console\Commands\Facebook\Page\SyncPageCommand',

    ];

    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // // FB数据同步 Start
        // $schedule->command('Facebook:EveryDayCommand')->hourlyAt("06")->runInBackground(); // 素材采集
        // $schedule->command('facebook:SyncAdAccountInfo')->hourlyAt('02')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步广告账户
        // $schedule->command('facebook:SyncPixel')->hourlyAt('06')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步像素
        // $schedule->command('facebook:SyncAdInfo')->everyThirtyMinutes()->runInBackground(); //同步广告参数信息
        // $schedule->command('facebook:SyncSetData')->everyTenMinutes()->runInBackground(); //同步广告数据
        // $schedule->command('facebook:SyncAdData')->hourlyAt("25")->runInBackground(); //同步广告数据
        // $schedule->command('facebook:SyncShopMenu')->hourlyAt('28')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步商品目录
        // // FB数据同步 End
        //
        // // 同步其他项目数据 Start
        // $schedule->command('facebook:SyncBookSummaryData')->everyThirtyMinutes()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //获取fb书籍数据
        // $schedule->command('SyncCommand')->everyTenMinutes()->runInBackground(); //获取其他项目的数据
        // $schedule->command('SyncLinkCommand')->everyTenMinutes()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //同步link数据
        // // 同步其他项目数据 End
        //
        // // $schedule->command('facebook:SyncPage')->hourly()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步主页、帖子、评论
        // // $schedule->command('facebook:SyncApp')->hourly()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步应用
        // $schedule->command('facebook:ProtectRule')->everyTenMinutes()->runInBackground(); // 投放预警
        //
        // // 同步番茄数据
        // $schedule->command('outside:AmoSync')->everyTenMinutes()->runInBackground();
        // $schedule->command('outside:AmoStat')->everyTenMinutes()->runInBackground();
        //
        // // TT
        // $schedule->command('tiktok:SyncCampaignInfo')->everyThirtyMinutes()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //同步广告参数信息
        // $schedule->command('tiktok:SyncSetData')->everyTenMinutes()->runInBackground(); //同步成效信息
        // $schedule->command('tiktok:SyncPixel')->hourlyAt('05')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步像素
        // // $schedule->command('EveryTTDayCommand')->dailyAt('05:02')->runInBackground(); // 订正数据
        //
        // // Google
        // $schedule->command('google:SyncAdAccountInfo')->hourlyAt('05')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); // 同步广告账户
        // $schedule->command('google:SyncCampaignInfo')->everyThirtyMinutes()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //同步广告参数信息
        // $schedule->command('google:SyncSetData')->everyTenMinutes()->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //同步广告成效数据
        //
        // // 测书
        // $schedule->command('testBook')->everyTenMinutes()->runInBackground();
        //
        // // 多平台数据统计
        // $schedule->command('ReRunSetData')->dailyAt('15:04')->runInBackground(); //重跑数据 保持准确性
        //
        // // 日常监控
        // $schedule->command('task:CheckIllegalStat')->hourlyAt('15')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //检查违规广告
        // // $schedule->command('task:CheckIllegal')->hourlyAt('32')->between(self::WORK_RANGE[0], self::WORK_RANGE[1])->runInBackground(); //检查违规广告
        //
        // $schedule->command('task:Monitor')->everyTenMinutes()->runInBackground(); // 监控超时任务
        // // $schedule->command('task:CheckToken')->everyThirtyMinutes()->runInBackground(); // 监控用户脚本
        // // 日常监控
        //
        // // 提成统计
        // $schedule->command('TimeToCommissionCommand')->monthlyOn(1, '8:30')->runInBackground();
        //
        // // 月结算重跑
        // // $schedule->command('ReRunSetDataMonthly')->monthlyOn(2, '1:30')->runInBackground();
        // $schedule->command('StatMonthlyCommand')->dailyAt('22:30')->runInBackground();
        //
        // $schedule->command('StatShareCommand')->dailyAt('00:02')->runInBackground();
        //
        // // 周日统计
        // $schedule->command('MaterialCheckUseCommand')->sundays()->dailyAt('00:05')->runInBackground();

        // $schedule->command('Test')->dailyAt('13:46')->runInBackground();

        // Google
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
