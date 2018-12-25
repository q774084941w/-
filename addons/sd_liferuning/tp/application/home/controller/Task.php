<?php
namespace app\home\controller;
use think\Controller;
use think\Request;
use app\home\model\ExpressModel;
use app\home\model\TaskModel;
class Task extends Controller{
    public function _initialize(Request $request = null)
    {
        $result = $this->_getBid();
        if(!$result){
            $this->success('未登录', 'induserindex.ex/login');
        }
    }
    /**
     * 订单分配列表
     */
    public function index(){
        $bid = $this->_getBid();
        $status = 1;
        $result = TaskModel::instance()->orderlist($bid,$status,'id asc');

        $express = ExpressModel::instance()->exprlist($bid);
        $inside=TaskModel::instance()->get_cust();
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '待接单';
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $result->offsetSet($k,$data);
        }
        return view('task/delivergoods',['data'=>$result,'express'=>$express,'inside'=>$inside]);
    }
    /**
     * 订单分配
     */
    public function GiveOrder(Request $request){
        $orderid = $request->get('orderid');
        $rid = $request->get('uid');
        $result = TaskModel::instance()->GiveOrder($orderid,$rid);
        return $result;
    }

}