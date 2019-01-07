<?php
namespace app\home\model;

use think\Db;

class OrderModel  
{
    /**
     * 单例模式
     * @return OrderModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new OrderModel();
        }
        return $m;
    }
    /**
     * 订单
     */
    public function orderlist($bid,$status,$order){
        //三天自动过期
        $list = Db::name('runorder')->where(['status'=>2])->field('id,oktime')->select();
//        var_dump($list);die;
        foreach ($list as $k => $v){
            if ($v['oktime'] != NULL){
                $delaytime = strtotime('+3 day',$v['oktime'])-time();
                if ($delaytime){
                    Db::name('runorder')->where(['id'=>$v['id']])->update(['status'=>3]);
                }
            }
        }
        $result = Db::name('runorder')->alias('g')->join('135k_user u','g.uid = u.uid')
            ->field('g.id,g.uid,g.order_no,g.bid,g.price,g.time,g.payway,g.status,u.nickname,g.phone,g.oktime,g.rid')
            ->where(['g.bid'=>$bid,'g.status'=>$status])
            ->order($order)
            ->paginate(10);
        return $result;
    }
    /**
     * 详情
     */
    public function showlist($id){
        $goodsorder =  Db::name('runorder')
            ->alias('g')
            ->join('__USER__ u','g.uid = u.uid')
            ->field('g.id,g.uid,g.order_no,g.bid,g.price,g.time,g.payway,g.status,u.nickname,u.phone,g.select_name,g.oktime,u.nickname,g.myadds,g.mudadds,g.goodsname,g.num_star,g.why_text,g.givetime,g.code')
            ->where('id',$id)
            ->find();
        //print_r($result);exit;
        return $goodsorder;
    }
    /**
     * 添加快递
     */
    public function addexpress($data){
        $result = Db::name('goodsOrder')->where('orderid',$data['pid'])->update(['status'=>3,'express_number'=>$data['number'],'exid'=>$data['mun']]);
        return $result;
    }

    /**
     * 余额支付
     */
    public static function pricePay($data){
        $price = $data['MyMoney']-$data['price'];
        array_pop($data);

        $data['status']=1;
        $data['time']=time();
        $data['payway']='pricePay';

        Db::startTrans();
        $rs=db('Runorder')->where(['order_no'=>$data['order_no'],'uid'=>$data['uid']])->update($data);
        $result=db('User')->where('uid',$data['uid'])->update(['money'=>$price]);
        if($rs&&$result){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }

    }


    /**
     * 详情
     */
    public function edit($id){
        $goodsorder =  Db::name('runorder')
            ->alias('g')
            ->join('__USER__ u','g.uid = u.uid')
            ->field('g.*,u.nickname,u.phone,u.nickname')
            ->where('g.id',$id)
            ->find();
        //print_r($result);exit;
        return $goodsorder;
    }
  
    public static function playMsg($uid,$bid){
        $order=db('Runorder')->where(['bid'=>$bid,'status'=>1])->order('id desc')->limit(1)->column('id');
        if(!empty($order)) {
            return true;
        }else{
            return false;
        }

    }
}
