<?php
namespace app\api\model;
use think\Model;
use app\conmon\BusinessPay;
use think\Db;
class Outprice extends Model{
    public function BusinessPay($uid,$price,$type){
        if(empty($price))return ['error'=>1,'msg'=>'金额错误'];
        switch ($type){
            case 1;
            $rs=db('User')->where('uid',$uid)->find();
                if(empty($rs))return ['error'=>1,'msg'=>'获取用户信息失败'];
                if($price>$rs['money'])return ['error'=>1,'msg'=>'余额不足'];
                $result=$this->user_pay($rs,$price);
            break;
            case 2;
            $rs=db('Cust_user')->alias('c')
                ->join('User u','u.uid=c.uid')
                ->where('c.uid',$uid)
                ->field('u.openid,u.bid,c.*')
                ->find();
                if(empty($rs))return ['error'=>1,'msg'=>'获取用户信息失败'];
                if($price>$rs['money'])return ['error'=>1,'msg'=>'余额不足'];
                $result=$this->cust_pay($rs,$price);
                var_dump($result);die;
            break;
        }
        if(empty($result))return ['error'=>1,'msg'=>'提现失败'];
        return ['error'=>0,'msg'=>'提现成功'];
    }
    public function user_pay($data,$price){
        $auto_pay=db('Business')->where('bid',$data['bid'])->value('auto_pay');
        Db::startTrans();
        $order_no=trade_no();
        $Dec=db('User')->where('uid',$data['uid'])->setDec('money',$price);
        $add=[
            'uid'=>$data['uid'],
            'order_no'=>$order_no,
            'out_time'=>time(),
            'type'=>1,
            'bid'=>$data['bid'],
            'status'=>$auto_pay,
            'price'=>$price
        ];
        $msg=[
            'uid'=>$data['uid'],
            'money'=>$price,
            'paytype'=>'pay',
            'msg'=>'提现到零钱',
            'createtime'=>time(),
        ];
        $rss=db('PriceMsg')->insert($msg);
        $rs=db('Business_pay')->insert($add);
        if(empty($Dec)||empty($rs)||empty($rss)){
            Db::startTrans();
            return false;
        }
        if($auto_pay){
            $pay=new BusinessPay($data['bid']);
            $result=$pay->Deposit($price,$data['openid'],$order_no);
            if($result['error']){
                Db::startTrans();
                return false;
            }
        }

        Db::commit();
        return true;
    }
    public function cust_pay($data,$price){
        $auto_pay=db('Business')->where('bid',$data['bid'])->value('auto_pay');
        Db::startTrans();
        $order_no=trade_no();
        $Dec=db('Cust_user')->where('cid',$data['cid'])->setDec('money',$price);
        $add=[
            'uid'=>$data['uid'],
            'cid'=>$data['cid'],
            'order_no'=>$order_no,
            'out_time'=>time(),
            'type'=>2,
            'bid'=>$data['bid'],
            'status'=>$auto_pay,
            'price'=>$price
        ];
        
        $rs=db('Business_pay')->insert($add);
        if(empty($Dec)||empty($rs)){
            Db::startTrans();
            return false;
        }
        if($auto_pay){
            $pay=new BusinessPay($data['bid']);
            $result=$pay->Deposit($price,$data['openid'],$order_no);
            var_dump($result);die;
            if($result['error']){
                Db::startTrans();
                return false;
            }
        }
        Db::commit();
        return true;
    }
}