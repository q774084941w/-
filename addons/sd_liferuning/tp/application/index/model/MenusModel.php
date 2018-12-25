<?php
namespace app\index\model;

use think\Controller;
use think\Db;

class MenusModel
{
    /**
     * 单例模式
     * @return MenusModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new MenusModel();
        }
        return $m;
    }
    /**
     * 页面展示
     *
     */
    public function show(){
        $field = 'tid,name,logo,solt,status,createtime';
        $list = Db::name('menus')->field($field)->where('status',1)->order('solt desc')->select();
        foreach ($list as $key=>&$val){
            $val['logo'] = uploadpath('menus',$val['logo']);
        }
        
        return $list;
    }
    /**
     * 添加数据库
     *
     */
    public function insert($data){
        $list = Db::name('menus')->insert($data);
        return $list;
    }
    
    
    /**
     * api 菜单
     */
    public function menus($where,$fieeid,$order){

        $list = Db::name('menus')->field($fieeid)->where($where)->order($order)->select();

        return $list;
    }
    

}
