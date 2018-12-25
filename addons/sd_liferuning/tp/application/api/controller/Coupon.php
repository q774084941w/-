<?php
namespace app\api\controller;

use app\api\model\CouponModel;
use think\Controller;
use think\Request;

class Coupon extends Controller
{
   /**
    * 优惠券类
    *
    * 优惠券列表
    */
    public function couponList(Request $request){
        $bid = $request->get('bid');
        $type = $request->get('type');
        if(empty($bid)) $this->outPut('', 1001, ":缺少参数 bid");
        $result = CouponModel::instance()->couponlist($bid,$type);
        if(empty($result)){
            exit(json_encode(['code'=>0,'msg'=>'没有数据']));
        }else{
            exit(json_encode(['code'=>1,'data'=>$result]));
        }

    }
    /**
     * 领取优惠券
     */
    public function getcoupon(Request $request){
        $disid = $request->get('disid');

        if(empty($disid)) $this->outPut('', 1001, ":缺少参数 disid");
        if(db('Coupon')->where(['uid'=>$this->uid,'disid'=>$disid])->find()){
            exit(json_encode(['code'=>0,'msg'=>'您已领取过该红包']));
        }
        list($result,$code) = CouponModel::instance()->getcoupon($disid,$this->uid);
        if($result == false)  $this->outPut(null,$code);
        if($result == 1){
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }

    }
    /**
     * 个人优惠券
     */
    public function RedUser($uid){
        $count=db('Runorder')->where('uid',$uid)->count();
        $where='';
        if($count){
            $where['ctitle']=['neq','仅限首单使用'];
        }
        $result=db('Coupon')
            ->where('uid',$uid)
            ->where('status',0)
            ->where('pasttime','>=',time())
            ->where($where)
            ->order('useid desc')
            ->select();
        foreach ($result as $k=>$v){
            $result[$k]['createtime']=date('Y-m-d',$result[$k]['createtime']);
            $result[$k]['pasttime']=date('Y-m-d',$result[$k]['pasttime']);
        }
        if($result){
            exit(json_encode(['code'=>1,'data'=>$result]));
        }else{
            exit(json_encode(['code'=>0,'msg'=>'数据为空']));
        }

    }
    public function coupon($uid){
        $count=db('Runorder')->where('uid',$uid)->count();
        $where='';
        if($count){
            $where['ctitle']=['neq','仅限首单使用'];
        }
         $field='135k_coupon.status,135k_coupon.pasttime,135k_coupon.uid,135k_coupon.useid,135k_coupon.money,135k_coupon.ctitle,135k_coupon_add.full_money';
        $result=db('Coupon')
            ->join('135k_coupon_add','135k_coupon.disid=135k_coupon_add.disid')
            ->where('135k_coupon.uid',$uid)
            ->where('135k_coupon.status',0)
            ->where('135k_coupon.pasttime','>=',time())
            ->order('135k_coupon.useid desc')
            ->where($where)
            ->field($field)
            ->select();
        $data=[];
        foreach ($result as $key=>$val){
            $data[$key]['text']=$val['money'].'优惠红包';
            $data[$key]['price']=$val['money'];
            $data[$key]['id']=$val['useid'];
          $data[$key]['full_money']=$val['full_money'];
        }
        exit(json_encode(['code'=>1,'data'=>$data]));
    }
    //使用红包
    public function status($useid){
        $result=db('Coupon')->where('useid',$useid)->update(['status'=>1]);
        if($result){
            exit(json_encode(['code'=>1,'msg'=>'使用成功']));
        }else{
            exit(json_encode(['code'=>0,'msg'=>'使用失败']));
        }

    }
    /**
     * 首单红包
     */
    public function CheckRed($bid,$uid){

        if(db('User')->where('uid',$uid)->value('redstatus')==0){

            $rs = CouponModel::instance()->couponlist($bid,4);
            if($rs){

              if(empty(db('Runorder')->where('uid',$uid)->value('id'))){
                  $disid=$rs[0]['disid'];
                  db('User')->where('uid',$uid)->update(['redstatus'=>1]);
                  list($result,$code) = CouponModel::instance()->getcoupon($disid,$uid);
                  if($result == false)  $this->outPut(null,$code);
                  if($result == 1){
                      exit(json_encode(['code'=>1,'money'=>$rs[0]['money']]));
                  }else{
                      $this->outPut(null,0);
                  }
              }else{
                  exit(json_encode(['code'=>0]));
              }
            }else{
                exit(json_encode(['code'=>0]));
            }
        }else{
            exit(json_encode(['code'=>0]));
        }


    }
    /*
     * 未领取红包列表
     * */
    public function actRed(Request $request){
        $bid = $request->get('bid');
        $uid = $request->get('uid');
        $list = CouponModel::instance()->actRed($bid,$uid);
        foreach ($list as $k => $v){
            if ($v['coupontype'] == 1){
                $createtime=time();
                $pasttime=strtotime('+'.$v['timelong'].'day');
                $list[$k]['createtime']=date('Y-m-d',$createtime);
                $list[$k]['pasttime']=date('Y-m-d',$pasttime);
            }else{
                $list[$k]['createtime']=date('Y-m-d',$list[$k]['starttime']);
                $list[$k]['pasttime']=date('Y-m-d',$list[$k]['endtime']);
            }
        }
        if (count($list)){
            return json_encode(['code'=>1,'data'=>$list]);
        }else{
            return json_encode(['code'=>0,'msg'=>'数据为空']);
        }
    }
    /**
     * 领取活动优惠券
     */
    public function getActCoupon(Request $request){
        $disid = $request->get('disid');
        $uid = $request->get('uid');
        $createtime = strtotime($request->get('createtime'));
        $pasttime = strtotime($request->get('pasttime'));
        if(empty($disid)) $this->outPut('', 1001, ":缺少参数 disid");
        if(db('Coupon')->where(['uid'=>$uid,'disid'=>$disid])->find()){
            exit(json_encode(['code'=>0,'msg'=>'您已领取过该红包']));
        }
        list($result,$code) = CouponModel::instance()->getActCoupon($disid,$uid,$createtime,$pasttime);
        if($result == false)  $this->outPut(null,$code);
        if($result == 1){
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }

    }
    /*
     * 用户已领取可用的优惠券列表
     * */
    public function getCouponList(Request $request){
        $bid = $request->get('bid');
        $uid = $request->get('uid');
        $list = CouponModel::instance()->getCouponList($bid,$uid);
        foreach ($list as $k => $v){
            $list[$k]['createtime']=date('Y-m-d',$list[$k]['starttime']);
            $list[$k]['pasttime']=date('Y-m-d',$list[$k]['endtime']);
        }
        if (count($list)){
            return json_encode(['code'=>1,'data'=>$list]);
        }else{
            return json_encode(['code'=>0,'msg'=>'数据为空']);
        }
    }

}
