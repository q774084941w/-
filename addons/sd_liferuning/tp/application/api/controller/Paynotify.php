<?php
namespace app\api\controller;

use app\api\model\OrderModel;


class Paynotify
{
/**
    * 微信支付回调
    */
    public function wxgoodsnotify(){
        //var_dump(111);DIE;
        OrderModel::instance()->wxNotify();
    }
/**
     * 微信支付回调（充值）
     */
    public function wxtopUpnotify(){
        OrderModel::instance()->topUpNotify();
    }


}
