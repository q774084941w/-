<?php
namespace app\home\model;
use think\Db;
class TaskModel{
    public static function instance(){
        static $m=null;
        if(!$m){
            $m=new TaskModel();
        }
        return $m;
    }
    /**
     * 待接单订单查询
     */
    public function orderlist($bid,$status,$order){
        $result = Db::name('runorder')->alias('g')->join('135k_user u','g.uid = u.uid')
            ->field('g.id,g.uid,g.order_no,g.bid,g.price,g.time,g.payway,g.status,u.nickname,u.phone,g.oktime')
            ->where(['g.bid'=>$bid,'g.status'=>$status])
            ->order($order)
            ->paginate(10);
        return $result;
    }
    /**
     * 查询内部跑腿人员
     */
    public function get_cust(){
        $field='uname,uid';
        $result=Db::name('cust_user')->field($field)->where('inside',1)->order('cid desc')->select();
        return $result;
    }
    /**
     * 分配单子
     */
    public function GiveOrder($orderid,$rid){
        if($rid != 0){
            $data =[
                'rid' => $rid,
                'status' => 2,
                'givetime' => time()
            ];
            $result=Db::name('runorder')
                ->where(['id' => $orderid])
                ->update($data);
            if($result){
                return $result;
            }else{
                $result = 0;
                return   $result;
            }
        }else{
            $result = 0;
            return   $result;
        }
    }
}