<?php
namespace app\home\model;

use think\Db;

class HzpnewsModel  
{
    /**
     * 单例模式
     * @return HzpnewsModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new HzpnewsModel();
        }
        return $m;
    }
    /**
     * 新闻列表
     */
    public function newslist($bid){
        $list = Db::name('hzpNews')->field('neid,bid,title,content,status,createtime,updatetime')->where(['bid'=>$bid,'status'=>0])->paginate(10);
        return $list;
    }
    /**
     * 新闻详情
     */
    public function details($id){
        $result = Db::name('hzpNews')->field('neid,bid,title,status,content,createtime,updatetime')->where(['neid'=>$id,'status'=>0])->find();
        return $result;
    }
    /**
     * 添加数据库
     */
    public function newsinsert($data){
        $result = Db::name('hzpNews')->insert($data);
        return $result;
    }
    /**
     * 编辑
     */
    public function newsedit($data){
        $result = Db::name('hzpNews')->update($data);
        return $result;
    }
    /**
     * 删除
     */
    public function newsdelete($id){
        $result = Db::name('hzpNews')->where('neid',$id)->update(['status'=>-1]);
        return $result;
    }


}
