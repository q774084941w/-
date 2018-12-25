<?php
namespace app\home\model;

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
     * banner
     */
    public function banlist($bid,$field){
        $list = Db::name('banner')->field($field)->where('bid',$bid)->order('sort desc')->select();
        return $list;
    }
    /**
     * 添加数据库
     */
    public function baninster($data){
        $list = Db::name('banner')->insert($data);
        return $list;
    }
    /**
     * 删除
     */
    public function deleteban($banid){
        $result = Db::name('banner')->delete($banid);
        return $result;
    }
    /*
     *修改查询
     */
    public function getban($banid){
        $result = Db::name('banner')->find($banid);
        return $result;
    }
    /*
     * 修改数据库信息并删除原图
     */
    public function saveban($banid,$data){
        $result=Db::name('banner')->where('banid',$banid)->update($data);
        return $result;
    }

}
