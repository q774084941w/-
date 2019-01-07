<?php
namespace app\api\model;

use app\conmon\wxpay;
use think\Db;
use think\Cache;
use app\home\model\MessageModel;
class OrderModel  
{
    /**
     * 单例模式
     * @return OrderModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new OrderModel();
        }
        return $m;
    }
   /**
    * 检查商品
    */
    public function detail($goodsid,$field){
        
        $list = Db::name('goods')->field($field)->where(['goodsid'=>$goodsid,'integral'=>0])->find();
        return $list;
    }
    /**
     * 余额支付
     */
    public static function pricePay($data,$price){
          $data['status']=1;
        $data['time']=time();
        $data['payway']='pricePay';
        Db::startTrans();
        $rs=db('Runorder')->where(['order_no'=>$data['order_no'],'uid'=>$data['uid']])->update($data);
        $result=db('User')->where('uid',$data['uid'])->update(['money'=>$price]);
        if($rs&&$result){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }

    }
    /**
     * 余额支付
     */
    public static function pricePay2($data){
        //余额减少
        $result=db('User')->where('uid',$data['uid'])->update(['money'=>$data['price']]);

        if($result){

            $data1 = [
                'status' => 4, //修改订单状态    状态（1：待付款 2：待发货 3：待收货  4：已完成  -1：关闭）
                'payway' => 'pricePay'// alipay：支付宝  weixin:微信  pricePay//餘額支付
            ];
            return self::changeOrder($data['order_no'], $data1);

        }else{

            return false;
        }

    }
    /**
     * 立即购买订单处理
     */
    
