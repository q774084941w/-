<?php
namespace app\api\model;

use think\Db;

class BannerModel
{
    /**
     * 单例模式
     * @return BannerModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new BannerModel();
        }
        return $m;
    }

    /**
     * banner  api
     */
    public function banlist($bid,$field,$order){
        $list = Db::name('banner')->field($field)->where('bid',$bid)->order($order)->limit(8)->select();
        return $list;
    }
    

}
