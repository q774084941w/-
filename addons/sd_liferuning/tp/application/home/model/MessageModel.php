<?php
namespace app\home\model;
use  think\Db;
class MessageModel{

    public static function PayMsg($order_no){
        $order=db("Runorder")->where('order_no',$order_no)->find();
        if(empty($order))return false;
        if($order['payway']=='weixin'){
            $data=[
                'uid'=>$order['uid'],
                'msg'=>$order_no.'订单号微信支付',
                'paytype'=>'pay',
                'money'=>$order['price'],
                'order_no'=>$order_no,
                'createtime'=>time()
            ];
        }
        if($order['payway']=='pricePay'){
            $data=[
                'uid'=>$order['uid'],
                'msg'=>$order_no.'订单号余额支付',
                'paytype'=>'pay',
                'money'=>$order['price'],
                'order_no'=>$order_no,
                'createtime'=>time()
            ];
        }

        $result=db('PriceMsg')->insert($data);
        return $result;
    }

    public static function PayMsg2($order_no){
        $order=db("price_order")->where('order_no',$order_no)->find();
        if(empty($order))return false;

            $data=[
                'uid'=>$order['uid'],
                'msg'=>$order_no.'保证金余额支付',
                'paytype'=>'pay',
                'money'=>$order['money'],
                'order_no'=>$order_no,
                'createtime'=>time()
            ];


        $result=db('PriceMsg')->insert($data);
        return $result;
    }



    public static function OutMsg($order_no){
        $order=db("Runorder")->where('order_no',$order_no)->find();
        if(empty($order))return false;
        if($order['payway']=='weixin'){
            $data=[
                'uid'=>$order['uid'],
                'msg'=>$order_no.'订单号退回微信',
                'paytype'=>'outpay',
                'money'=>$order['price'],
                'order_no'=>$order_no,
                'createtime'=>time()
            ];
        }
        if($order['payway']=='pricePay'){
            $data=[
                'uid'=>$order['uid'],
                'msg'=>$order_no.'订单号退回余额',
                'paytype'=>'outpay',
                'money'=>$order['price'],
                'order_no'=>$order_no,
                'createtime'=>time()
            ];
        }
        $result=db('PriceMsg')->insert($data);
        return $result;

    }
    public static function rechargeMsg($order_no){
        $order=db("TopUp")->where('order_no',$order_no)->find();
        if(empty($order))return false;
        $data=[
            'uid'=>$order['uid'],
            'msg'=>'微信充值余额',
            'paytype'=>'pay',
            'money'=>$order['money'],
            'order_no'=>$order_no,
            'createtime'=>time()
        ];
        $result=db('PriceMsg')->insert($data);
        return $result;
    }


    public static function index($uid,$msg,$money,$order_no=null,$paytype='outpay'){

        $data=[
            'uid'=>$uid,
            'msg'=>$msg,
            'paytype'=>$paytype,
            'money'=>$money,
            'order_no'=>$order_no,
            'createtime'=>time()
        ];
        $result=db('PriceMsg')->insert($data);
        return $result;
    }

}

?>