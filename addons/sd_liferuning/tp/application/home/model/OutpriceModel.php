<?php
namespace app\home\model;
use think\Db;
use app\conmon\BusinessPay;
/**
 * 用户端提现
 */
class OutpriceModel{
    /**
     * @param $bid
     * 提现列表
     */
    public static function priceList($bid){
        $result=db('OutPrice')
            ->alias('a')
            ->join('User u','u.uid=a.uid')
            ->field('a.*,u.uid,u.nickname')
            ->where('a.bid',$bid)
            ->order('oid desc')
            ->paginate(15);
        $all=$result->all();
        foreach ($all as $key=>$val){
            $val['updatetime']=$val['updatetime']==''?'':date('Y-m-d H:i:s',$val['updatetime']);
            switch ($val['status']){
                case 1:
                    $val['msg']='已通过';
                    $val['class']='putaway';
                    break;
                case -1:
                    $val['msg']='已拒绝';
                    $val['class']='sold';
                    break;
                case 0:
                    $val['msg']='等待审核';
                    $val['class']='putaway';
                    break;
            }

            $result[$key]=$val;
        }
        return $result;
    }

    /**
     * @param $data
     * @param $money
     * @return bool
     * 小程序提现
     */
    public static function outPrice($data,$money){
        $userMoney=$money-$data['money'];
        Db::startTrans();
        $result=db('OutPrice')->insertGetId($data);
        $rs=db('User')->where('uid',$data['uid'])->update(['money'=>$userMoney]);
        if($result&&$rs){
            Db::commit(); // 提交事务
            return $result;
        }else{
            Db::rollback();
            return false;
        }
    }

    /**
     * @param $id
     * @return int|string
     * 同意提现操作
     */
    public static function consent($id){
        $OutPrice=db('OutPrice')->where('oid',$id)->find();
        if(empty($OutPrice))return false;
        $result=db('OutPrice')->where('oid',$id)->update(['status'=>1]);

        return $result;
    }
    public static function refuse($id){
        $price=db('OutPrice')->where('oid',$id)->find();
        Db::startTrans();
        $rs=db('OutPrice')->where('oid',$price['oid'])->update(['status'=>-1]);
        $result=db('User')->where('uid',$price['uid'])->setInc('money',$price['money']);
        if($rs&&$result){
            Db::commit();
            $data=[
                'uid'=>$price['uid'],
                'msg'=>'拒绝提现',
                'paytype'=>'outpay',
                'money'=>$price['money'],
                'oid'=>$price['oid'],
                'createtime'=>time()
            ];
            db('PriceMsg')->insert($data);
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }
    public static function off($id){
        $rs=db('Business_pay')->where('out_id',$id)->find();
        if(empty($rs))return false;
        if($rs['type']==1){
            Db::startTrans();
            $out=db('User')->where('uid',$rs['uid'])->setInc('money',$rs['price']);
            $re=db('Business_pay')->where('id',$id)->update(['status'=>-1]);
            $data=[
                'uid'=>$rs['uid'],
                'msg'=>'拒绝提现',
                'paytype'=>'outpay',
                'money'=>$rs['price'],
                'createtime'=>time()
            ];
            $rss=db('PriceMsg')->insert($data);
            if(empty($out)||empty($re)||empty($rss)){
                Db::rollback();
                return false;
            }else{
                Db::commit();
                return true;
            }
        }else{
            Db::startTrans();
            $out=db('CustUser')->where('uid',$rs['uid'])->setInc('money',$rs['price']);
            $re=db('Business_pay')->where('id',$id)->update(['status'=>-1]);
            if(empty($out)||empty($re)){
                Db::rollback();
                return false;
            }else{
                Db::commit();
                return true;
            }
        }

    }
    public function on($id){
        $rs=db('Business_pay')->where('out_id',$id)->find();
        $openid=db('User')->where('uid',$rs['uid'])->value('openid');
        if(empty($rs))return false;
        Db::startTrans();
        $rs=db('Business_pay')->where('id',$id)->update(['update_time'=>time(),'status'=>1]);
        if(empty($rs)){
            Db::rollback();
            return false;
        }
        $pay=BusinessPay(session('bus_bid'));
        $result=$pay->Deposit($rs['price'],$openid,trade_no());
        if($result['error']){
            Db::startTrans();
            return false;
        }
        Db::commit();
        return true;
    }

}

?>