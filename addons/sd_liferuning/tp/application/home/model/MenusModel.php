<?php
namespace app\home\model;

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
            $val['logo'] = uploadpath('commtype',$val['logo']);
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
     * 编辑
     *
     */
    public function edit($id){
        $field = 'tid,name,logo,solt,status,createtime';
        $list = Db::name('menus')->field($field)->where('tid',$id['id'])->find();
        $list['logo'] = uploadpath('commtype',$list['logo']);
        return $list;
    }
    /**
     * 添加数据库  编辑
     */
    public function editinse($data){
        $result = Db::name('menus')->update($data);
        return $result;
    }
 
    

}
