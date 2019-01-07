<?php

namespace app\api\Controller;


use app\api\model\OrderModel;
use app\api\model\SellerModel;
use app\home\model\MessageModel;
use think\Controller;
use think\Request;
use think\Db;

class Seller extends Controller
{


    public function index(Request $request)
    {
        $id = $request -> param('id');
        if ($id) {
            $data = SellerModel::instance() -> getOrder($id);
            if ($data) {
               $this -> jsonOut($data);
            } else {
                echo json_encode(['data' => 0]);
            }
        } else {
            echo json_encode(['data' => 0]);
        }
    }

    /**
     * 添加订单
     * @return \think\response\View
     *
     */
    public function insertOrder(Request $request)
    {
        $data = $request -> param();
		
        if($data){
            $order_no = trade_no();

            $datas = [
                'goodsname' => '',
                'mudadds' => $data['uarea'],
                'myadds' => $data['uaddress'],
                 'select_name' => isset($data['select_name']) ? $data['select_name']: '',
                'price' => '',
                'times' => '',
                'time' => time(),
                'uid' => $data['uid'],
                'order_no' => $order_no,
                'order_type' => isset($data['order_type']) ? $data['order_type']: '',
                'old_order_no' => isset($data['old_order_no']) ? $data['old_order_no']: '',
               'prepay_id' => isset($data['formId']) ? $data['formId']: '',
                'distance' => isset($data['distance']) ? $data['distance']: '',
                'ins' => '',
                'status' => 1,
                'redbao' =>'',
                 'prepay_id' => isset($data['formId']) ? $data['formId']: '',
                'xphoto' =>isset($data['xphoto']) ? $data['xphoto']: '',
                'yinpin' =>isset($data['yinpin']) ? $data['yinpin']: '',
                'my_phone' => isset($data['my_phone']) ? $data['my_phone']: '',
                'pre_price' => isset($data['pre_price']) ? $data['pre_price']: '',
                'pretime' => isset($data['pretime']) ? $data['pretime']: '',
                'tip' => '',
                'type' => '商家点单',
                'message' => '',
                'distype' => 0,
                 'username' =>  '',
                'my_username' =>  $data['uname'],
                'phone' =>  '',
                'bid' => 1,
                'audiotime' => empty($data['audiotime'])?"":$data['audiotime'],
                'imgurl' =>  empty($data['imgurl'])?"":$data['imgurl'],
                'proxy_id'=>0,
                'seller_type' => 1
            ];

            $result = db('runorder')->insert($datas);

            if($result){
             \app\api\controller\Order::sendMsg();
                $info =$order_no;
                $this->jsonOut($info);
            }else{
                echo json_encode(['data' => 0]);
            }
        }else{
            echo json_encode(['data' => 0]);
        }
    }

    /**
     * 骑手修改订单
     * @return \think\response\View
     *
     */
    public function UpdateOrder(Request $request)
    {
        $data = $request->param();
        if($data){
            $datas = [
                'goodsname' => $data['goodsname'],
                'mudadds' => $data['mudadds'],
                'myadds' => $data['myadds'],
                'price' => $data['price'],
                'times' => $data['times'],
                'order_type' => isset($data['order_type']) ? $data['order_type']: '',
                'old_order_no' => isset($data['old_order_no']) ? $data['old_order_no']: '',
                'ins' => $data['ins'],
                'worth' => isset($data['worth'])?$data['worth']:0,
                'redbao' =>$data['redbao'],
                'xphoto' =>$data['xphoto'],
                'yinpin' =>$data['yinpin'],
                'tip' => $data['tip'],
                'message' => $data['message'],
                'distype' =>  $data['distype'],
                'username' =>  $data['username'],
                'phone' =>  $data['phone'],
                'audiotime' => empty($data['audiotime'])?"":$data['audiotime'],
                'imgurl' =>  empty($data['imgurl'])?"":$data['imgurl'],
                'proxy_id'=>$data['proxy_id'],
            ];

            Db::startTrans();
            $result = db('runorder')
                -> where(array('id'=>$data['id']))
                -> update($datas);

            if($result){
                $result = $this-> pricePay($data['id']);
                if ($result['code']==1)
                {
                    Db::commit();
                    echo json_encode(['data'=>1,'msg'=> '成功']);
                }
                else
                {
                    Db::rollback();
                    echo json_encode(['data' => 0,'msg'=>$result['msg']]);
                }
            }else{
                Db::rollback();
                echo json_encode(['data' => 0,'msg' => '修改失败']);
            }
        }else{
            echo json_encode(['data' => 0,'msg' => '错误操作']);
        }
    }


    /**
     * 余额支付
     */
    public function pricePay($id,$formId=null){
            $money=db('Runorder')->where('id',$id)->field('code,phone,price,uid,order_no')->find();
            $uid = $money['uid'];
            $MyMoney=db('User')->where('uid',$uid)->value('money');
            $order_no = $money['order_no'];
            $phone=$money['phone'];
            $code =$money['code'];
            $money =$money['price'];

            if(($MyMoney+100)<$money){
                return ['code'=>0,'msg'=>'商家余额不足'];
            }else{
                $data=[
                    'uid'=>$uid,
                    'price'=>$money,
                    'prepay_id'=>$formId,
                    'order_no'=>$order_no,
                    'MyMoney'=>$MyMoney,
                ];



                $result=OrderModel::pricePay($data);
                array_pop($data);
                if($result){
                    model('Sms')->index($phone,$code);
                    fastcgi_finish_request();
                    sleep(1);
                    MessageModel::PayMsg($data['order_no']);
                    //OrderModel::SendMsg($order_no);

                    return ['code'=>1];
                }else{
                    return ['code'=>0,'msg'=>'支付失败'];
                }
            }

    }

    /**
     * 上下班
     * @param Request $request
     */
    public function isOn(Request $request) {
        $data  = $request -> param();
        $is_on = $data['isOn'];
        $cid   = $data['cid'];
        if (isset($cid) && isset($is_on)) {
            $result = db('CustUser')
                -> where(['cid'=> $cid])
                -> update(['is_on'=>$is_on]);
            if ($result) {
                return json_encode(['code'=>1]);
            } else {
                return json_encode(['code'=>0,'msg' => '修改失败']);
            }
        } else {
            return json_encode(['code'=>0,'msg' => '错误操作']);
        }
    }


}