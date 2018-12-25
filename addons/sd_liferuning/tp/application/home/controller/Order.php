<?php
namespace app\home\controller;

use app\home\model\MessageModel;
use app\home\model\OrderModel;
use app\home\model\ExpressModel;
use app\home\model\Sms;
use think\Controller;
use think\Request;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class Order extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 订单类
     * @return \think\response\View
     *
     * 代付款
     */
    public function index()
    {
        $bid = $this->_getBid();
        $status = 0;
        $result = OrderModel::instance()->orderlist($bid,$status,'time desc');
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '待付款';
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $result->offsetSet($k,$data);
        }

        return view('order/index',['data'=>$result]);
    }
    /**
     * 待接单
     */
    public function delivergoods()
    {
        $bid = $this->_getBid();
        $status = 1;
        $result = OrderModel::instance()->orderlist($bid,$status,'id asc');

        $express = ExpressModel::instance()->exprlist($bid);

        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '待接单';
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $result->offsetSet($k,$data);
        }
        return view('order/delivergoods',['data'=>$result,'express'=>$express]);
    }


    /**
     *
     */
    public function cancel()
    {
        $bid = $this->_getBid();
        $status = -1;
        $result = OrderModel::instance()->orderlist($bid,$status,'id asc');

        $express = ExpressModel::instance()->exprlist($bid);

        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '已取消';
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $result->offsetSet($k,$data);
        }
        return view('order/cancel',['data'=>$result,'express'=>$express]);
    }
    /**
     * 配送中
     */
    public function takegoods()
    {
        $bid = $this->_getBid();
        $status = 2;
        $result = OrderModel::instance()->orderlist($bid,$status,'id desc');

        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $list=db('cust_user')->field('uname')->where(['uid'=>$data['rid']])->find();
            $data['status'] = '配送中';
            $data['uname']=$list['uname'];
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $result->offsetSet($k,$data);
        }
        return view('order/takegoods',['data'=>$result]);
    }
    /**
     * 已完成
     */
    public function complete()
    {
        $bid = $this->_getBid();
        $status = 3;
        $result = OrderModel::instance()->orderlist($bid,$status,'id desc');

        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '已完成';
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $result->offsetSet($k,$data);
        }
        return view('order/complete',['data'=>$result]);
    }

    /**
     * 已关闭
     */
    public function occlude()
    {
        $bid = $this->_getBid();
        $status = -1;
        $result = OrderModel::instance()->orderlist($bid,$status,'createtime desc');

        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '已关闭';
            $v['createtime'] == 0 ? $data['createtime'] = '' :$data['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $result->offsetSet($k,$data);
        }
        return view('order/occlude',['data'=>$result]);
    }
    /**
     * 详情
     */
    public function details(Request $request){
        $orderid = $request->get('orderid');
        $bid = $this->_getBid();
        $result = OrderModel::instance()->details($bid,$orderid);
        return view('order/occlude',['data'=>$result]);
    }
    /**
     * 详情
     */
    public function show(Request $request){
        $orderid = $request->get('id');
        $result = OrderModel::instance()->showlist($orderid);
        $numz  = $totalpricez = 0;
//        foreach ($result['goods'] as &$val){
//            $numz +=  $val['num'];
//            $totalpricez += $val['total_price'];
//        }
        switch ($result['status'])
        {
            case 0:
                $result['status'] = '待付款';
                break;
            case 1:
                    $result['status'] = '待接单';
                break;
            case 2:
                    $result['status'] = '配送中';
                break;
            case 3:
                    $result['status'] = '已完成';
                break;
            default:
                $result['status'] = '已取消';
        }
        $result['time'] == 0 ? $result['time'] = '' : $result['time'] = date('Y-m-d h:i:s',$result['time']);
        // var_dump($result['time']);die;
//        $result['time'] == 0 ? $result['time'] = '' : $result['time'] = date('Y-m-d h:i:s',$result['time']);
//        $result['time'] == 0 ? $result['time'] = '' : $result['time'] = date('Y-m-d h:i:s',$result['time']);
//        $result['time'] == 0 ? $result['time'] = '' : $result['time'] = date('Y-m-d h:i:s',$result['time']);
        return view('order/show',['data'=>$result]);
    }
    /**
     * 添加快递
     */
    public function addexpress(Request $request){
        $data = $request->get();
        $result = OrderModel::instance()->addexpress($data);
        if($result > 0){
            $this->success('添加成功', 'order/index');
        }else{
            $this->error('新增失败');
        }

    }

    /**
     * 对接禾匠申请
     */
    public function HeJiangShenQing(Request $request){
        $bid = $this->_getBid();
        $result = db('joint_order')->alias('jo')
            ->field('u.*,jo.jid,jo.bid,jo.hjmall_id,jo.uid,jo.uid,jo.status,jo.shenhe_time,jo.name as whechat,jo.appid,jo.shop_name,jo.is_zguanli,jo.type as type1')
            ->join('user u','u.uid = jo.uid','left')
            ->where(['u.bid'=>$bid,'jo.bid'=>$bid])
            ->order('jo.status asc')
            ->select();
        foreach ($result as &$value){
            if($value['type1']==1) {
                $value['type1'] =  '禾匠';
            }elseif($value['type1'] ==2){
                $value['type1'] =  '智慧外卖';
            }
            if(!$value['shop_name']){
                $value['shop_name'] = '';
            }
        }
        return view('order/hejiangshenqing',['data'=>$result]);
    }
    /**
     * 审核操作
     */
    public function ShenQinStatus(Request $request){
        $data = $request->get();
        if($data['status']==1){
            $result = db('joint_order')->where('jid',$data['jid'])->update(['status'=>$data['status'],'shenhe_time'=>time()]);
        }else if($data['status'] == 0){
            $result = db('joint_order')->where('jid',$data['jid'])->delete();
        }
        echo 1;die;
    }

