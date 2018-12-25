<?php
namespace app\api\controller;
use app\api\model\BusinessModel;
use think\Controller;
use think\Request;

class Business extends Controller{
    /*
     * 获取商户客服电话
     */
    public function getPhone(Request $request){
        $bid=$request->get('bid');
        $list=BusinessModel::instance()->getPhone($bid);
        $this->jsonOut($list);
    }
}