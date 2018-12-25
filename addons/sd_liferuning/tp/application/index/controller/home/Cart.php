<?php
namespace app\index\controller\home;

use app\index\model\CartModel;
use app\index\model\OrderModel;
use app\index\model\GoodsAttrModel;
use phpDocumentor\Reflection\Types\Null_;
use think\console\Output;
use think\Controller;
use think\Request;

class Cart extends Controller
{
   /**
    * 购物车列表
    *
    */
    public function cartLists(){
        $result = CartModel::instance()->getCartList($this->uid);
        $this->jsonOut($result);
        
    }
    /**
     * 添加购物车
     *
     */
    public function cartadd(Request $request){
        $data = $request->post();
        $goodsid = $data['goodsid'];
        $number = $data['num'];
        if(empty($goodsid)) $this->outPut('', 1001, ":缺少参数 goodsid");
        $result = CartModel::instance()->addToCart($goodsid,$this->uid,$number,$data);
        $this->jsonOut($result);
    }
    /**
     * 删除购物车
     */
    public function delete(Request $request){
        $cartid = $request->get('cartid');
        if($cartid){
            $result = CartModel::instance()->del($cartid) ? 1 : 0;
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }
    }
}
