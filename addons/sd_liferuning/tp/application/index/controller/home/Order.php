<?php
namespace app\index\controller\home;

use app\index\model\CommonModel;
use app\index\model\OrderModel;
use app\index\model\GoodsAttrModel;
use phpDocumentor\Reflection\Types\Null_;
use think\console\Output;
use think\Controller;
use think\Request;

class Order extends Controller
{
    /**
     * 添加订单
     * @return \think\response\View
     *
     */
    public function insertOrder(Request $request)
    {
        $cart_data = $_POST['cart_data'];;
        $goods_data = $request->post('goods_data');
        if(!empty($goods_data)){
            $goods_data =  array_filter($goods_data['goods_data']);
            if(empty($goods_data)) $this->outPut('', 1001, "goods_data");
            $goodsid = intval($goods_data['goodsid']);
            isset($goods_data['rule']) ? $rule = htmlspecialchars($goods_data['rule']) : $rule = '';
            isset($goods_data['rule1']) ? $rule1 = htmlspecialchars($goods_data['rule1']) : $rule1 = '';
            isset($goods_data['rule2']) ? $rule2 = htmlspecialchars($goods_data['rule2']) : $rule2 = '';
            isset($goods_data['remark']) ? $goods_data['remark'] = $goods_data['remark'] :  $goods_data['remark'] = '';
           
            $number = intval($goods_data['number']);
            if(empty($goodsid)) $this->outPut('', 1001, ":缺少参数goodsid" );
            if(empty($number)) $this->outPut('', 1001, ":缺少参数number" );

            //检查商品信息
            $goods = OrderModel::instance()->detail($goodsid,'goodsid,bid,open_rule,sales,name,min_price,stock,template,integral,status,discount,freight_count,weight,freight_unify');

            if(empty($goods)) $this->outPut(null,2001);
            if($goods['status'] == 0) $this->outPut(0,2002);

            //检测商品 库存
            if($goods['open_rule']==0){   // 0 关闭规则表   1 开启
                $stock = [
                    'stock' => $goods['stock'],
                    'sales' => $goods['sales'],
                    'price' => $goods['min_price'],
                    'attrid' => 0
                ];

            }else{

                $stock = GoodsAttrModel::instance()->checkStock(['goodsid'=>$goodsid,'rule'=>$rule,'rule1'=>$rule1,'rule2'=>$rule2]);
            }

            if(empty($stock) || $stock['stock'] < $number) $this->outPut(null,2003);
            //创建订单
            //订单
            $order = [
                'order_no' => trade_no(),
                'bid' => $goods['bid'],
                'num' => $number,
                'money' => $stock['price'],
                'remark' => $goods_data['remark'],
            ];

            //商品
            $commodity = [
                'goodsid' => $goods_data['goodsid'],
                'num' => $number,
                'rule' => $rule,
                'rule1' => $rule1,
                'rule2' => $rule2,
                'price' => $stock['price'],
                'total_price' => $stock['price']*$number,
                'attrid' => $stock['attrid'],
                'open_rule' => $goods['open_rule'],
            ];
           

            //添加订单信息
            $result = OrderModel::instance()->goodsOrder($order,$commodity,$this->uid);
            if($result == false) $this->outPut(0, 2004);
            $info = $result;
        }else{
            isset($cart_data['order_remark']) ? $order_remark = $cart_data['order_remark'] :  $order_remark = '';
            $cart_data = array_filter($cart_data);
            if(empty($cart_data) || !is_array($cart_data)) $this->outPut('', 1001, ":缺少参数cart_data");
            list($result,$code,$info) = OrderModel::instance()->cartsOrder($cart_data,$order_remark,$this->uid);
            if($result == false) $info = !empty($info)? ':'.$info:'';
            if($result == false)  $this->outPut(null,$code,$info); //提示错误信息
        }
        $this->jsonOut($info);

    }
    /**
     * 我的订单 订单列表
     *
     */
    public function getOrderLists(Request $request){
        $status = $request->get('status');
        $result = OrderModel::instance()->getOrderLists($status,$this->uid);
        $this->jsonOut($result);
    }
    /**
     * 取消订单
     */
    public function delOrder(Request $request){
        $orderid = $request->post('orderid');
        $res = OrderModel::instance()->delOrder($orderid,$this->uid);
        if ($res == false) {
            $this->outPut(0, 0);
        }
        $this->jsonOut(['orderid'=>$orderid]);
    }
    
    
}