//    /**
//     * 对接禾匠商城的订单表
//     */
//    public function HeJiangOrder(Request $request){
//        $HeJiangid = 7;
//        $sql = "select ho.id as hoid,ho.store_id,ho.name,ho.order_no,ho.mobile,ho.address,ho.pay_price,hg.name as goods_name,ho.is_send,ho.apply_delete,ho.is_delete,ho.is_pay = 1 from hjmall_order as ho LEFT JOIN hjmall_order_detail as hod on ho.id = hod.order_id LEFT JOIN hjmall_goods hg on hod.goods_id = hg.id where hg.store_id = ".$HeJiangid." AND ho.store_id = ".$HeJiangid." AND  ho.is_send = 0 AND ho.is_pay = 1 AND ho.apply_delete = 0 AND ho.is_delete = 0 ";
//        $result = Db::query($sql);
//        foreach ($result as $k =>$v){
//            if($v['is_send'] == 0){
//                $result[$k]['is_send'] = '待发货';
//            }
//        }
//        return view('order/hejiangorder',['data'=>$result]);
//    }

    /**
     * 对接禾匠商城的申请检查
     */
    public function Hejiang(Request $request){
        $jid = $request->get('jid');
        $acid = $request->get('hjmall_id');
        $joint_data = db('joint_order')->where('jid',$jid)->find();
        if($joint_data['type']==1){//判断属于哪个模块 1 代表禾匠
            $result1 = Db::table('hjmall_store')
                ->field('id,acid,user_id')
                ->where('acid',$acid)->find();
            $data = Db::table('ims_account_wxapp')->where('acid',$acid)->find();
            if(!$result1 || !$data){
                echo json_encode(['code'=>0,'mess'=>'未找到小程序id']);die;
            }
            $result2 = Db::table('hjmall_user')
                ->field('we7_uid')
                ->where(['id'=>$result1['user_id']])
                ->value('we7_uid');
            $result = Db::table('ims_users')
                ->where(['uid'=>$result2])
                ->value('uid');
            if(!$result){
                echo json_encode(['code'=>0,'mess'=>'不合格用户']);die;
            }
            if($joint_data['is_zguanli'] == 1){
                echo json_encode(['code'=>1,'mess'=>'申请合格平台管理员，可给予通过']);die;
            }else{
                $where = [
                    'is_delete'=>0,
                    'is_open'=>1,
                    'is_lock'=>0,
                    'review_status'=>1,
                ];
                $shop = Db::table('hjmall_mch')
                    ->where('store_id',$result1['id'])
                    ->where(['name'=>$joint_data['shop_name']])
                    ->where($where)
                    ->find();
                if($shop){
                    echo json_encode(['code'=>1,'mess'=>'申请合格店铺管理员，可给予通过']);die;
                }else{
                    echo json_encode(['code'=>0,'mess'=>'不合格店铺信息，请联系禾匠客服']);die;
                }
            }
        }
        if($joint_data['type']==2){//判断属于哪个模块 2 代表智慧外卖
            $map = [['wv.modules', 'like','%zh_cjdianc%']];
            $sql = 'select *  from ims_account_wxapp as aw left join ims_wxapp_versions as wv on aw.uniacid=wv.uniacid WHERE aw.acid = '.$acid.' and wv.modules like "%zh_cjdianc%" ';
            $data =  Db::query($sql);
            if(!$data){
                echo json_encode(['code'=>0,'mess'=>'未找到小程序id']);die;
            }
            if($joint_data['is_zguanli'] == 1){
                echo json_encode(['code'=>1,'mess'=>'申请合格平台管理员，可给予通过']);die;
            }else{
                $sql = " select * from ims_cjdc_store where name = ".$joint_data['shop_name']." and is_open = 1 and uniacid = ".$acid;
                $list  = Db::query($sql);
                if($list){
                    echo json_encode(['code'=>1,'mess'=>'申请合格店铺管理员，可给予通过']);die;
                }else{
                    echo json_encode(['code'=>0,'mess'=>'不合格店铺信息，请联系智慧客服']);die;
                }
            }
        }
    }
    
    
    /**
     * 资金
     * */
	public function capital(){
        $bid = $this->_getBid();
        $status = 3;
        $result = OrderModel::instance()->orderlist($bid,$status,'id desc');

        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['status'] = '已完成';
            $v['time'] == 0 ? $data['time'] = '' :$data['time'] = date('Y-m-d H:i:s',$v['time']);
            $MoneyArr=$v['price'];
            $result->offsetSet($k,$data);
        }
        $price = array_sum(Db::name('runorder')->alias('g')->join('135k_user u','g.uid = u.uid')
            ->field('g.id,g.uid,g.order_no,g.bid,g.price,g.time,g.payway,g.status,u.nickname,u.phone,g.oktime')
            ->where(['g.bid'=>$bid,'g.status'=>$status])
            ->column('price'));
        return view('',['data'=>$result,'money'=>$price]);

	}

	/**
     * 导出数据
     */
    public function excels(){
        $bid = $this->_getBid();
        $time=input('get.');

        $where['time']=[['gt',intval($time['starttime'])],['lt',intval($time['endtime'])]];
        $result =Db::name('runorder')->alias('g')->join('135k_user u','g.uid = u.uid')
            ->field('g.id,g.uid,g.order_no,g.bid,g.price,g.time,g.payway,g.status,u.nickname,u.phone,g.oktime')
            ->where(['g.bid'=>$bid,'g.status'=>3])
            ->order('id desc')
            ->where($where)
            ->select();

        $path = dirname(__FILE__); //找到当前脚本所在路径

        $PHPExcel = new PHPExcel();
        //        $PHPExcel_IOFactory = new PHPExcel_IOFactory();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("demo"); //给当前活动sheet设置名称
        $PHPSheet->setCellValue("A1", "ID")
            ->setCellValue("B1", "用户名")
            ->setCellValue("C1", "手机号")
            ->setCellValue("D1", "订单号")
            ->setCellValue("E1", "支付金额")
            ->setCellValue("F1", "支付时间");

        $d=2;

        foreach($result as $key=>$vo){

            $PHPSheet->setCellValue("A".$d,$vo['id'])
                ->setCellValue("B".$d,$vo['nickname'])
                ->setCellValue("C".$d,$vo['phone'])
                ->setCellValue("D".$d,$vo['order_no'])
                // ->setCellValue("E".$d,$vo['id_number'].' ')
                ->setCellValue("E".$d,$vo['price'])
                ->setCellValue("F".$d,date('Y-m-d H:i:s',$vo['time']));

            $d++;
        }
        //        exit;
        //        $PHPSheet->setCellValue("A2","张三")->setCellValue("B2","2121");//表格数据
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007");
        ob_end_clean(); // Added by me
        ob_start(); // Added by me
        header('Content-Disposition: attachment;filename="交易记录'.date('Y-m-d',time()).'.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output",$path); //表示在$path路径下面生成demo.xlsx文件
    }

    /**
     * 对接信息配置
     */
    public function DuijieOrder(){
        return view('order/duijieorder');
    }

    /**
     * 取消订单
     * 王哲
     * 2018-8-16
     */
    public function del_order(Request $request){
        $id=$request->get('id');
      	$sendInfo=$request->get('sendinfo');
        $result=Db::name('runorder')->where(['order_no'=>$id])->update(['status'=>-2,'sendinfo'=>$sendInfo]);
        if($result){
            echo json_encode(array('state'=>1));
        }else{
            echo json_encode(array('state'=>0));
        }
    }

    /**
     * 转单
     * 王哲
     * 2018-8-16
     */
    public function zr_order(Request $request){
        $id=$request->get('id');
        $result=Db::name('runorder')->where(['order_no'=>$id])->update(['status'=>1,'rid'=>0]);
        if($result){
            echo json_encode(array('state'=>1));
        }else{
            echo json_encode(array('state'=>0));
        }
    }


    /**
     * 添加订单
     * @return \think\response\View
     *
     */
    public function insertOrder(Request $request)
    {
        $data = $request->param();
        if ($data) {
            $order_no = trade_no();



            $code  = rand(1000,9999);
            $datas = [
                'goodsname' => $data['goodsname'],
                'mudadds' => $data['mudadds'],
                'myadds' => $data['myadds'],
                'price' => $data['price'],
                'times' => strtotime($data['times']),
                'time' => time(),
                'uid' => $data['uid'],
                'order_no' => $order_no,
                'order_type' => isset($data['order_type']) ? $data['order_type']: '',
                'distance' => isset($data['distance']) ? $data['distance']: 0,
                'old_order_no' => isset($data['old_order_no']) ? $data['old_order_no']: '',
              	 'weight' => isset($data['weight']) ? $data['weight']: '',
                 'select_name' => isset($data['select_name']) ? $data['select_name']: '',
                'ins' => $data['ins'],
                'status' => 0,
                'redbao' =>$data['redbao'],
                'xphoto' =>$data['xphoto'],
                'yinpin' =>$data['yinpin'],
                'tip' => $data['tip'],
                'type' => $data['type'],
                'message' => $data['message'],
                'distype' =>  $data['distype'],
                'username' =>  $data['username'],
                'phone' =>  $data['tel'],
                'bid' => $data['bid'],
                'weight' => $data['weight'],
                'payway' => 'pricePay',
                'audiotime' => empty($data['audiotime'])?"":$data['audiotime'],
                'imgurl' =>  empty($data['imgurl'])?"":$data['imgurl'],
                'proxy_id'=>$data['proxy_id'],
                'code'  =>$code
            ];

//            $url='https://restapi.amap.com/v3/geocode/geo';
//            $input=[
//                'address'=>$data['myadds'],
//                'key'=>'d81974e1b46b1d913ead63752fe8c434',
//            ];
//            $adds=json_decode(file_get_contents($url.'?'.http_build_query($input)),1);
//            if($adds['info']=='OK')$data['location']=$adds['geocodes'][0]['location'];

            $result = db('runorder')->insert($datas);

            /**
             * 检测禾匠订单并且修改发货状态
             */
            if (!empty($data['old_order_no']) && !empty($data['order_type']) && $data['order_type'] == 1) {
                $is = [
                    'is_send'=>1,
                    'send_time'=>time(),
                    'words'=>'跑腿小程序配送',
                ];
                Db::table('hjmall_order')->where('order_no',$data['old_order_no'])->update($is);
            } else if (!empty($data['old_order_no']) && !empty($data['order_type']) && $data['order_type'] == 2) {
                $is = [
                    'state'=>3,
                    'jd_time'=>date("Y-m-d H:i:s",time())
                ];
                Db::table('ims_cjdc_order')->where('order_num',$data['old_order_no'])->update($is);
            }
            if ($result) {
                $result = $this-> pricePay($data['uid'],null,null,$order_no);
                if ($result['code']==1)
                {
                    \app\api\controller\Order::sendMsg();
                    $this -> success('添加成功','order/index');
                }
                else
                {
                    $this -> error($result['msg'],'order/index');
                }

            }else{
                $this -> error('失败了','order/index');
            }
        } else {
            $this -> error('失败了','order/index');
        }


    }


    /**
     * 余额支付
     */
    public function pricePay($uid,$formId,$openid,$order_no){

            $MyMoney=db('User')->where('uid',$uid)->value('money');
            $money=db('Runorder')->where('order_no',$order_no)->field('code,phone,price')->find();
            $phone=$money['phone'];
            $code =$money['code'];
            $money =$money['price'];

            if($MyMoney<$money){
                return array('code'=>0,'msg'=>'余额不足');
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
                    Sms::instance()->index($phone,$code);
                    MessageModel::PayMsg($data['order_no']);
                    return array('code'=>1);
                }else{
                    return array('code'=>0,'msg'=>'支付失败');
                }
            }

    }

    /**
     * 修改订单页面
     * @param Request $request
     * @return \think\response\View
     */
    public function edit (Request $request) {
        $id = $request -> param('id');
        if (!empty($id)) {
            $result = OrderModel::instance() -> edit($id);
            return view('order/edit',['data'=>$result]);
        } else {
              $this -> error('错误操作');
        }
    }

    /**
     * @param Request $request
     */
    public function editPost (Request $request) {
        $data = $request->param();
        if ($data) {

            $datas = [
                'times' => strtotime($data['times']),
                'goodsname' => $data['goodsname'],
                'mudadds' => $data['mudadds'],
                'myadds' => $data['myadds'],
                'price' => $data['price'],
                'order_no' => $data['order_no'],
                'status' => $data['status'],
                'redbao' =>$data['redbao'],
                'tip' => $data['tip'],
                'worth' => $data['worth'],
                'weight' => $data['weight'],
                'type' => $data['type'],
                'message' => $data['message'],
                'distype' =>  $data['distype'],
                'username' =>  $data['username'],
                'phone' =>  $data['phone'],
                'code'  =>$data['code']
            ];
            $result = db('runorder')->where('id',$data['id'])->update($datas);

            if ($result) {

                $this -> success('修改成功','order/index');

            }else{
                $this -> error('修改失败','order/index');
            }
        } else {
            $this -> error('错误操作','order/index');
        }
    }


    public function playMsg($uid,$bid){
        $result=OrderModel::playMsg($uid,$bid);

        if($result) {echo json_encode(['code'=>1,'msg'=>'有新的订单']);}
        else echo json_encode(['code'=>0,'msg'=>'暂无订单']);
    }


}