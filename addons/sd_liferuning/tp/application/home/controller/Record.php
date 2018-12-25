<?php
namespace app\home\controller;

use app\home\model\RecordModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;

class Record extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    public function index()
    {

        $bid = $this->_getBid();
        $field = 'orderid,order_no,money,paytime';
        $result = RecordModel::instance()->recordlist($bid, $field);
     //   var_dump($result);exit;
//        for($i=0;$i<count($result);$i++){
//            if($result[$i]['status']==2){
//                $result[$i]['status']='代发货';
//            }elseif($result[$i]['status']==3){
//                $result[$i]['status']='待收货';
//            }elseif($result[$i]['status']==4){
//                $result[$i]['status']='已完成';
//            }elseif($result[$i]['status']==-1){
//                $result[$i]['status']='交易关闭';
//            }
//        }



        return view('record/index', ['data' => $result]);
    }
}