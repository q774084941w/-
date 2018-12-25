<?php
namespace app\home\model;

use think\Db;

class ExpressModel
{
    /**
     * 单例模式
     * @return ExpressModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new ExpressModel();
        }
        return $m;
    }
    /**
     * 快递列表
     */
    public function exprlist($bid){
        $list = Db::name('express')->field('exid,bid,name,status,createtime')->where(['bid'=>$bid,'status'=>1])->order('createtime desc')->select();
        return $list;
    }
    /**
     * 删除
     */
    public function exdelete($id){
        $result = Db::name('express')->delete($id);
        return $result;
    }
    /**
     * 添加数据库
     */
    public function exinsert($name){
        $result = Db::name('express')->insert($name);
        return $result;
    }
    


}
