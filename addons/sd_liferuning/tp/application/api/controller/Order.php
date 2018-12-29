<?php
namespace app\api\controller;

use app\api\model\OrderModel;
use app\api\model\GoodsAttrModel;
use app\api\model\AddressModel;
use app\api\model\CommonModel;
use app\home\model\BusinessModel;
use think\Controller;
use think\Request;
use think\Cache;
use think\db;
use app\home\model\MessageModel;
use app\conmon\sendSms;
use app\home\model\UserModel as HomeUserModel;

class Order extends Controller
{
    /**
     * 添加订单
     * @return \think\response\View

     */

    public static function curl_get($url){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);// 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,2);// 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return $tmpInfo;    //返回json对象
    }



    public static function postMsg($accesstoken,$data){

        //$url为地址
//$post_data为数组
        // $url="https://mp.weixin.qq.com/wxopen/tmplmsg?action=self_list&token=".$accesstoken."&lang=zh_CN";
        // $url="https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=".$accesstoken;
        $url="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$accesstoken;
        $post_data = json_encode($data,JSON_UNESCAPED_UNICODE);
        sleep(1);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            echo 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl);
        return json_decode($output,true);

    }

    public static function sendMsg(){
        $res = db('runorder')
            ->field('time,mudadds,myadds,price')
            ->where(['status'=>1])
            ->find();
        if(count($res)>=1){
            $msg=[];
            $where = array(
                'is_on'=>1,
                'status'=>3,
                'is_form'=>1,
                'formId'=>['neq',''],
                'open_id'=>['neq',''],
            );

            $res1 = db('CustUser')
                ->field('cid,open_id,formId')
                ->where($where)
                ->select();
            //var_dump($res1);exit;
            $cid = array();
            foreach ($res1 as $k=>$v){
                $res['price'] = OrderModel::instance()->f_price($res['price']);
                $data = array(
                    date("Y年m月d日 H:i:s",$res['time']),
                    $res['mudadds'],
                    $res['myadds'],
                    $res['price']
                );
                $msg[] =  \app\api\model\MessageModel::sendMsg($v['open_id'],$v['formId'],$data,'oeGoSj02a4ocZnO548z4W-Xofz6pwRgCiBRJ54uPvRc');
                $cid[] = $v['cid'];
               /* $datass=array(
                    "touser"=>$v['open_id'],//openid
                    "template_id"=>"oeGoSj02a4ocZnO548z4W-Xofz6pwRgCiBRJ54uPvRc",
                    "page"=>"service/pages/service/index/index",
                    "form_id"=>$v['formId'],
                    "data"=>array(

                        "keyword1"=>array(
                            "value"=> date("Y年m月d日 H:i:s",$res['time']),
                            "color"=>"#173177"
                        ),
                        "keyword2"=>array(
                            "value"=> $res['mudadds'],
                            "color"=>"#173177"
                        ),
                        "keyword3"=>array(
                            "value"=> $res['myadds'],
                            "color"=>"#173177"
                        ),
                        "keyword4"=>array(
                            "value"=> ($res['price']*0.8),
                            "color"=>"#173177"
                        )

                    )

                );

                $accesstoekn=self::curl_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx18d3e1aa8b3fb5dd&secret=530202d48fb9b9b0160ffedf43b9696f");
                $accesstoekn = json_decode($accesstoekn);
                $msg[] = self::postMsg($accesstoekn->access_token,$datass);*/

            }
            //dump($msg);
            db('CustUser')
                ->where(['cid'=>['IN',$cid]])
                -> update(['is_form'=>0]);
            return $msg;
        }
        return "未查到订单数据";

    }

    public function insertOrder(Request $request)
    {


        //$this->sendMsg();exit;



        $data = $request->param();

        if($data){
            $times    = BusinessModel::getOpenTime($data['bid']);
            $thisTime = date('H:i:s');
            if ($thisTime<$times['openTime'] || $thisTime > $times['closeTime']) {
                echo json_encode(['data' => 3,'msg' => '商家已經打樣']);exit;
            }
            $order_no = trade_no();

            $code  = rand(1000,9999);
            $datas = [
                'goodsname' => $data['goodsname'],
                'mudadds' => $data['mudadds'],
                'myadds' => $data['myadds'],
                'price' => $data['price'],
                'times' => $data['times'],
                'time' => time(),
                'uid' => $data['uid'],
                'order_no' => $order_no,
                'order_type' => isset($data['order_type']) ? $data['order_type']: '',
                'distance' => isset($data['distance']) ? $data['distance']: '',
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
                'phone' =>  $data['phone'],
                'bid' => $data['bid'],
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
            if(!empty($data['old_order_no']) && !empty($data['order_type']) && $data['order_type'] == 1){
                $is = [
                    'is_send'=>1,
                    'send_time'=>time(),
                    'words'=>'跑腿小程序配送',
                ];
                Db::table('hjmall_order')->where('order_no',$data['old_order_no'])->update($is);
            }else if(!empty($data['old_order_no']) && !empty($data['order_type']) && $data['order_type'] == 2){
                $is = [
                    'state'=>3,
                    'jd_time'=>date("Y-m-d H:i:s",time())
                ];
                Db::table('ims_cjdc_order')->where('order_num',$data['old_order_no'])->update($is);
            }

            if($result){

                $info['order_no'] =$order_no;
                $info['msg'] =$result;
                $this->jsonOut($info);
            }else{
                echo json_encode(['data' => 0,'msg'=>'添加失敗']);
            }
        }else{
            echo json_encode(['data' => 0,'msg'=>'錯誤操作']);
        }
    }







    public function insertOrders(Request $request)
    {
        $data = $request->param();
        //var_dump($data['goodsname']);die;
        if($data){
            $order_no = trade_no();
            $datas = [
                'goodsname' => $data['goodsname'],
                'mudadds' => $data['mudadds'],
                'myadds' => $data['myadds'],
                'price' => $data['price'],
                'times' => $data['times'],
                'time' => time(),
                'uid' => $data['uid'],
                'order_no' => $order_no,
                'status' => 0,
                'redbao' =>$data['times'],
                'tip' => $data['times'],
                'type' => $data['type'],
                'worth'=> $data['worth'],
                'weight'=> $data['weight'],
                'audiotime' => $data['audiotime'],
                'message' => $data['message'],
                'bid' => $data['bid']
            ];
            $result = db('runorder')->insert($datas);
            if($result){
                $info =$order_no;
                $this->jsonOut($info);
            }else{
                echo json_encode(['data' => 0]);
            }
        }else{
            echo json_encode(['data' => 0]);
        }
    }
    /***
     * 获取门票订单参数
     */
    public function insertPrice($bid,$uid){
        if(\request()->isGet()){
            if(intval($uid)<1){
                exit(json_encode(['code'=>0]));
            }
            $order_no = trade_no();
            $Goods=db('Goods')->where('bid',$bid)->find();

            $data=[
                'order_no'=>$order_no,
                'gid'=>0,
                'bid'=>$bid,
                'money'=>$Goods['min_price'],
                'createtime'=>time(),
                'uid'=>$uid
            ];
            if(db('price_order')->insert($data)){
                exit(json_encode(['code'=>1,'order_no'=>$data['order_no']]));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'获取失败']));
            }

        }else{
            exit();
        }
    }
    /**
     * 获取订单支付参数
     *
     */
    public function orderPayParams(Request $request)
    {
        $order_no = $request->param('order_no');     //订单号
        $id=$request->param('_acid');
        $acid=db('business')->field('name')->where(['uniacid'=>$id])->find();
        if(!empty($acid['name'])){
            $title=$acid['name'];
        }else{
            $title = $request->param('title');       //订单描述
        }
        if(empty($order_no)) $this->outPut(null, 1001, "order_no");
        if(empty($title)) $this->outPut(null, 1001, "title");
        $uid = $this->uid;
        //var_Dump($uid);
        $res_parms = OrderModel::instance()->getOrderPayParams($order_no,$title,$uid);
        if (!$res_parms) {
            $this->outPut(null, 1001);
        }
        $this->jsonOut($res_parms);
    }
    /**
     * 退款
     */
    public function out_price($order_no){

        if(\request()->isPost()){
            $result = OrderModel::instance()->out_money($order_no);
//            var_dump($result);die;
            if($result){
                exit(json_encode(['code'=>1,'msg'=>'退款成功']));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'退款失败']));
            }
        }
    }

    private function timediff($begin_time,$end_time)
    {
        if($begin_time < $end_time){
            $starttime = $begin_time;
            $endtime = $end_time;
        }else{
            $starttime = $end_time;
            $endtime = $begin_time;
        }
        //计算天数
        $timediff = $endtime-$starttime;
        $days = intval($timediff/86400);
        //计算小时数
        $remain = $timediff%86400;
        $hours = intval($remain/3600);
        //计算分钟数
        $remain = $remain%3600;
        $mins = intval($remain/60);
        //计算秒数
        $secs = $remain%60;
        $res =  $days.'天 '. $hours.'时'. $mins.'分'.$secs.'秒';
        return $res;
    }

    /**
     * 我的订单 订单列表
     *
     */
    public function getOrderLists(Request $request){
        $this->_isGET();
        $status = $request->get('status');
        $limit=$request->get('limit');
        $result = OrderModel::instance()->getOrderLists($status,$this->uid,$limit);
        foreach ($result as $key=>$val){
            $result[$key]['look_time']='待接单';
            switch ($val['status']){
                case -1:
                    $result[$key]['status']='已取消';
                    break;
                case -2:
                    $result[$key]['status']='已取消，请自行退款';
                    break;
                case 1:
                    $result[$key]['status']='待接单';
                    $result[$key]['look_time']="已下单：".$this ->timediff(time(),$val['time']);
                    break;
                case 2:
                    $result[$key]['status']='配送中';
                    $result[$key]['look_time']="已配送：".$this ->timediff(time(),$val['givetime']);
                    break;
                case 3:
                    $result[$key]['status']='已完成';
                    $result[$key]['look_time']="总花费：".$this ->timediff($val['oktime'],$val['time']);
                    break;
                case 4:
                    $result[$key]['status']='已取消';
                    break;

            }
            $result[$key]['time']=date('Y-m-d H:i:s',$val['time']);
        }
        $count = OrderModel::instance() -> getAllOrder($this->uid);
        $array = array('data'=>$result,'count'=>$count);
        echo json_encode($array);
        //$this->jsonOut($result);


    }

    /**
     * 我的订单 订单列表
     *
     */
    public function getOrderListss(Request $request){
        $this->_isGET();
        $status = $request->get('status');
        $limit=$request->get('limit');
        $type=$request->get('type');
        $result = OrderModel::instance()->getOrderListss($status,$this->uid,$limit,$type);

        foreach ($result as $key=>$val){
            $result[$key]['time']=date('Y-m-d H:i:s',$val['time']);


            switch ($val['status']){
                case -1:
                    $result[$key]['status']='已取消';
                    break;
                case -2:
                    $result[$key]['status']='已取消，请自行退款';
                    break;
                case 1:
                    $result[$key]['status']='待接单';
                    break;
                case 2:
                    $result[$key]['status']='配送中';
                    break;
                case 3:
                    $result[$key]['status']='已完成';
                    break;
                case 4:
                    $result[$key]['status']='已取消';
                    break;

            }
        }
        $this->jsonOut($result);
    }
    /**
     * 取消订单
     */
    public function delOrder(Request $request){
        $this->_isPOST();
        $orderid = $request->post('orderid');
        $res = OrderModel::instance()->delOrder($orderid,$this->uid);
        if ($res == false) {
            $this->outPut(null, 0);
        }
        $this->jsonOut(['orderid'=>$orderid]);
    }
    /**
     * 订单详情
     */
    public function getOneOrder(Request $request){
        $orderid = $request->get('orderid');
        $result = OrderModel::instance()->getOneOrder($orderid);
        $this->jsonOut($result);
    }
    /**
     * 确认收货
     */
    public function takeGoods(Request $request){
        $this->_isGET();
        $orderid = $request->get('orderid');
        if(empty($orderid)) $this->outPut('', 1001, ":orderid" );
        list($result,$info) = OrderModel::instance()->takeGoods($orderid,$this->uid);
        if($result == false)  $this->outPut(null,$info);
        $this->jsonOut($info);
    }
    /**
     * 充值
     */
    public function rechargePay(Request $request){

        $money = $request->get('money');       //订单描述

        if(empty($money)) $this->outPut(null, 1001, "money");
        $uid = $this->uid;
        $res_parms = OrderModel::instance()->rechargePay($money,$uid,trade_no());
        if (!$res_parms) {
            $this->outPut(null, 1001);
        }
        $this->jsonOut($res_parms);
    }
    /**
     * 余额/积分 购买
     */
    public function orderPayIntegral(Request $request){
        $this->_isPOST();
        $order_no = $request->post('order_no');     //订单号
        $type = $request->post('type');       // 1：余额购买  2：积分购买
        if(empty($order_no)) $this->outPut(null, 1001, "order_no");
        if(!$type) $this->outPut(null, 1001, "type");
        $uid = $this->uid;
        list($result,$info) = OrderModel::instance()->getorderPayIntegral($order_no,$type,$uid);
        if($result == false)  $this->outPut(null,$info);
        $this->jsonOut($info);
    }
    /**
     * 充值记录
     */
    public function recharge(Request $request){
        $this->_isGET();
        $uid = $request->get('uid');
        $bid = $request->get('bid');
        $result = OrderModel::instance()->recharge($uid,$bid);
        $this->jsonOut($result);
    }
    /**
     * 订单详情
     */
    public function getOrderInfo(Request $request){
        $order = $request->get('orderid');

        $result = OrderModel::instance()->getOneOrder($order);
        $this->jsonOut($result);
    }

    /**
     * 评论提交
     */
    public function commentSave(Request $request){
        $num_star = $request -> post('num',0,'intval');
        $order_id = $request -> post('orderNo',0,'intval');
        $why_text = $request -> post('comment');
        if (empty($order_id)) {
            $this -> outPut('false',1001);
        }
        if (empty($num_star)) {
            $this -> outPut('false',1001,'您还未评论星级');
        }

        $result = OrderModel::instance()->comment($order_id,$num_star,$why_text);
        $this->jsonOut($result);

    }


    /**
     * 服务端订单列表
     */
    public function ServerOrder(Request $request){

        $bid = $request->get('bid');
        $status = $request->get('status');
        $thisStatic = $request->get('thisStatic');
        $rid = $request->get('uid');
        $limit=$request->get('limit');
        $result = OrderModel::instance()->ServerOrder($bid,$status,$rid,$limit);
        if (!$thisStatic) {
            $count = OrderModel::instance() -> OrderCount($bid,$rid);
            $array['count'] = $count;
        }
        $array['data'] = $result;
        echo json_encode($array);

        //$this->jsonOut($result);
    }
    /**
     * 服务端订单详情
     */
    public function ServerOrderInfor(Request $request){
        $orderid = $request->get('orderid');
        $result = OrderModel::instance()->ServerOrderInfor($orderid);
        $this->jsonOut($result);
    }
    /**
     * 跑腿抢单
     */
    public function GiveOrder(Request $request){

        $orderid = $request->get('orderid');
        $rid = $request->get('uid');
        $bid = $request->get('bid');

        $result = OrderModel::instance()->GiveOrder($orderid,$rid,$bid);
        switch ($result)
        {
            case -1:
                return json_encode(['status'=>-1]);
                break;
            case 1:
                return json_encode(['code'=>1,'data'=>1]);
                break;
            default:
                return json_encode(['status'=>0]);
        }
        /** 发送收货码 todo 完善**/

        /*if($result!=-1){

            $sms = new sendSms();
            if(!$bid){
                return json_encode(['status'=>0,'code'=>0,'table'=>'未设置商户id']);
            };
            $list=db('business')->field('distype')->where(['bid'=>$bid])->find();
            $data = db('run_yards')->where('bid',$bid)->find();
            if(empty($data)){
                return json_encode(['status'=>0,'code'=>0,'table'=>'未设置短信配置信息']);
            }
            $order = db('runorder')->where('id',$orderid)->find();
            $usrphone = db('runorder')->where('uid',$order['uid'])->field('phone')->find();
            $runphone = db('user')->where('uid',$order['rid'])->field('phone')->find();
            $code = rand(1000,9999);
            if($list['distype']==1){
                $results = $sms->sendSms($data,$code,$usrphone['phone']);
                if($results['Code']=='OK'){
                    db('runorder')->where(['id'=>$orderid])->update(['code'=>$code]);
                    $this->jsonOut($result);
                    return json_encode(['status'=>1,'code'=>$code,'table'=>'发送验证码成功']);
                }
            }else{
                return json_encode(['code'=>1,'data'=>1]);
            }

        }else{
            return json_encode(['status'=>-1]);
        }
        $this->jsonOut($result);*/
    }
    /**
     * 跑腿完成订单
     */
    public function OkOrder(Request $request){
        $orderid = $request->get('orderid');
        $bid = $request->get('bid');
        $bids  = Db::name('business')
            ->where(['bid'=>$bid])
            ->field('distype')
            ->find();
        $uid = $request->get('uid');
        $code=$request->get('code');
        $result = OrderModel::instance()->OkOrder($orderid,$bid,$uid,$code,$bids['distype']);
        //查询奖励条件
        $map['reendtime'] = array('>',time());
        $dis = db('reward')
            ->where('bid',$bid)
            ->where('type',1)
            ->where($map)
            ->order('fulfil_the_quota asc')
            ->select();
        //查询是否达到满额奖励资格
        foreach ($dis as $k => $v){
            $money = db('cust_user')
                ->where('uid',$uid)
                ->find();
            if($money['money'] >= $v['fulfil_the_quota']){
                if(!$money['manejilu']){
                    var_dump('44');
                    $money  = $money['money'] + $v['reward'];
                    db('cust_user')
                        ->where('uid',$uid)
                        ->update(['money'=>$money,'manejilu'=>$v['fulfil_the_quota']]);
                }elseif($money['manejilu'] != $v['fulfil_the_quota'] && $money['manejilu'] < $money['money'] && $money['manejilu'] < $v['fulfil_the_quota']){
                    var_dump('44');
                    $money  = $money['money'] + $v['reward'];
                    db('cust_user')
                        ->where('uid',$uid)
                        ->update(['money'=>$money,'manejilu'=>$v['fulfil_the_quota']]);
                }
            }
        }
        $this->jsonOut($result);
    } /**
 * 跑腿完成订单
 */
    public function OkWorth(Request $request){
        $orderid = $request->get('orderid');
        $code=$request->get('code');
        $result = OrderModel::instance()->OkWorth($orderid,$code);
        $this->jsonOut($result);
    }
    /**
     * 跑腿完成订单
     */
    public function Income(Request $request){
        $cid = $request->get('cid');
        $result = OrderModel::instance()->Income($cid);
        $this->jsonOut($result);
    }
    /**
     * 跑腿押金退款记录
     */
    public function out_balance($bid,$cid){
        if(\request()->isPost()){
            $user=db('Cust_user')->where('cid',$cid)->find();
            if(empty($user)){
                exit(json_encode(['code'=>0,'msg'=>'获取用户信息出错']));
            }
            $save=[
                'cashstatus'=>-1,
                'promisemoney'=>0
            ];
            if(db('Cust_user')->where('cid',$cid)->update($save)){
                $data=[
                    'bid'=>$bid,
                    'cid'=>$user['cid'],
                    'moeny'=>$user['promisemoney'],
                    'createtime'=>time(),
                    'status'=>0
                ];
                $result=db('Out_balance')->insert($data);
                if($result){
                    exit(json_encode(['code'=>1,'msg'=>'退款成功']));
                }else{
                    db('Cust_user')->where('cid',$cid)->update($user);//回退操作
                    exit(json_encode(['code'=>0,'msg'=>'退款失败']));
                }
            }else{
                exit(json_encode(['code'=>0,'msg'=>'退款失败']));
            }

        }else{
            exit();
        }
    }
    /**
     * 选择时间
     */
    public function Code(){
        $getTime = input('time');
        $dangqianTmine = date("N",time());//星期数
        $timehour = date('H',time());
        $timeI = date("i",time());
        $x = 30*60;
        $y =  $getTime*24*60*60;
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));//当天开始的时间戳
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;//当天结束的时间戳
        if($getTime == 0) {
            if($timeI >= 30){
                $timehour = $timehour+1;
                $sj = date('Y-m-d '.$timehour.':',time());
                $sj = $sj.'00';
                $dtksTime = strtotime($sj);//当天开始输出的时间戳
            }else{
                $sj = date('Y-m-d '.$timehour,time());
                $sj = $sj.':30';
                $dtksTime = strtotime($sj);//当天开始输出的时间戳
            }
            $resultTime = [];
            for ($i = $dtksTime;$i<=$endToday;$i+=$x){
                array_push($resultTime,['name'=>date('H:i:s',$i),'timechuo'=>$i]);
            }
        }else{
            $resultTime = [];
            for ($i = $beginToday+$y;$i<=$endToday+$y;$i+=$x){
                array_push($resultTime,['name'=>date('H:i:s',$i),'timechuo'=>$i]);
            }
        }
        $this->jsonOut($resultTime);
    }
    /**
     * 获取用户余额
     */
    public function balance($uid){
        $result=db('User')->where('uid',$uid)->value('money');
        exit(json_encode(['code'=>1,'data'=>number_format($result, 2)]));

    }
    /**
     * 收支明细
     */
    public function priceMsg($uid){
        $data=db('PriceMsg')->where(['uid'=>$uid,'cust'=>0])->order('createtime desc')->select();

        foreach ($data as $key=>$val){
            $data[$key]['createtime']=date('Y-m-d H:i:s',$val['createtime']);
            $data[$key]['money']=$val['paytype']=='pay'?'-'.$val['money']:'+'.$val['money'];
        }

        exit(json_encode(['code'=>1,'msg'=>'收支明细','data'=>$data]));
    }
    /**
     * 本月支出
     */
    public function orderMsg($uid){
        $time=strtotime(date('Y-m'));
        $data=db('Runorder')->where(['uid'=>$uid,'status'=>['neq',-1],'status'=>['neq',0]])->where(['time'=>['gt',$time]])->order('time desc')->select();
        $arr=array_column($data,'price');
        $money=array_sum($arr);
        exit(json_encode(['code'=>1,'money'=>$money]));
    }
    /**
     * 余额支付
     */
    public function pricePay($uid,$formId,$openid,$order_no){
        if(\request()->isPost()){
            $MyMoney=db('User')->where('uid',$uid)->value('money');
            $money=db('Runorder')->where('order_no',$order_no)->field('code,phone,price')->find();
            $phone=$money['phone'];
            $code =$money['code'];
            $money =$money['price'];

            if($MyMoney<$money){
                exit(json_encode(['code'=>0,'msg'=>'余额不足']));
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
                    echo json_encode(['code'=>1,'msg'=>'支付成功']);
                    model('Sms')->index($phone,$code);
                    fastcgi_finish_request();
                    sleep(1);
                    MessageModel::PayMsg($data['order_no']);
                    //OrderModel::SendMsg($order_no);
                    $result = Order::sendMsg();
                    return false;
                }else{
                    exit(json_encode(['code'=>0,'msg'=>'支付失败']));
                }
            }

        }

    }
    /**
     * 余额支付2.0
     */
    public function yezfbzj($uid,$order_no,$money){
        if(\request()->isPost()){

            $MyMoney=db('User')->where('uid',$uid)->value('money');


            if($MyMoney<$money){
                exit(json_encode(['code'=>0,'msg'=>'余额不足']));
            }else{
                $data=[
                    'uid'=>$uid,
                    'price'=>$MyMoney-$money,
                    'order_no'=>$order_no,
                ];
                $result=OrderModel::pricePay2($data);
                if($result){
                    echo json_encode(['code'=>1,'msg'=>'支付成功']);
                    fastcgi_finish_request();
                    sleep(1);
                    MessageModel::PayMsg2($data['order_no']);
                    return false;
                }else{
                    exit(json_encode(['code'=>0,'msg'=>'支付失败']));
                }
            }

        }

    }
    /**
     * 判断是否提交过审核和审核状态
     */
    public function Is_ShenHe($uid,$bid){
        $data = [
            'uid'=>$uid,
            'bid'=>$bid
        ];
        $datalist = db('joint_order')->where(['uid'=>$data['uid'],'bid'=>$data['bid']])
            ->order('jid asc')
            ->select();
        if(!empty($datalist)){
            foreach ($datalist as $k => $data){
                if($data['status']==-1){
                    $result=HomeUserModel::userContent($bid);
                    unset($result['menu_list']);
                    foreach ($result['menus'] as $k =>$v){
                        if($v['id']=='guanli'){
                            $result['menus'][$k]['url'] = '/service/pages/module-mananger/auth-status/index';
                        }
                    }
                    echo json_encode(['code'=>99,'mess'=>'审核正在等待审核','data'=>$result]);
                }elseif ($data['status']==1){
                    echo json_encode(['code'=>1,'mess'=>'审核已同意']);die;
                }
            }
//            elseif ($data['status']==0){
//                $result=HomeUserModel::userContent($bid);
//                unset($result['menu_list']);
//                foreach ($result['menus'] as &$v){
//                    if($v['id']=='guanli'){
//                        $v['url'] = '/service/pages/module-mananger/authentication/index';
//                    }
//                }
//                echo json_encode(['code'=>0,'mess'=>'申请已拒绝，可从新提交申请','data'=>$result]);
//            }
        }else{
            $result=HomeUserModel::userContent($bid);
            unset($result['menu_list']);
            foreach ($result['menus'] as &$v){
                if($v['id']=='guanli'){
                    $v['url'] = '/service/pages/module-mananger/authentication/index';
                }
            }
            echo json_encode(['code'=>0,'mess'=>'未提交审核','data'=>$result]);
        }
    }
    /**
     *可对接的模块列表
     */
    public function YesJoint(){
        $result = db('joint')->where('status',1)->select();
        if($result){
            echo json_encode(['code'=>1,'mess'=>$result]);
        }else{
            echo json_encode(['code'=>0,'mess'=>$result]);
        }
    }
    /**
     * 提交对接申请
     */
    public function sumbit_ShenHe(Request $request){
        $data = $request->post();
        $insrte = [
            'uid'=>$data['uid'],
            'bid'=>$data['bid'],
            'name'=>$data['name'],
            'appid'=>$data['appid'],
            'hjmall_id'=>$data['hjmall_id'],
            'is_zguanli'=>$data['is_zguanli'],
            'shop_name'=>$data['shop_name'],
            'type'=>$data['type'],
        ];
        $result = db('joint_order')->insertGetId($insrte);
        if($result){
            echo json_encode(['code'=>1,'mess'=>'提交审核成功']);
        }else{
            echo json_encode(['code'=>0,'mess'=>'提交审核失败，请重试']);
        }
    }
    /**
     * 查询对接完成后的订单
     */
    public function HeJiangOrder(Request $request){
        $data = $request->get();
        /** 未推送的订单列表*/
        $datalist = db('joint_order')->where(['uid'=>$data['uid'],'bid'=>$data['bid'],'status'=>1])->select();
//        $result = '';
//        $result1 = '';
//        $list = '';
//        $list1 = '';
        foreach ($datalist as $k => $data){
            if($data['type']==1){
                if($data['is_zguanli']==0){
                    $mch_id = Db::table('hjmall_mch')->where('name',$data['name'])->value('id');
                    $where = 'and ho.mch_id='.$mch_id;
                }else{
                    $where = '';
                }
                $result = Db::table('hjmall_store')
                    ->field('id,acid,user_id')
                    ->where('acid',$data['hjmall_id'])->find();
                $HeJiangid = $result['id'];
                $sql = "select ho.id as hoid,ho.store_id,ho.addtime,ho.name,ho.order_no,ho.mobile,ho.address,ho.pay_price,hg.name as goods_name,is_send  from hjmall_order as ho LEFT JOIN hjmall_order_detail as hod on ho.id = hod.order_id LEFT JOIN hjmall_goods hg on hod.goods_id = hg.id where hg.store_id = ".$HeJiangid." AND ho.store_id = ".$HeJiangid." AND  ho.is_send = 0 ".$where." AND ho.is_pay = 1 AND ho.apply_delete = 0 AND ho.is_delete = 0 order by addtime desc";
                $result = Db::query($sql);
                foreach ($result as $k =>$v){
                    if($v['is_send'] == 0){
                        $result[$k]['is_send'] = '待发货';
                        $result[$k]['addtime'] = $this->formatTime(date('Y-m-d H:i:s',$v['addtime']));
                    }
                    $result[$k]['addtime1'] = $v['addtime'];
                    $result[$k]['laiyuan'] = $data['name'];
                    $result[$k]['laiyuan_id'] = $data['type'];
                }
                /** 已推送的订单 */
                $bid = $data['bid'];
                $where = array(
                    'uid'=>$data['uid'],
                    'bid'=>$bid,
                    'order_type'=>$data['type']
                );
                $list = db('runorder')->where($where)->select();
                foreach ($list as $k =>$v){
                    $list[$k]['time'] = $this->formatTime(date('Y-m-d H:i:s',$v['time']));
                    $list[$k]['time1'] = $v['time'];
                    $list[$k]['laiyuan'] = $data['name'];
                }
            }elseif($data['type']==2){
                //代发货的订单
                if($data['is_zguanli']==0){
                    $mch_id = Db::table('ims_cjdc_store')->where('name',$data['shop_name'])->value('id');
                    $where = 'and co.store_id='.$mch_id;
                }else{
                    $where = '';
                }
                $sql = "select co.id as hoid,co.store_id,co.order_num as order_no,co.name,co.tel as mobile,cg.name as goods_name,cg.money as pay_price,co.address as address,co.time as addtime,co.state as is_send from ims_cjdc_order as co left join ims_cjdc_order_goods as cog on co.id = cog.order_id LEFT JOIN ims_cjdc_goods as cg on cog.dishes_id = cg.id where co.uniacid =".$data['hjmall_id']." and cog.uniacid =".$data['hjmall_id']." and cg.uniacid = ".$data['hjmall_id']." and co.del = 2 and  co.state = 2 and co.pay_type = 1".$where." order by addtime desc";
                $result1 = Db::query($sql);
                foreach ($result1 as $k =>$v){
                    if($v['is_send'] == 2){
                        $result1[$k]['is_send'] = '待发货';
                        $result1[$k]['addtime'] = $this->formatTime($v['addtime']);
                    }
                    $result1[$k]['addtime1'] = strtotime($v['addtime']);
                    $result1[$k]['laiyuan'] = $data['name'];
                    $result1[$k]['laiyuan_id'] = $data['type'];
                }
                $bid = $data['bid'];
                $where = array(
                    'uid'=>$data['uid'],
                    'bid'=>$bid,
                    'order_type'=>$data['type']
                );
                $list1 = db('runorder')->where($where)->select();
                foreach ($list1 as $k =>$v){
                    $list1[$k]['time'] = $this->formatTime(date('Y-m-d H:i:s',$v['time']));
                    $list1[$k]['time1'] = $v['time'];
                    $list1[$k]['laiyuan'] = $data['name'];
                }
            }
        }
        if(isset($result) && isset($result1)){
            $result = array_merge($result,$result1);
        }else{
            if(isset($result)){
                $result = $result;
            }
            if(isset($result1)){
                $result = $result1;
            }
        }
        if(isset($list) && isset($list1)){
            $list = array_merge($list,$list1);
        }else{
            if(isset($list)){
                $list = $list;
            }
            if(isset($list1)){
                $list = $list1;
            }
        }
        $result = list_sort_by($result,'addtime1','desc');
        $list = list_sort_by($list,'time1','desc');
        $orderArr = array(
            array(
                'id'=>0,
                'wareArr'=>$result
            ),
            array(
                'id'=>1,
                'wareArr'=>$list
            )
        );
        echo json_encode($orderArr);
    }
    /**
     * 获取要推送的订单信息
     */
    public function TuiSongList(Request $request){
        $hoid = $request->get('id');
        $type_status = $request->get('type_status');
        if($type_status == 1){
            $sql = "select ho.id as hoid,ho.store_id,ho.addtime,ho.name,ho.order_no,ho.mobile,ho.address,ho.pay_price,hg.name as goods_name,ho.is_send,ho.apply_delete,ho.is_delete,ho.is_pay from hjmall_order as ho LEFT JOIN hjmall_order_detail as hod on ho.id = hod.order_id LEFT JOIN hjmall_goods hg on hod.goods_id = hg.id where ho.id =".$hoid;
            $result = Db::query($sql);
            foreach ($result as $k =>$v){
                echo json_encode($v);die;
            }
        }elseif($type_status == 2){
            $sql = "select co.id as hoid,co.store_id,co.order_num as order_no,co.name,co.tel as mobile,cg.name as goods_name,cg.money as pay_price,co.address as address,co.time as addtime,co.state as is_send from ims_cjdc_order as co left join ims_cjdc_order_goods as cog on co.id = cog.order_id LEFT JOIN ims_cjdc_goods as cg on cog.dishes_id = cg.id where co.id=".$hoid;
            $result = Db::query($sql);
            foreach ($result as $k =>$v){
                echo json_encode($v);die;
            }
        }
    }
    /**
     * 时间转几分钟前  几天前方法
     */
    public function formatTime($date) {
        $str = '';
        $timer = strtotime($date);
        $diff = $_SERVER['REQUEST_TIME'] - $timer;
        $day = floor($diff / 86400);
        $free = $diff % 86400;
        if($day > 0) {
            return $day."天前";
        }else{
            if($free>0){
                $hour = floor($free / 3600);
                $free = $free % 3600;
                if($hour>0){
                    return $hour."小时前";
                }else{
                    if($free>0){
                        $min = floor($free / 60);
                        $free = $free % 60;
                        if($min>0){
                            return $min."分钟前";
                        }else{
                            if($free>0){
                                return $free."秒前";
                            }else{
                                return '刚刚';
                            }
                        }
                    }else{
                        return '刚刚';
                    }
                }
            }else{
                return '刚刚';
            }
        }
    }
    /**
     * 上传图片,上传音频
     */
    public function uploadimg()
    {
        // 获取表单上传文件
        $file = request()->file('file');
        if ($file) {
            $info = $file->move(ROOT_PATH.'/public/uploads/xiaochengxu/',true);
            if ($info) {
                $file = $info->getSaveName();
                $file = config('uploadPath')."xiaochengxu\\".$file;
                $file = str_replace('\\','/',$file);
                $file = str_replace("/\r/\n",'',$file);
                $res = ['errCode'=>0,'errMsg'=>'图片上传成功','file'=>$file];
                return $file;
            }
        }
    }
    /**
     * 检测服务号openid
     *
     */
    public function CheckOpenid($uid,$bid){
        $result=db('CustUser')->where('uid',$uid)->value('open_id');
        if(!empty($result))exit(json_encode(['msg'=>$result,'code'=>1]));
        $service=db('WxService')->where('bid',$bid)->find();
        if(empty($service['appid'])||empty($service['secret'])){
            exit(json_encode(['msg'=>'没有设置','url'=>'','code'=>0]));
        }
        $redirect_uri='https://'.$_SERVER['HTTP_HOST'].'/addons/sd_liferuning/tp/api.php/order/MsgView';
        $data=[
            'appid'=>$service['appid'],
            'redirect_uri'=>$redirect_uri,
            'secret'=>$service['secret'],
            'response_type'=>'code',
            'scope'=>'snsapi_userinfo',
            'state'=>$uid
        ];
        $url='https://open.weixin.qq.com/connect/oauth2/authorize?'.http_build_query($data).'#wechat_redirect';
        exit(json_encode(['msg'=>'null','url'=>$url,'code'=>2]));
    }

    public function MsgView($code,$state){
        $bid=db('User')->where('uid',$state)->value('bid');
        $weixin=db('WxService')->where('bid',$bid)->find();
        $url='https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$weixin['appid'].'&secret='.$weixin['secret'].'&code='.$code.'&grant_type=authorization_code';
        $rs=file_get_contents($url);
        $result=json_decode($rs);
        if(!empty($result->errcode)){
            echo '错误提示:'.$result->errmsg;
        }else{
            $rss=db('CustUser')->where('uid',$state)->update(['open_id'=>$result->openid]);
            if($rss){
                return view();
            }else{
                echo "获取openid失败";
            }
        }

    }
    public function goorder(){
        return view();
    }
    public function playMsg($uid,$bid){
        $addess['longitude']=input('longitude');
        $addess['latitude']=input('latitude');
        db('CustUser')->where('uid',$uid)->update($addess);
        $result=OrderModel::playMsg($uid,$bid);
        if($result) exit(json_encode(['code'=>1,'msg'=>'有新的订单']));

    }