  public function goodsOrder($order,$goods,$uid){

        $open_rule = $goods['open_rule'];
        unset($goods['open_rule']);
        // 启动事务
        Db::startTrans();
        $time = time();
        //添加订单
        $order['uid'] = $uid;
        $order['createtime'] = $time;

        $orderid =  Db::name('goodsOrder')->insertGetId($order);

        //添加订单商品信息
        $goods['createtime'] = $time;
        $goods['uid'] = $uid;
        $goods['orderid'] = $orderid;
        $orderGoods = Db::name('orderGoods')->insert($goods);
        //库存处理

        if($open_rule == 1){
            $map['rule'] = $goods['rule'];
            if(!empty($goods['rule1'])) $map['rule1'] = $goods['rule1'];
            if(!empty($goods['rule2'])) $map['rule2'] = $goods['rule2'];
            $goodsstock = Db::name('goodsAttr')->where($map)->setDec('stock',$goods['num']);
            //$goodssales = Db::name('goodsAttr')->where($map)->setInc('sales',$goods['num']);
        }else{
            $goodsstock = Db::name('goods')->where('goodsid',$goods['goodsid'])->setDec('stock',$goods['num']);
            //$goodssales = Db::name('goods')->where('goodsid',$goods['goodsid'])->setInc('sales',$goods['num']);
        }
        if(!$orderid || !$orderGoods || !$goodsstock){
            // 回滚事务
            Db::rollback();
            return false;
        }
        // 提交事务
        Db::commit();
        return ['order_no'=>$order['order_no']];

    }
    /**
     * 购物车购买订单处理
     */
    public function cartsOrder($cart_data,$order_remark,$uid = 0){

        $cart_data = array_filter($cart_data);
        if(empty($cart_data) || !is_array($cart_data) || $uid<=0) return [false,1001,''];

        $cart_list = Db::name('cart')->field('cartid,bid,goodsid,price,rule,rule1,rule2,num')->select($cart_data['cartid']);

        if(empty($cart_list)) return [false,2002,''];

        foreach($cart_list as $value){
            $buss[$value['bid']][] =  $value;
        }
        Db::startTrans();
        foreach ($buss as $k=>$v){
            $total = $num = 0;
            foreach ($v as $val){


                //检测商品信息
                $goods = Db::name('goods')->field('name,bid,status,open_rule,stock,sales,min_price,template,weight,freight_count,freight_unify')->where('goodsid',$val['goodsid'])->find();

                if(empty($goods)) return [false,2006,''];  //未查找到商品信息
                if($goods['status'] == 0) return [false,2007,$goods['name']]; //商品已经下架
                //检测商品 库存

                if($goods['open_rule']==0){
                    $stock = [
                        'stock' => $goods['stock'],
                        'sales' => $goods['sales'],
                        'attrid' => 0,
                    ];
                }else{
                    $stock = Db::name('goodsAttr')->field('stock,sales,price,attrid')
                        ->where(['goodsid'=>$val['goodsid'],'rule'=>$val['rule'],'rule1'=>$val['rule1'],'rule2'=>$val['rule2']])->find();
                }
               

                if(empty($stock) || $stock['stock']<=$val['num']) return [false,2001,'商品：'.$goods['name'].';规则:'.$val['rule'].' '.$val['rule1'] . ' ' . $val['rule2']]; //库存不足 或不存在此商品规格
                //订单商品信息
                $orderGoods[] = [
                    'goodsid' => intval($val['goodsid']),
                    'num' => intval($val['num']),
                    'rule' => htmlspecialchars($val['rule']),
                    'rule1' => htmlspecialchars($val['rule1']),
                    'rule2' => htmlspecialchars($val['rule2']),
                    'price' => htmlspecialchars($val['price']),
                    'total_price' => $val['price']*intval($val['num']),
                    'uid' => $uid,
                    'attrid' => $stock['attrid'],
                    'createtime' => time()
                ];

                $total += $val['price']*$val['num']; //计算订单总价格

                $num += $val['num']; //计算订单总数量

            }
            //邮费
          

            //查询地址
            $address = AddressModel::instance()->defaultaddr($uid,$goods['bid']);
            //处理订单信息
	if(!$address){
                return [false,3002,''];
            }
            $order = [
                'order_no' => trade_no(),  // 订单号
                'bid' => $goods['bid'],
                'name' => $address['name'],
                'phone' => $address['phone'],
                'address' => $address['province'] . '-' . $address['city'] . '-' . $address['area'] .'-'. $address['address'],
                'num' => intval($num),
                'money' => $total,
                'remark' => !empty($order_remark)?htmlspecialchars($order_remark):'',
                'uid' => $uid,
                'createtime' => time()
            ];

            //创建订单
            $orderid = Db::name('goodsOrder')->insertGetId($order);

            if(!$orderid) {
                Db::rollback(); // 回滚事务
                return [false,2002,''];
            }


            //库存处理
            foreach($orderGoods as &$va){
                $open_rule = Db::name('goods')->field('open_rule')->where('goodsid',$va['goodsid'])->find();

                if($open_rule['open_rule'] == 1){
                    $map['rule'] = $goods['rule'];
                    if(!empty($goods['rule1'])) $map['rule1'] = $goods['rule1'];
                    if(!empty($goods['rule2'])) $map['rule2'] = $goods['rule2'];
                    $goodsstock = Db::name('goodsAttr')->where($map)->setDec('stock',$goods['num']);
                    //$goodssales = Db::name('goodsAttr')->where($map)->setInc('sales',$goods['num']);
                }else{

                    $goodsstock = Db::name('goods')->where('goodsid',$va['goodsid'])->setDec('stock',$va['num']);
                    //$goodssales = Db::name('goods')->where('goodsid',$va['goodsid'])->setInc('sales',$va['num']);
                }

                //删除购物车

                $map['uid'] = $uid;
                Db::name('cart')->where($map)->delete($cart_data['cartid']);

                if(!$goodsstock){
                    // 回滚事务
                    Db::rollback();
                    return false;
                }

            }

            foreach ($orderGoods as &$val){
                $val['orderid'] = $orderid;
            }

            //订单商品信息添加
            $orderg = Db::name('orderGoods')->insertAll($orderGoods);
            if(!$orderg){
                Db::rollback();
                return false;
            }

            $ordernos[] = $order['order_no'];


        }
        //print_r($orderGoods);exit;

        //判断订单个数  大于1生成临时订单号
        if(count($ordernos)>1){
            $ordernoData = [
                'out_trade_no' => 'o'.trade_no(),  // 订单编号
                'order_no' => implode(',',$ordernos),  // 订单号
                'uid' => $uid
            ];

            $ordernoRes = Db::name('orderNo')->insert($ordernoData);
            if(!$ordernoRes){
                Db::rollback(); // 回滚事务
                return [false,2002,''];
            }
            $orderResult = ['order_no'=>$ordernoData['out_trade_no']];
        }else{
            $orderResult = ['order_no'=>$order['order_no']];
        }
        //提交事务
        Db::commit();
        return [true,'',$orderResult];

    }
/**
     * 获取订单支付参数
     */
    public function getOrderPayParams($order_no,$title,$uid){
        global $_W;
        $weixin_url =   'https://'.$_SERVER['HTTP_HOST'].'/addons/sd_liferuning/tp/api.php/Paynotify/wxgoodsnotify';
        $field = 'id,order_no,status,price,time';
        $openid = Db::name('user')->field('uid,openid')->where('uid',$uid)->find();
        $money = 0;  //支付总金额
        $where = [
            'order_no' => $order_no,
            'uid' => $uid,
        ];
        $order = Db::name('runorder')->field($field)->where($where)->find();
        if(!empty($order)) {
            $money = $order['price'];
        }else{
            $money = Db::name('price_order')->where($where)->value('money');
        }

        $payment_money = $money * 100;

        //获取支付参数
        $wx_pay = new wxpay();
        $res_weixin = $wx_pay->getAppData($order_no, $payment_money, $title, $weixin_url,$openid['openid'],$setAttach='');
      // var_dump($res_weixin);die;

        $params['weixin'] = $res_weixin ? $res_weixin : (object)null;
        $prepay_id=str_replace('prepay_id=','',$res_weixin['package']);
      
        $params['weixin']['order_no']=$order_no;
        Db::name('runorder')->where($where)->update(['prepay_id'=>$prepay_id]);

        return $params;
    }
/**
     * 取消订单
     */
    public function delOrder($orderid, $uid){
        if(empty($orderid) || empty($uid)) return false;
        Db::startTrans();
        //取消订单
        $gdResult = Db::name('goodsOrder')->where(['orderid'=>$orderid,'uid'=>$uid])->update(['status'=>-1,'take_time'=>time()]);
        $orderGoods = Db::name('orderGoods')->field('goodsid,rule,rule1,rule2,num')->where(['orderid' => $orderid])->select();
        foreach ($orderGoods as $val){
            $mapSt['goodsid'] = $val['goodsid'];
            if($val['rule'] || $val['rule1'] || $val['rule2']){
                isset($val['rule']) ? $mapSt['rule'] = $val['rule'] : $mapSt['rule'] = '';
                isset($val['rule1']) ? $mapSt['rule1'] = $val['rule1'] : $mapSt['rule1'] = '';
                isset($val['rule2']) ? $mapSt['rule2'] = $val['rule2'] : $mapSt['rule2'] = '';
                //$stock = Db::name('goodsAttr')->where($mapSt)->setDec('sales',$val['num']);
                $stock1 = Db::name('goodsAttr')->where($mapSt)->setInc('stock',$val['num']);
            }else{
                //$stock = Db::name('goods')->where($mapSt)->setDec('sales',$val['num']);
                $stock1 = Db::name('goods')->where($mapSt)->setInc('stock',$val['num']);
            }
            if (!$stock1) {
                break;
            }
        }
        if (!$gdResult || !$stock1) {
            Db::rollback(); //回滚事务
            return false;
        }

        Db::commit(); //提交事务
        return true;
    }
   public function getAllOrder($uid) {
        $list = Db::name('runorder')
            ->alias('a')
            ->field('a.status')
            ->where(['a.uid'=>$uid,])
            ->select();
       $count = array('count'=>0,'waiting'=>0,'received'=>0,'completed'=>0,'cancelled'=>0);
        foreach ($list as $val) {
            switch ($val['status']){
                case -1:

                    $count['cancelled']++;
                    break;
                case -2:

                    break;
                case 1:
                    $count['waiting']++;
                    break;
                case 2:
                    $count['received']++;

                    break;
                case 3:
                    $count['completed']++;
                    break;
                case 4:
                    $count['cancelled']++;
                    break;

            }
            $count['count']++;
        }

        return $count;
    }
    /**
     * 我的订单
     */
    public function getOrderLists($type,$uid,$limit){
        //          判断是否自动收货
        $list = Db::name('runorder')
            ->alias('a')
            ->join('User u','u.uid=a.uid')
            ->field('a.status,a.id,a.oktime')
            ->where(['a.uid'=>$uid,])
            ->where('a.status',2)
            ->limit($limit)
            ->select();
        foreach ($list as $key => $vo){
            if($vo['oktime']) {
                $delaytime = strtotime("+3 day", $vo['oktime']);
                $checktime = $delaytime - time();
                if ($checktime < 0) {
                    Db::name('runorder')->where('id', $vo['id'])->update(['status'=> 3]);
                }
            }
        }
//        $uid = 1902;
//        var_dump($uid);
        $field = 'a.id,u.uid,a.order_no,a.price,a.status,a.time,a.givetime,a.oktime,a.goodsname,a.mudadds,a.myadds,a.type,u.phone,a.select_name';
        if($type == 0){

            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where('a.status', 'neq',0)
                ->where('a.status', 'neq',-1)
                ->limit($limit)
                ->order('id desc')
                ->select();
        }
        if($type == 1){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where('a.status',1)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        if($type == 2){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where('a.status',2)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        if($type == 3){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where('a.status',3)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        if($type == 4){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where('a.status',-1)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
      foreach($order as $k=>$v){
            $v['givetime'] = date('Y-m-d H:i:s',$v['givetime']);
        }
        return $order;

    }
    /**
     * 我的订单
     */
    public function getOrderListss($type,$uid,$limit,$types){
        //          判断是否自动收货
        $list = Db::name('runorder')
            ->alias('a')
            ->join('User u','u.uid=a.uid')
            ->field('a.status,a.id,a.oktime')
            ->where(['a.uid'=>$uid,])
            ->where('a.status',2)
            ->limit($limit)
            ->select();
        foreach ($list as $key => $vo){
            if($vo['oktime']) {
                $delaytime = strtotime("+3 day", $vo['oktime']);
                $checktime = $delaytime - time();
                if ($checktime < 0) {
                    Db::name('runorder')->where('id', $vo['id'])->update(['status'=> 3]);
                }
            }
        }
        $field = 'a.id,u.uid,a.order_no,a.price,a.status,a.time,a.goodsname,a.mudadds,a.myadds,a.type,u.phone';
        switch ($types)
        {
            case 1:
                $types = ['a.type'=> array('not in','家政,代驾')];
                break;
            case 2:
                $types = ['a.type'=> '家政'];
                break;
            case 3:
                $types = ['a.type'=> '代驾'];
                break;
        }
        if($type == 0){

            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where($types)
                ->where('a.status', 'neq',0)
                ->where('a.status', 'neq',-1)
                ->limit($limit)
                ->order('id desc')
                ->select();

        }
        if($type == 1){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where($types)
                ->where('a.status',1)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        if($type == 2){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where($types)
                ->where('a.status',2)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        if($type == 3){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where($types)
                ->where('a.status',3)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        if($type == 4){
            $order = Db::name('runorder')
                ->alias('a')
                ->join('User u','u.uid=a.uid')
                ->field($field)
                ->where(['a.uid'=>$uid,])
                ->where($types)
                ->where('a.status',-1)
                ->order('id desc')
                ->limit($limit)
                ->select();
        }
        return $order;

    }
/**
     * 订单详情
     */
    public function getOneOrder($order){

        $oderinfo = Db::name('runorder')
            ->alias('r')
            ->join('CustUser c','c.uid=r.rid')
            ->field('r.id,r.uid,r.rid,c.cid,r.order_no,r.imgurl,r.phone,r.price,r.audiotime,r.xphoto,r.yinpin,r.type,r.oktime,r.tip,r.redbao,r.status,r.goodsname,r.mudadds,r.myadds,r.times,r.time,r.payway,r.message,r.distype,r.outtime,c.tel,c.uname,r.num_star,r.why_text,r.worth,r.worth_type')
            ->where('id',$order)
            ->find();
        if(empty($oderinfo)){
            $oderinfo = Db::name('runorder')
                ->field('id,uid,order_no,price,audiotime,imgurl,tip,type,redbao,oktime,status,xphoto,yinpin,goodsname,mudadds,myadds,times,time,payway,message,distype,outtime,phone,num_star,why_text,worth,worth_type')
                ->where('id',$order)
                ->find();
        }
        if ($oderinfo['oktime']){
            $oderinfo['succ'] = 1;
        }else{
            $oderinfo['succ'] = 0;
        }

//       三天自动确认
//        $delaytime = strtotime("+3 day",$oderinfo['oktime']);
//        $checktime = $delaytime-time();
//        if ($oderinfo['status'] == 2 && $checktime < 0){
//            Db::name('runorder')->where('id',$order)->update(['status'=>3]);
//            $oderinfo['status'] = 3;
//        }
        $oderinfo['time'] = date('Y-m-d H:i:s',$oderinfo['time']);
        $oderinfo['imgurl'] = explode(',',$oderinfo['imgurl']);
        $oderinfo['delaytime'] = date("Y-m-d H:i:s",strtotime("+3 day",$oderinfo['oktime']));
        $oderinfo['outtime'] = date('Y-m-d H:i:s',$oderinfo['outtime']);
        $oderinfo['oktime'] = date('Y-m-d H:i:s',$oderinfo['oktime']);
        return $oderinfo;
    }

    /**
     * 评论提交
     */
    public function comment($order_id,$num_star,$why_text) {
        $where = array(
            'id' => $order_id
        );
        $ret  = Db::name('runorder') -> field('num_star') -> where($where) -> find();
        if ($ret['num_star'])
        {
            return array(
                'errorCode' => 4003
            );
        }
        $array = array(
            'num_star'      => $num_star,
            'why_text'      => $why_text,
            'comment_time'  => time()
        );
        $result = Db::name('runorder')
            -> where($where)
            -> update($array);
        return $result;
    }

    /**
     * 确认收货
     */
    public function takeGoods($orderid,$uid){
        $status = Db::name('goodsOrder')->field('status,orderid,uid')->where(['uid'=>$uid,'orderid'=>$orderid])->find();
        if($status['status'] == 3){
            $result = Db::name('goodsOrder')->where(['uid'=>$uid,'orderid'=>$orderid])->update(['status'=>4]);
        }else{
            return [false,2008];
        }
        return [true,$result];
    }
    /**
     * 微信支付回调处理
     */
   public function wxNotify(){
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];

        $weixin = new wxpay();
        $result = $weixin->verfy($data);

        $input = new \WxPayOrderQuery();
        $input->SetTransaction_id($result['transaction_id']);
       $input->SetAppid($result['appid']);//公众账号ID
       $input->SetMch_id($result['mch_id']);//商户号
        $result = \WxPayApi::orderQuery($input);
        
        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            $data = [
                'status' => 4, //修改订单状态    状态（1：待付款 2：待发货 3：待收货  4：已完成  -1：关闭）
                'payway' => 'pricePay'// alipay：支付宝  weixin:微信  pricePay//餘額支付
            ];

            $this->changeOrder($result['out_trade_no'], $data);

        }
        return false;
    }
    /**
     * 退款
     */
    public function out_money($order_no){
        $field='a.price,a.order_no,b.appid,b.mchid,a.bid,a.payway,a.uid';
        $result=db('Runorder')
            ->alias('a')
            ->join('Business b','a.bid=b.bid')
            ->field($field)
            ->where(['a.order_no'=>$order_no,'a.status'=>array('in','1,-2')])
            ->find();
        if(!$result){
            return false;
        }

        if (empty($result['price'])) {
            db('Runorder')->where('order_no',$order_no)->update(['status'=>-1,'outtime'=>time()]);
            return true;
        }
      
        //if($result['payway']=='pricePay'){
            Db::startTrans();
            $rs=db('Runorder')->where('order_no',$order_no)->update(['status'=>-1,'outtime'=>time()]);
            $rss=db('User')->where('uid',$result['uid'])->setInc('money',$result['price']);

            if($rs&&$rss){
                Db::commit();
                MessageModel::OutMsg($order_no);
                exit(json_encode(['code'=>1,'msg'=>'退款成功']));
            }else{
                Db::rollback();
                return false;
            }

       // }
        $result['trade_no']=trade_no();
        $rs=new wxpay();
        $msg=$rs->out_price($result);
        MessageModel::OutMsg($order_no);
        if($msg['return_code']=='SUCCESS'){
            if($msg['result_code']=='SUCCESS'){
                $rs=db('Runorder')->where('order_no',$order_no)->update(['status'=>-1,'outtime'=>time()]);
                if($rs){
                    return true;
                }
            }else{
                if($msg['err_code_des']=='订单已全额退款'){
                    $rs=db('Runorder')->where('order_no',$order_no)->update(['status'=>-1,'outtime'=>time()]);
                    if($rs){
                        return true;
                    }
                }
            }
        }else{
            return false;
        }


    }

    /**
     * 改变订单状态 销量更新
     */
    public static function changeOrder($orderid,$data){
        $order = Db::name('runorder')->field('id,order_no,status')->where('order_no',$orderid)->find();
        if(empty($order)){
            $order = Db::name('price_order')->where('order_no',$orderid)->find();
            if(empty($order)) return false;
            if($order['status'] == 0){
                Db::startTrans();
                //更新订单
                $result = Db::name('price_order')->where('order_no',$orderid)->update($data);

                if (!$result ) {
                    Db::rollback(); //回滚事务
                    return false;
                }else{
                    db('CustUser')->where('uid',$order['uid'])->update(['cashstatus'=>1,'promisemoney'=>$order['money']]);
                }
                //提交事务
                Db::commit();


                MessageModel::PayMsg($result['out_trade_no']);
                return $result;
            }else{
                return true;
            }

        }
        if($order['status'] == 0){
            Db::startTrans();
            //更新订单
            $result = Db::name('runorder')->where('order_no',$orderid)->update($data);
            if (!$result ) {
                Db::rollback(); //回滚事务
                return false;
            }
            //提交事务
            Db::commit();
            self::SendMsg($orderid);
            return $result;
        }else{
            return true;
        }
    }


/**
     * 充值
     */
    public function rechargePay($money,$uid,$order_no){
        $weixin_url = "https://".$_SERVER['HTTP_HOST']."/addons/sd_liferuning/tp/api.php/Paynotify/wxtopUpnotify";   //微信回调地址

        $openid = Db::name('user')->field('uid,openid,bid')->where('uid',$uid)->find();
        $payment_money = $money * 100;
        //获取支付参数
        $wx_pay = new wxpay();
        
        $res_weixin = $wx_pay->getAppData($order_no, $payment_money, '充值', $weixin_url,$openid['openid'],$setAttach='');
        $params['weixin'] = $res_weixin ? $res_weixin : (object)null;
        return $params;
    }
/**
     * 充值回调
     */
    public function topUpNotify(){
        $data = $GLOBALS['HTTP_RAW_POST_DATA'];
        $weixin = new wxpay();
        $result = $weixin->verfy($data);
        $input = new \WxPayOrderQuery();
        $input->SetAppid($result['appid']);//公众账号ID
        $input->SetMch_id($result['mch_id']);//商户号
        $input->SetTransaction_id($result['transaction_id']);

        $result = \WxPayApi::orderQuery($input);


        if(array_key_exists("return_code", $result)
            && array_key_exists("result_code", $result)
            && $result["return_code"] == "SUCCESS"
            && $result["result_code"] == "SUCCESS")
        {
            $goodsorde = Db::name('user')->field('uid,bid,openid')->where(['openid'=>$result['openid']])->find();
            $data = [
                'uid' => $goodsorde['uid'],
                'bid' => $goodsorde['bid'],
                'order_no' => $result['out_trade_no'],
                'money' => $result['total_fee'] / 100,
                'paytime' => time(),
                'pay_user' => $result['openid'],
                'pay_serial_number' => $result['transaction_id'],
                'pay_way' => 'weixin'// alipay：支付宝  weixin:微信
            ];
                $results = Db::name('topUp')->insert($data);
                if($results){
                    Db::startTrans();
                    $usermoney = Db::name('user')->where('uid',$data['uid'])->setInc('money',$data['money']);
                    if($usermoney){
                        Db::commit();
                        MessageModel::rechargeMsg($data['order_no']);
                        echo 'SUCCESS';
                    }else{
                        Db::rollback();
                    }
                }

               


            return false;
        }
        return false;
    }
    /**
     * 余额/积分 支付
     */
    public function getorderPayIntegral($order_no,$type,$uid){
        $order = Db::name('goodsOrder')->field('orderid,order_no,status,bid,uid,money')->where('order_no',$order_no)->find();
        $getuser = Db::name('user')->field('money,integral,uid,bid')->where('uid',$order['uid'])->find();
        if($type == 1){
            $payway = 'balance';  //余额购买
            if($getuser['money'] < $order['money']){
                return [false,2010];
            }
        }elseif ($type == 2){
            $payway = 'integral';  //积分购买
            if($getuser['integral'] < $order['money']){
                return [false,2011];
            }
        }else{
            $payway = '';
        }
        $data = [
            'paytime' => time(),
            'pay_way' => $payway,
            'status'  => 2
        ];
        if(empty($order))  return [false,0];
        if($order['status'] == 1){
            Db::startTrans();
            //更新订单
            $result = Db::name('goodsOrder')->where('order_no',$order_no)->update($data);
            //销量
            $orderGoods=Db::name('orderGoods')->where(['orderid'=>$order['orderid']])->field('goodsid,num,rule,rule1')->select();
            foreach ($orderGoods as $val){
                $goods = Db::name('goods')->where('goodsid',$val['goodsid'])->setInc('sales', $val['num']);
            }
            if($type == 1){
                $bainpay = Db::name('user')->where('uid', $order['uid'])->setDec('money', $order['money']);
            }elseif ($type == 2){
                $bainpay = Db::name('user')->where('uid', $order['uid'])->setDec('integral', $order['money']);
            }
            if (!$result || !$goods || !$bainpay) {
                Db::rollback(); //回滚事务
                return [false,0];
            }
            //提交事务
            Db::commit();
            return [true,$result];
        }else{
            return [false,0];
        }

    }
/**
     * 充值记录
     */
    public function recharge($uid,$bid){
        $result=Db::name('topUp')->where(['uid'=>$uid,'bid'=>$bid])->field('order_no,topid,uid,bid,money,pay_user,pay_serial_number,paytime,pay_way')->select();
        foreach($result as $key =>&$vo){
        $vo['paytime']=date('Y-m-d',$vo['paytime']);
        }
        return $result;
    }
    /**
     * 服务端订单列表
     */
       public function ServerOrder($bid,$status,$rid,$limit){
        $money=db('Percent')->where('bid',$bid)->value('percent');
        $money=(100-$money)/100;
        $class=db('Class')->select();
        $cuid = db('cust_user')->where('uid',$rid)->value('is_status');
        switch ($cuid){
            case 1:
                $where = ['g.type'=>array('not in','代驾,家政')];
                break;
            case 2:
                $where = ['g.type'=>'家政'];
                break;
            case 3:
                $where = ['g.type'=>'代驾'];
                break;
        }
        if($status == 1){
            $result=Db::name('runorder')
                ->alias('g')
                ->join('135k_user u','g.uid = u.uid')
                ->where(['g.bid'=>$bid, 'g.status' => $status])
                ->where($where)
                ->field('g.status,g.imgurl,g.order_no,g.id,g.time,g.givetime,g.oktime,g.bid,g.audiotime,g.times,g.type,g.price,g.uid,g.xphoto,g.yinpin,g.goodsname,u.nickname,g.myadds,g.mudadds,g.redbao,g.seller_type,g.select_name')
                ->order('g.order_no desc')
                ->limit($limit)
                ->select();
            foreach($result as $key =>&$vo){
               switch ($vo['status']) {
                    case 1:
                        $result[$key]['look_time']="已下单：".$this ->timediff(time(),$vo['time']);;
                        break;
                    case 2:
                        $result[$key]['look_time']="已配送：".$this ->timediff(time(),$vo['givetime']);
                        break;
                    case 3:
                        $result[$key]['look_time']="总花费：".$this ->timediff($vo['oktime'],$vo['time']);
                        break;
                    default:
                }
                $vo['time']=date('Y-m-d',$vo['time']);
                $vo['imgurl'] = explode(',', $vo['imgurl']);
//                var_dump($vo['imgurl']);die;
                foreach ($class as $k=>&$v){
                    if($v['cid']==$vo['type']){
                        $result[$key]['type']=$v['name'];
                    }
                }
                $vo['price']=($vo['price']*$money)<0.01?0.01:number_format($vo['price']*$money,2);
                $vo['price'] = $vo['price'] + $vo['redbao'];
                $time=intval(($vo['times']-time())/60)<1?0:intval(($vo['times']-time())/60);
                if($time>60){
                    $H=intval($time/60);
                    $I=intval(60*($time/60-$H));
                    $vo['times']=$H.'小时'.$I.'分钟';
                }else{
                    $vo['times']=$time.'分钟';
                }
               

            }
        }else{
            //          判断是否自动收货
            $list =Db::name('runorder')
                ->alias('g')
                ->join('135k_user u','g.uid = u.uid')
                ->where(['g.bid'=>$bid, 'g.status' => 2 ,'g.rid' => $rid])
                ->field('g.id,g.oktime')
                ->limit($limit)
                ->select();
            foreach ($list as $key => $vo){
                if($vo['oktime']) {
                    $delaytime = strtotime("+3 day", $vo['oktime']);
                    $checktime = $delaytime - time();
                    if ($checktime < 0) {
                        Db::name('runorder')->where('id', $vo['id'])->update(['status'=> 3]);
                    }
                }
            }


            $result=Db::name('runorder')
                ->alias('g')
                ->join('135k_user u','g.uid = u.uid')
                ->where(['g.bid'=>$bid, 'g.status' => $status ,'g.rid' => $rid])
                ->where($where)
                ->field('g.status,g.imgurl,g.order_no,g.id,g.time,g.givetime,g.oktime,g.audiotime,g.bid,g.oktime,g.times,g.xphoto,g.yinpin,g.type,g.price,g.uid,g.myadds,g.mudadds,g.goodsname,u.nickname,g.redbao,g.seller_type,g.select_name')
                ->order('g.order_no desc')
                ->limit($limit)
                ->select();
            foreach($result as $key =>&$vo){
                switch ($vo['status']) {
                    case 1:
                        $result[$key]['look_time']="已下单：".$this ->timediff(time(),$vo['time']);;
                        break;
                    case 2:
                        $result[$key]['look_time']="已配送：".$this ->timediff(time(),$vo['givetime']);
                        break;
                    case 3:
                        $result[$key]['look_time']="总花费：".$this ->timediff($vo['oktime'],$vo['time']);
                        break;
                    default:
                }
            //判断是否送达
                if ($vo['oktime']){
                    $result[$key]['succ'] = 1;
                }else{
                    $result[$key]['succ'] = 0;
                }


                $vo['oktime']=date('Y-m-d H:i:s',$vo['oktime']);
                $vo['time']=date('Y-m-d',$vo['time']);
                foreach ($class as $k=>&$v){
                    if($v['cid']==$vo['type']){
                        $result[$key]['type']=$v['name'];
                    }
                }
                $vo['price']=($vo['price']*$money)<0.01?0.01:number_format($vo['price']*$money,2);
                $vo['price'] = $vo['price'] + $vo['redbao'];
                $time=intval(($vo['times']-time())/60)<1?0:intval(($vo['times']-time())/60);
                if($time>60){
                    $H=intval($time/60);
                    $I=intval(60*($time/60-$H));
                    $vo['times']=$H.'小时'.$I.'分钟';
                }else{
                    $vo['times']=$time.'分钟';
                }
            }
        }

        return $result;
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
     * 服务端订单详情
     */
 public function ServerOrderInfor($orderid){
        $result=Db::name('runorder')
            ->alias('g')
            ->join('user u','g.uid = u.uid')
            ->where(['g.id'=>$orderid])
            ->field('g.order_no,g.username,g.type,g.imgurl,g.my_username,g.phone,g.message,g.audiotime,g.oktime,g.id,g.time,g.bid,g.times,g.status,g.price,g.uid,g.yinpin,g.xphoto,g.goodsname,u.nickname,g.myadds,g.mudadds,g.redbao,g.num_star,g.why_text,g.worth,g.worth_type,g.seller_type,g.my_phone,g.pretime,g.pre_price,g.rid')
            ->find();
        if ($result['oktime']){
            $result['succ'] = 1;
        }else{
            $result['succ'] = 0;
        }
        $result['time']=date('Y-m-d H:i:s',$result['time']);
        $result['oktime']=date('Y-m-d H:i:s',$result['oktime']);
        $result['imgurl'] = explode(',',$result['imgurl']);
        if($result['type']=='帮我买'||$result['type']=='帮我送'){
            if(!empty($result['times'])){
                if (is_integer($result['times'])){
                    $result['timesss']=date('Y-m-d H:i:s',$result['times']);
                } else {
                    $result['timesss'] = $result['times'];
                }
            }else{
                $result['timesss']='立即送往';
            }
        }

//        $result['xphoto'] = uploadpath('user',$result['xphoto']);
//        $result['yinpin'] = uploadpath('xiaochengxu',$result['yinpin']);
        $time=intval(($result['times']-time())/60)<1?0:intval(($result['times']-time())/60);
        if($time>60){
            $H=intval($time/60);
            $I=intval(60*($time/60-$H));
            $result['timess']=$H.'小时'.$I.'分钟';
        }else{
            $result['timess']=$time.'分钟';
        }

        return $result;
    }
    /**
     * 银行卡信息提交
     */
    public function card_sub(){
        if(request()->isPost()){
            $data=input('post.');
            print_r($data);
        }
    }
    /**
     * 跑腿抢单
     */
    public function GiveOrder($orderid,$rid,$bid){
        $num=Db::name('business')->field('num')->where(['bid'=>$bid])->find();
        $arr=Db::name('runorder')->where(['rid'=>$rid,'status'=>2])->select();
        if(count($arr)>$num['num']){
            $result=-1;
            return $result;
        }else{
            $data=Db::name('runorder')->field('status')->where(['id' => $orderid])->find();
            //var_dump($data);die;
            if($rid != 0  && $data['status'] == 1){
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
                $result = 2;
                return   $result;
            }
        }
    }

 
    /**
     * 跑腿完成订单
     */
     public function OkOrder($orderid,$bid,$uid,$code,$status,$pay_type){
        if($status==1){
            if($code==''){
                exit(json_encode(['code'=>2,'msg'=>'收貨碼為空']));
            }else{
                $oneorder =  Db::name('runorder')
                    -> alias('a')
                    -> join('user b','a.uid=b.uid')
                    -> field('a.*,b.formId2,b.openid,b.money')
                    ->where(['a.id' => $orderid,'a.status'=>2,'a.code'=>$code])
                    ->find();//提成佣金
            }
        }else{
            $oneorder =  Db::name('runorder')
                -> alias('a')
                -> join('user b','a.uid=b.uid')
                -> field('a.*,b.formId2,b.openid,b.money')
                ->where(['a.id' => $orderid,'a.status'=>2])
                ->find();//提成佣金
        }
        if(empty($oneorder))exit(json_encode(['code'=>2,'msg'=>'收貨碼錯誤']));
        $myssl =  Db::name('percent')
            ->where(['bid' => $bid])
            ->find();//提成佣金
        if($myssl != 0){
            $p_price =$oneorder['price']* $myssl['percent']/100;
            $f_price = $oneorder['price'] - $p_price + $oneorder['redbao'];
            $data ['p_price']=$p_price;
            if($oneorder['proxy_id']!=0){//区域代理分润
                $share=db('Proxy')->where(['proxy_id'=>$oneorder['proxy_id'],'proxy_status'=>1])->value('share');
                $proxy_price=number_format($p_price*($share/100),2);
                $data ['proxy_price']=$proxy_price;
                $data ['p_price']=$p_price-$proxy_price;
            }
            $data ['f_price']=$f_price;
        }

        $data['oktime'] = time();
        $msg = $this -> makeMsg($uid,$data ['f_price'],$oneorder['order_no']);

        $data['status'] = 3;
        Db::startTrans();
        $proxy=true;
        $rss=db('PriceMsg')->insert($msg);
        $returnMoney= $oneorder['money'];
  		 $resss = true;
         $ressss = true;
        switch ($pay_type) {
            case  1:// 现金
                $pay_price = $oneorder['pre_price'];
             
     
                break;
            case 2://钱包
                $pay_price = $oneorder['pre_price'];
               if ($oneorder['worth']>$oneorder['pre_price']) {
                    $number = $oneorder['worth']-$oneorder['pre_price'];
                    if ($returnMoney<$number) {
                        exit(json_encode(['code'=>0,'msg'=>'客戶錢包餘額不足，請提醒客戶充值，或選擇現金收款']));
                    }
                    $resss = Db::name('user')
                        -> where('uid',$oneorder['uid'])
                        ->setDec('money',$number);
                   $ressss=MessageModel::index($oneorder['uid'],$oneorder['order_no'].'商品預付補迴',$number,$oneorder['order_no'],'pay');
                    $returnMoney -= $number;
                    $pay_price = $oneorder['pre_price'];
                }
             
                break;
            default:
                if ($oneorder['worth']<$oneorder['pre_price']) {
                    $number = $oneorder['pre_price']-$oneorder['worth'];
                    $resss = $this -> refund($oneorder['uid'],$number);
                    $ressss= MessageModel::index($oneorder['uid'],$oneorder['order_no'].'商品預付回退',$number,$oneorder['order_no']);
                    $returnMoney += $number;
                }
              $pay_price = $oneorder['worth'];
               

    }
        $msg2 = $this -> makeMsg($uid,$pay_price,$oneorder['order_no'],'商品价值');
                $rsss=db('PriceMsg')->insert($msg2);
        $f_price += $pay_price;

        if(!empty($data['proxy_price']))$proxy=db('Proxy')->where(['proxy_id'=>$oneorder['proxy_id'],'proxy_status'=>1])->setInc('proxy_price',$data['proxy_price']);
        $rs=Db::name('runorder')->where(['id' => $orderid])->update($data);
        $cust = Db::name('cust_user')->where(['uid' => $uid])->setInc('money',$f_price);
        if($rs&&$cust&&$proxy&&$rss&&$rsss&&$resss&&$ressss){
            Db::commit();
            $data = array(
                "MOP:".$f_price,
                "MOP:".$pay_price,
                $oneorder['distance']."公里-縂花費MOP:".$oneorder['price'],
                "MOP:".$returnMoney
            );
            \app\api\model\MessageModel::sendMsg($oneorder['openid'],$oneorder['formId2'],$data,'spy0M2xDQzMwSQfcR3hZM-delgzaEMAnEyMqJfK5Zl8');
            return ['code'=>1,'msg'=>'感謝您的付出'];
        }else{
            Db::rollback();
            return ['code'=>0,'msg'=>'扣款失敗'];
        }
    }

    /**
     * 退款
     */
    private function refund ($uid,$money) {
        return Db::name('user')
            -> where('uid',$uid)
            ->setInc('money',$money);
    }

    /**
     * 记录数据组合
     */
    private function makeMsg ($uid,$f_price,$order_no,$msg='订单收入') {
       return $msgArray=[
            'uid'=>$uid,
            'money'=>$f_price,
            'order_no'=>$order_no,
            'paytype'=>'outpay',
            'msg'=>$order_no.$msg,
            'createtime'=>time(),
            'type'=>1,
            'cust'=>1
        ];
    }

    /**
     * 跑腿添加商品价值
     */
    public function OkWorth($orderid,$code){
        $data = array(
            'worth' => $code,
            'worth_type' => 0
        );
       $rs=Db::name('runorder')->where(['id' => $orderid])->update($data);
      if($rs){

            $result = Db::name('runorder')
                -> alias('a')
                -> join('user b','a.uid=b.uid')
                -> field('b.formId,a.order_no,a.time,a.goodsname,b.openid')
                -> where(['a.id' => $orderid])
                -> find();
            if ($result) {
                $data = array(
                    $result['order_no'],
                    date("Y-m-d H:i:s",$result['time']),
                    $result['goodsname'],
                    $code
                );
                \app\api\model\MessageModel::sendMsg($result['openid'],$result['formId'],$data,'Ah2RV2Q7SbasWwxlP9b3zIlXrvpOMojdiR6JpRhVIfE');
            }

            return true;
        }else{
            return false;
        }
    }
    /**
     *预计收益
     */
    public function Income($cid){
        $result1 = Db::name('cust_user')
            ->alias('u')
            ->join('runorder g','g.rid = u.uid')
            ->where(['g.status' => 3,'u.cid' => $cid])
            ->where('g.givetime','>',time()-3600*24)
            ->sum('f_price');
        $result2 = Db::name('cust_user')
            ->alias('u')
            ->join('runorder g','g.rid = u.uid')
            ->where(['g.status' => 3,'u.cid' => $cid])
            ->where('g.givetime','>',time()-3600*24)
            ->count();

        //配送中
        $result3 = Db::name('cust_user')
            ->alias('u')
            ->join('runorder g','g.rid = u.uid')
            ->where(['g.status' => 2,'u.cid' => $cid])
            ->where('g.givetime','>',time()-3600*24)
            ->sum('price');
        $result4 = Db::name('cust_user')
            ->alias('u')
            ->join('runorder g','g.rid = u.uid')
            ->where(['g.status' => 2,'u.cid' => $cid])
            ->where('g.givetime','>',time()-3600*24)
            ->count();
        $money = Db::name('cust_user')
            ->where(['cid' => $cid])
            ->find();
        $res = [
            'res1' => $result1,
            'res2' => $result2,
            'res3' => $result3,
            'res4' => $result4,
            'res5' => date('Y-m-d',time()),
            'money' => $money['money']
        ];

        return $res;
    }
    /**
     * 服务号获取Token
     */
    public static function GetToken($bid){

        $weixin=db('WxService')->where('bid',$bid)->find();
        $data['grant_type']='client_credential';
        $data['appid']=$weixin['appid'];
        $data['secret'] = $weixin['secret'];
        $url='https://api.weixin.qq.com/cgi-bin/token?'.http_build_query ( $data );

        $ch  = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        $obj=json_decode($data);
        if(!empty($obj->access_token)){
            return $obj->access_token;
        }
    }
    /**
     * 接单消息提醒
     */
    public static function SendMsg($order_no){
        $order=db('Runorder')->where('order_no',$order_no)->find();
        $weixin=db('WxService')->where('bid',$order['bid'])->find();
        $accessToken=$weixin['token'];
        if(empty($accessToken)){
            $accessToken=self::GetToken($order['bid']);
        }
        if(($weixin['time']+6000)<time()){
            $accessToken=self::GetToken($order['bid']);
        }
        $url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$accessToken;
        $openid=db('CustUser')->alias('a')->join('User u','u.uid=a.uid')->where(['u.bid'=>$order['bid']])->where(['a.open_id'=>['neq','']])->column('open_id');

        foreach (array_unique($openid) as $key=>$val){
            self::curl_msg($val,$weixin['template_id'],$order,$url);
        }

    }
    public static function curl_msg($openid,$template_id,$order,$url){
        $postData=[
            'touser'=>$openid,
            'template_id'=>$template_id,
            'url'=>'https://'.$_SERVER['HTTP_HOST'].'/addons/sd_liferuning/tp/api.php/order/goorder',
            'topcolor'=>"#FF0000",
            'data'=>[
                'first'=>['value'=>$order['myadds'].'下单提醒'],
                'keyword1'=>['value'=>$order['order_no']],
                'keyword2'=>['value'=>'('.$order['username'].')下单时间:'.date('Y-m-d H:i:s',$order['time'])],
                'keyword3'=>['value'=>$order['goodsname']],
                'remark'=>['value'=>'说明:'.$order['message']]
            ]
        ];
        $postData =  json_encode($postData,JSON_UNESCAPED_UNICODE);
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        curl_exec($ch);
    }
    public static function playMsg($uid,$bid){
        $order=db('Runorder')->where(['bid'=>$bid,'status'=>1])->order('id desc')->limit(1)->column('id');
        if(empty($order)) return false;
        $id=db('CustUser')->where('uid',$uid)->value('play');
        if($id!=$order[0]&&$id<$order[0]){
            $rs=db('CustUser')->where('uid',$uid)->update(['play'=>$order[0]]);
            return $rs;
        }else{
            return false;
        }

    }
//  用户确认收货，订单完成

    public function confirmOrder($order_no){
        if(empty($order_no)) return false;
        $res = Db::name('runorder')->where(['order_no'=>$order_no])->update(['status'=>3]);
        //检测是不为外来订单
        $data = db('runorder')->where('order_no',$order_no)->find();
        if($data['old_order_no'] && $data['order_type'] == 1){//禾匠
            Db::table('hjmall_order')->where('order_no',$data['old_order_no'])->update(['is_confirm'=>1,'confirm_time'=>time()]);
        }
        if($data['old_order_no'] && $data['order_type'] == 2){//智慧外卖
            Db::table('ims_cjdc_order')->where('order_num',$data['old_order_no'])->update(['state'=>4,'complete_time'=>date("Y-m-d H:i:s",time())]);
        }

//        $res =  Db::name('runorder')->getLastSql();
        return $res;
    }

//  用户付款商品

    public function payWorth ($order_no,$uid,$worth,$orderid,$formId) {
        $data = array(
            'worth_type' => 1
        );
        $rid  = Db::name('runorder') ->where(['id' => $orderid]) -> value('rid');
        Db::startTrans();
        $proxy=true;
        MessageModel::index($rid,'商品价值收入',$worth,$order_no,'outpay');
        MessageModel::index($uid,'商品价值支出',$worth,$order_no,'pay');
        $rs=Db::name('runorder')->where(['id' => $orderid])->update($data);
        $custs = Db::name('cust_user')->where(['uid' => $rid])->setInc('money',$worth);
        $data = array(
            'formId' => $formId,
            'money'  => $worth
        );
        $cust = Db::name('user')->where(['uid' => $uid])->update($data);
        if($rs&&$cust&&$custs&&$proxy){

            Db::commit();
            return array('code' => 1,'msg' => '成功');
        }else{
            Db::rollback();
            return array('code' => 0,'msg' => '支付失敗');
        }
    }

    public function OrderCount ($bid,$rid) {
        $where = ['type'=>array('not in','代驾,家政')];
         $result=Db::name('runorder')
            ->where(['bid'=>$bid,'rid' => $rid])
            ->field('status')
            ->where($where)
            ->select();
      	
          $count = Db::name('runorder')
            ->where(['bid'=>$bid,'status' => 1])
            ->field('count(*)')
            ->where($where)
            ->find();
        $count = array('waiting'=>$count["count(*)"],'received'=>0,'completed'=>0);
        foreach ($result as $val) {
            switch ($val['status']) {
               
                case 2:
                    $count['received']++;
                    break;
                case 3:
                    $count['completed']++;
                    break;
                default:
                  
            }
        }
      
        return $count;
    }
  
   public function f_price ($price,$bid=1) {
        $myssl =  Db::name('percent')
            ->where(['bid' => $bid])
            ->find();//提成佣金
        if($myssl != 0){
            $p_price =$price* $myssl['percent']/100;
            $f_price = $price - $p_price ;
            return $f_price;
        }
        return $price;
    }
}
