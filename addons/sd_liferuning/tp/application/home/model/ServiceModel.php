<?php
namespace app\home\model;

use think\Db;

class ServiceModel
{
    /**
     * 单例模式
     * @return UserModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new ServiceModel();
        }
        return $m;
    }
    /**
     * 服务列表
     */
    public function servicelist($bid){
        $list = Db::name('service')->field('id,name,pic,status')->where('bid',$bid)->paginate(10);
        return $list;
    }
    /**
     *  添加
     */
    public function insert($data){
        $list = Db::name('service')->insert($data);
        return $list;
    }
    /**
     * 服务详情
     */
    public function details($id){
        $result = Db::name('service')->field('id,name,pic,title')->where('id',$id)->find();
        return $result;
    }
    /**
     * 上下架
     */
    public function soldOut($type){
        $type = Db::name('service')->where('id',$type['goodsid'])->update(['status'=>$type['status']]);
        return $type;
    }
    /**
     *编辑查询
     */
    public function detail($id){
        $list = Db::name('service')
            ->where('id',$id)
            ->find();
        return $list;
    }
    /*
     * 编辑数据库
     */
    public function editinsert($data,$id){
        $goodsid = Db::name('service')
            ->where('id',$id)
            ->update($data);

        return $goodsid;
    }
    /**
     * 删除
     */
    public function deleteban($id){
        $result = Db::name('service')->where('id',$id)->delete();
        return $result;
    }
}
