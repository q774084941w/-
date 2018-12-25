<?php
namespace app\home\model;

use think\Db;

class BusinessModel{
    /**
     * 单例模式
     * @return BannerModel
     *
     */
    public static function instance()
    {
        static $m = null;
        if (!$m) {
            $m = new BusinessModel();
        }
        return $m;
    }

    public function buslist($bid,$field){
        $list = Db::name('business')->where('bid',$bid)->field($field)->select();
        return $list;
    }


    public function busonelist($id){
        $list = Db::name('business')->where('bid',$id)->find();
        return $list;
    }


    public function buseditlist($bid,$data){
        $list = Db::name('business')->where('bid',$bid)->update($data);
        return $list;
    }

    public static function getOpenTime ($bid) {
        $result = Db::name('business')
            -> where('bid',$bid)
            -> field('openTime,closeTime')
            -> find();
        return $result;
    }

}