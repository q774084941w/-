<?php
namespace app\home\model;

use think\Db;

class RewardModel
{
    /**
     * 单例模式
     * @return BannerModel
     *
     */
    public static function instance()
    {
        static $m = null;
        if (!$m) {
            $m = new RewardModel();
        }
        return $m;
    }

    public function recordlist($bid, $field)
    {
        $list = Db::name('goods_order')->where('bid', $bid)->where('status','neq','1')->field($field)->order('paytime desc')->paginate(10);
        return $list;
    }
}