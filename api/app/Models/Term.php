<?php

namespace App\Models;

use App\Helper\EncryptionHelper;
use App\Helper\Tool;

class Term extends BaseModel
{
    protected $table = 'term';

    protected $guarded = ['id'];

    /**
     * @return Term
     */
    public static function getIns(): Term
    {
        return parent::getInstance();
    }

    public function addTerm()
    {
        $m = date("m");
        $name = '秋季';
        if ($m < 9) {
            $name = '春季';
        }
        $name = date("Y") . $name;
        $rs = $this->getByCond(['name' => $name]);
        if ($rs) {
            return $rs['id'];
        }
        $date = date("Ymd");
        $key = EncryptionHelper::encrypt(
            [
                'event_date' => $date,
                'random'     => Tool::randomNum()
            ]
        );
        $code = implode('', Tool::shortUrl($key));

        $this->updateOrInsert(
            ['name' => $name],
            [
                'event_date' => $date,
                'key'        => $code
            ]
        );
    }
}
