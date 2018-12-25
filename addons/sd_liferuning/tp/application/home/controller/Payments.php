<?php
namespace app\home\controller;

use app\home\model\PaymentsModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;

class Payments extends Controller{
    public function _iniaialize(Request $request = null)
    {


    }
    public function index(){
        $bid = $this->_getBid();
        $field = 'bid,appid,secret,key,mchid';
        $result = PaymentsModel::instance()->paylist($bid,$field);
        // var_dump($result);exit;
        return view('payments/index',['data'=>$result,'bid'=>$result[0]['bid']]);
    }


    public function edit(){
        $id = $this->_getBid();
        $result = PaymentsModel::instance()->payonelist($id);
        return view('payments/edit',['data'=>$result]);
    }

    public function editer(Request $request){

        $data = $request->post();
        if(empty($data['open_pay']))$data['open_pay']=0;
        if(empty($data['auto_pay']))$data['auto_pay']=0;
        $bid =  $data['bid'];

        $result = PaymentsModel::instance()->payeditlist($bid,$data);
        if($result){

            file_put_contents(dirname(dirname(dirname(__FILE__))).'/conmon/wxpaylib/cert/apiclient_cert'.$bid.'.pem',$data['apiclient_cert']);
            file_put_contents(dirname(dirname(dirname(__FILE__))).'/conmon/wxpaylib/cert/apiclient_key'.$bid.'.pem',$data['apiclient_key']);

            $this->success('修改成功');
        }else{
            $this->error('修改失败');
        }
    }
}
