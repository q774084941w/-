<?php

namespace app\api\model;


use think\Model;

class SellerModel extends Model
{
    /**
     * 单例模式
     * @return SellerModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new SellerModel();
        }
        return $m;
    }

    /**
     * 获取
     * @param $uid
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index($uid){
        $where = array(
            'uid' => $uid
        );
        $data = $this
            -> name('CustSeller')
            -> where($where)
            -> find();
        return $data;
    }

    /**
     * 获取订单数据
     * @param $id
     * @return array|false|\PDOStatement|string|Model
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException\
     */
    public function  getOrder($id){
        $where = array(
            'id' => $id
        );
        $data = $this
            -> name('runorder')
            -> where($where)
            -> find();
        return $data;
    }
}