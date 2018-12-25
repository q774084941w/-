<?php
namespace app\home\controller;

use app\home\model\CouponModel;
use think\Controller;
use think\Request;

class Coupon extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
   /**
    * 优惠券
    *
    */
    public function index(){
        $bid = $this->_getBid();
        $result = CouponModel::instance()->couponlist($bid);

        return view('coupon/index',['list'=>$result]);

    }
    /**
     * 0 开启  1 关闭
     */
    public function soldOut(Request $request){
        $id = $request->post('goodsid');
        $status = $request->post('status');
        $result  = CouponModel::instance()->soldOut($status,$id);
        return $result;
    }
    /**
     * 添加
     */
    public function add(){
        $bid = $this->_getBid();
        list($goods,$meuns) = CouponModel::instance()->addlist($bid);
        return view('coupon/add',['goods'=>$goods,'menus'=>$meuns]);
    }
    /**
     * 添加数据库
     */
    public function insert(Request $request){
        $data = $request->post();
        $bid = $this->_getBid();
        $data['bid'] = $bid;
        if($data['type'] == 2) $data['commid'] = $data['typeq'];
        if($data['type'] == 3) $data['goodsid'] = $data['typeq'];
        if($data['coupontype'] == 0){
            $data['starttime'] = strtotime($data['starttime']);
            $data['endtime'] = strtotime($data['endtime']);
        }else{
          	$data['starttime'] =time();
            $data['endtime'] = strtotime($data['timelong']);
        }
        unset($data['typeq']);
        $result = CouponModel::instance()->adddata($data);
        if($result == 1){
            $this->success('添加成功', 'coupon/index');
        }else{
            $this->error('新增失败');
        }
    }
    /**
     * 编辑
     */
    public function edit(Request $request){
        $id = $request->get('id');
        $bid = $this->_getBid();
        $result = CouponModel::instance()->couponedit($id);
        list($goods,$meuns) = CouponModel::instance()->addlist($bid);

        return view('coupon/edit',['data'=>$result,'goods'=>$goods,'menus'=>$meuns]);
    }
    /**
     * 添加数据库（编辑）
     */
    public function editadd(Request $request){
        $data = $request->post();
        if($data['type'] == 2) $data['commid'] = $data['typeq'];
        if($data['type'] == 3) $data['goodsid'] = $data['typeq'];
         if($data['coupontype'] == 0){
            $data['starttime'] = strtotime($data['starttime']);
            $data['endtime'] = strtotime($data['endtime']);
        }else{
          	$data['starttime'] =time();
            $data['endtime'] = strtotime($data['timelong']);
        }
        unset($data['typeq']);
        $result = CouponModel::instance()->updatedata($data);
        if($result == 1){
            $this->success('修改成功', 'coupon/index');
        }else{
            $this->error('修改失败');
        }
    }
    /**
     * 删除
     */
    public function delete(Request $request){
        $banid = $request->get('id');
        $result = CouponModel::instance()->deleteban($banid);
        return $result;
    }
}
