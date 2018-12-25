<?php
namespace app\api\controller;
use think\Controller;
use app\home\model\OutpriceModel;
use app\home\model\CardModel;
use think\Request;

class Outprice extends Controller{
    /**
     * 银行卡提现
     */
    public function Send($uid,$money){
        $Card=CardModel::CardDefault($uid);
        if(empty($Card)){
            exit(json_encode(['code'=>0,'msg'=>'没有设置银行卡']));
        }
        $User=db('User')->where('uid',$uid)->find();
        if($User['money']<$money){
            exit(json_encode(['code'=>0,'msg'=>'余额不足']));
        }
        $data=[
            'uid'=>$uid,
            'bid'=>$User['bid'],
            'money'=>$money,
            'name'=>$Card['name'],
            'card'=>$Card['cardnumber'],
            'cardname'=>$Card['uname'],
            'createtime'=>time()
        ];
        $result=OutpriceModel::outPrice($data,$User['money']);
        if($result){
            $msg=[
                'uid'=>$data['uid'],
                'money'=>$data['money'],
                'paytype'=>'pay',
                'msg'=>'提现操作',
                'createtime'=>time(),
                'oid'=>$result
            ];
            db('PriceMsg')->insert($msg);
            exit(json_encode(['code'=>1,'msg'=>'提现成功','money'=>$money]));
        }else{
            exit(json_encode(['code'=>0,'msg'=>'提现失败']));
        }
    }
    /**
     * 可提现的金额
     */
    public function UpMoney($uid,$type){
        if($type == 1){
            $money = db('User')->where('uid',$uid)->value('money');
            echo json_encode(['code'=>1,'money'=>$money]);die;
        }
        if($type == 2){
            $money = db('cust_user')->where('uid',$uid)->value('money');
            echo json_encode(['code'=>1,'money'=>$money]);die;
        }
    }
    /**
     * 支付宝账号提现
     */
    public function alipaySend(){
        $data = input('get.');
        if($data['type']==1){
            $User=db('User')->where('uid',$data['uid'])->find();
            if($User['money']<$data['money']){
                exit(json_encode(['code'=>0,'msg'=>'余额不足']));
            }
            $vn = [
                'uid'=>$data['uid'],
                'bid'=>$data['bid'],
                'money'=>$data['money'],
                'status'=>0,
                'name'=>$data['name'],
                'alipay'=>$data['alipay'],
                'createtime'=>time(),
            ];
            $dataL = db('out_price')->insertGetId($vn);
            if($dataL){
                $msg=[
                    'uid'=>$data['uid'],
                    'money'=>$data['money'],
                    'paytype'=>'pay',
                    'msg'=>'提现操作',
                    'createtime'=>time(),
                    'oid'=>$data
                ];
                db('PriceMsg')->insert($msg);
                $moneys = $User['money']-$data['money'];
                db('User')->where('uid',$data['uid'])->update(['money'=>$moneys]);
                echo json_encode(['code'=>1]);
            }else{
                echo json_encode(['code'=>0]);
            }
        }
        if($data['type']==2){
            $User=db('cust_user')->where('uid',$data['uid'])->find();
            if($User['money']<$data['money']){
                exit(json_encode(['code'=>0,'msg'=>'余额不足']));
            }
            $vn = [
                'uid'=>$data['uid'],
                'bid'=>$data['bid'],
                'money'=>$data['money'],
                'status'=>0,
                'name'=>$data['name'],
                'alipay'=>$data['alipay'],
                'createtime'=>time(),
            ];
            $dataL = db('take_out')->insertGetId($vn);
            if($dataL){
                $moneys = $User['money']-$data['money'];
                db('cust_user')->where('uid',$data['uid'])->update(['money'=>$moneys]);
                echo json_encode(['code'=>1]);
            }else{
                echo json_encode(['code'=>0]);
            }
        }
    }
    /**
     * 提现到零钱
     */
    public function BusinessPay(Request $request){

        if(request()->isPost()){
            $uid=$request->post('uid');
            $price=$request->post('price');
            $type=$request->post('type');
            $result=model('Outprice')->BusinessPay($uid,$price,$type);
            exit(json_encode($result));
        }
    }
}

?>