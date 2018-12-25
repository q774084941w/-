<?php
namespace app\api\model;

use think\Db;

class CouponModel
{
    /**
     * 单例模式
     * @return CouponModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CouponModel();
        }
        return $m;
    }
    /**
     * 优惠券列表
     */
    public function couponlist($bid,$type){
        $list = Db::name('couponAdd')
            ->field('starttime,commid,goodsid,number,bid,endtime,money,full_money,disid,type,status')
            ->where('bid',$bid)
	        ->where('status',0)
            ->where('type',$type)
            ->where('number','>',0)
            ->where('delete',0)
            ->where('starttime','<=',time())
            ->where('endtime','>=',time())
            ->order('disid desc')
            ->select();

        return $list;

    }
    /**
     * 领取优惠券
     */
    public function getcoupon($disid,$uid){
        $couadd = Db::name('couponAdd')->field('number,money,endtime,type')->where('disid',$disid)->find();
        Db::name('couponAdd')->field('number,money,endtime')->where('disid',$disid)->setDec('number',1);
        $count = Db::name('coupon')->where(['uid'=>$uid,'disid'=>$disid])->count();
        if(($couadd['number'] - $count) > 0){
            $arr = [];
            for($i=0;$i<4;$i++){
                $arr[] = randCode(6,0);
            }
            $data['ctitle'] = $couadd['type']==5?'转发红包':'仅限首单使用';
            $data['uid'] = $uid;
            $data['disid'] = $disid;
            $data['money'] = $couadd['money'];
            $data['createtime'] = time();
            $data['pasttime'] = $couadd['endtime'];
            $result = Db::name('coupon')->insert($data);
            return [$result,''];
        }else{
            return [false,6001];
        }
    }
    /*
     * 未领取优惠券列表
     * 转发红包和首单注册红包的页面请求不在这里
     * 这里是活动优惠券红包
     * */
    public function actRed($bid,$uid){
//        所有数据
        $list = Db::name('coupon_add')
            ->field('disid,name,money,full_money,starttime,endtime,timelong,coupontype')
            ->where(['bid'=>$bid,'type'=>6,'status'=>0,'delete'=>0])
            ->where('number','>',0)
            ->order('sort desc')
            ->select();
//        未领取未过期优惠券
        $coupon = Db::name('coupon');
        $list2 = [];
        foreach ($list as $k => $v){
            $res = $coupon->where(['uid'=>$uid,'disid'=>$v['disid']])->find();
            if (!$res && $v['coupontype'] == 0 && $v['endtime'] > time()){
                $list2[] = $list[$k];
            }elseif (!$res && $v['coupontype'] == 1){
                $list2[] = $list[$k];
            }
        }
        return $list2;
    }
    /**
     * 领取活动优惠券
     */
    public function getActCoupon($disid,$uid,$createtime,$pasttime){
        $couadd = Db::name('couponAdd')->field('number,money')->where('disid',$disid)->find();
        $num = $couadd['number'] - 1;
        Db::name('couponAdd')->where('disid',$disid)->update(['number'=>$num]);
        $data['disid'] = $disid;
        $data['uid']   = $uid;
        $data['ctitle']= '活动红包';
        $data['money'] = $couadd['money'];
        $data['createtime'] = $createtime;
        $data['pasttime'] = $pasttime;
        $result = Db::name('coupon')->insert($data);
        if($result){
            return [$result,''];
        }else{
            return [false,6001];
        }
    }
    /*
     * 用户已领取优惠券列表
     * */
    public function getCouponList($bid,$uid){
        $time = time();
        $list = Db::name('coupon')
            ->where(['bid'=>$bid,'uid'=>$uid,'status'=>0])
            ->where('pasttime','>',$time)
            ->order('sort desc')
            ->select();
        return $list;
    }

}