//  用户确认订单
    public function confirmOrder(Request $request){
        $order_no = $request->post('order_no');
        $res = OrderModel::instance()->confirmOrder($order_no);
        if ($res){
            echo json_encode(['code'=>1,'mess'=>'收货成功']);
            $this->divideMoney();
        }else{
            echo json_encode(['code'=>0,'mess'=>'收货失败']);
        }
    }
    //  用户付款商品
    public function payWorth(Request $request){
        $order_no   = $request->post('order_no');
        $orderid    = $request->post('orderid');
        $uid        = $request->post('uid');
        $worth      = $request->post('worth');
        $formId      = $request->post('formId');

        $money = Db::name('user')->where(['uid' => $uid]) -> value('money');
        if ($money < $worth) {
            echo json_encode(['code'=>0,'msg'=>'您的餘額不足，請充值']);exit;
        }
        $worth = $money-$worth;
        $res   = OrderModel::instance()->payWorth($order_no,$uid,$worth,$orderid,$formId);

        if ($res['code'] == 1){
            echo json_encode(['code'=>1,'msg'=>$res['msg']]);
        }else{
            echo json_encode(['code'=>0,'msg'=>$res['msg']]);
        }
    }
    /*
     * 用户确认订单后，分配佣金
     * */
    public function divideMoney(){
        return 1;
    }
    /***
     * @多图上传
     */
    public function  OrderUploadsImg(){
        $image = CommonModel:: instance()->uploads('user');
        exit(json_encode(['code'=>1,'url'=>uploadpath('user',$image)]));
    }

}
