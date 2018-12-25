<?php
namespace app\home\model;

use think\Db;

class PaymentsModel{
    /**
     * 单例模式
     * @return BannerModel
     *
     */
    public static function instance()
    {
        static $m = null;
        if (!$m) {
            $m = new PaymentsModel();
        }
        return $m;
    }

    public function paylist($bid,$field){
        $list = Db::name('business')->where('bid',$bid)->field($field)->select();
        return $list;
    }


    public function payonelist($id){
        $list = Db::name('business')->where('bid',$id)->find();
        return $list;
    }
    
    public function payeditlist($bid,$data){
        $list = Db::name('business')->where('bid',$bid)->update($data);
        return $list;
    }

}
