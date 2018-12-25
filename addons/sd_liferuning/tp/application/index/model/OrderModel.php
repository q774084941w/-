<?php
namespace app\index\model;

use think\Controller;
use think\Db;
use Think\Model\AdvModel;

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
            $goodssales = Db::name('goodsAttr')->where($map)->setInc('sales',$goods['num']);
        }else{
            $goodsstock = Db::name('goods')->where('goodsid',$goods['goodsid'])->setDec('stock',$goods['num']);
            $goodssales = Db::name('goods')->where('goodsid',$goods['goodsid'])->setInc('sales',$goods['num']);
        }
        if(!$orderid || !$orderGoods || !$goodsstock || !$goodssales){
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
            foreach ($v as $val){

                $total = $num = 0;
                //检测商品信息
                $goods = Db::name('goods')->field('name,bid,status,open_rule,stock,sales,min_price')->where('goodsid',$val['goodsid'])->find();

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
                $total = $total+$val['price']*$val['num']; //计算订单总价格
                $num = $num+$val['num']; //计算订单总数量
            }
            //处理订单信息
            $order = [
                'order_no' => trade_no(),  // 订单号
                'bid' => $goods['bid'],
                'num' => intval($num),
                'money' => $total,
                'remark' => !empty($order_remark)?htmlspecialchars($order_remark):'',
                'uid' => $uid,
                'createtime' => time()
            ];

            //创建订单
            $orderid = Db::name('goodsOrder')->insert($order);

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
                    $goodssales = Db::name('goodsAttr')->where($map)->setInc('sales',$goods['num']);
                }else{

                    $goodsstock = Db::name('goods')->where('goodsid',$va['goodsid'])->setDec('stock',$va['num']);
                    $goodssales = Db::name('goods')->where('goodsid',$va['goodsid'])->setInc('sales',$va['num']);
                }

                //删除购物车

                $map['uid'] = $uid;

                Db::name('cart')->where($map)->delete($cart_data['cartid']);

                if(!$goodsstock || !$goodssales){
                    // 回滚事务
                    Db::rollback();
                    return false;
                }

            }

            //订单商品信息添加
            $orderg = Db::name('orderGoods')->insertAll($orderGoods);
            if(!$orderg){
                Db::rollback();
                return false;
            }

            $ordernos[] = $order['order_no'];


        }

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
     * 取消订单
     */
    public function delOrder($orderid, $uid){
        if(empty($orderid) || empty($uid)) return false;
        Db::startTrans();
        //取消订单
        $gdResult = Db::name('goodsOrder')->where(['orderid'=>$orderid,'uid'=>$uid])->update(['status'=>-1]);
        $orderGoods = Db::name('orderGoods')->field('goodsid,rule,rule1,rule2,num')->where(['orderid' => $orderid])->select();
        foreach ($orderGoods as $val){
            $mapSt['goodsid'] = $val['goodsid'];
            if(isset($val['rule']) || isset($val['rule1']) || isset($val['rule2'])){
                isset($val['rule']) ? $mapSt['rule'] = $val['rule'] : $mapSt['rule'] = '';
                isset($val['rule1']) ? $mapSt['rule1'] = $val['rule1'] : $mapSt['rule1'] = '';
                isset($val['rule2']) ? $mapSt['rule2'] = $val['rule2'] : $mapSt['rule2'] = '';
                $stock = Db::name('goodsAttr')->where($mapSt)->setDec('sales',$val['num']);
                $stock1 = Db::name('goodsAttr')->where($mapSt)->setInc('stock',$val['num']);
            }else{
                $stock = Db::name('goods')->where($mapSt)->setDec('sales',$val['num']);
                $stock1 = Db::name('goods')->where($mapSt)->setInc('stock',$val['num']);
            }
            if (!$stock || !$stock1) {
                break;
            }
        }
        if (!$gdResult || !$stock || !$stock1) {
            Db::rollback(); //回滚事务
            return false;
        }
        Db::commit(); //提交事务
        return true;
    }

    /**
     * 我的订单
     */
    public function getOrderLists($type,$uid){
        $field = 'orderid,uid,order_no,bid,num,money,status,createtime,paytime,remark';
        if($type == 0){
            $order = Db::name('goodsOrder')->field($field)->where(['uid'=>$uid])->order('orderid desc')->select();
        }else{
            $order = Db::name('goodsOrder')->field($field)->where(['uid'=>$uid,'status'=>$type])->order('orderid desc')->select();
        }
        foreach ($order as $key=>&$val){
            $buss = Db::name('business')->field('bid,name,address,logo')->where('bid',$val['bid'])->find();
            $val['busname'] = $buss['name'];
            $val['logo'] = $buss['logo'];
            $val['busaddress'] = $buss['address'];
            $orderGoods = Db::name('orderGoods')->field('orderid,ogid,goodsid,rule,rule1,rule2')->where('orderid',$val['orderid'])->find();
            $val['rule'] = $orderGoods['rule'];
            $val['rule1'] = $orderGoods['rule1'];
            $val['rule2'] = $orderGoods['rule2'];
            $goods = Db::name('goods')->field('goodsid,pic,name,unit,explain,gain_integral,discount')->where('goodsid',$orderGoods['goodsid'])->find();
            $val['pic'] = uploadpath('goods',$goods['pic']);;
            $val['name'] = $goods['name'];
            $val['unit'] = $goods['unit'];
            $val['explain'] = $goods['explain'];
        }
        return $order;

    }


}
