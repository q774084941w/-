<?php
namespace app\api\model;
use think\Db;

class BusinessModel{
    /*
     * 单例模式
     * return BusinessModel()
     */
    public static function instance(){
        static $m=null;
        if(!$m){
            $m=new BusinessModel();
        }
        return $m;
    }
    /*
     * 获取商户客服电话
     */
    public function getPhone($bid){
        $list=Db::name('business')->field('phone')->find($bid);
        return $list;
    }
}