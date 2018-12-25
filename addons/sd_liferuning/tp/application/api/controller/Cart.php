<?php
namespace app\api\controller;

use app\api\model\CartModel;
use think\Controller;
use think\Request;
use think\Db;
class Cart extends Controller
{
   /**
    * 购物车列表
    *
    */
    public function cartLists(){
        //$cartid = $request->get('cartid');
        $result = CartModel::instance()->getCartList($this->uid);
	
        $this->jsonOut($result);
    }
    /**
     * 添加购物车
     *
     */
    public function cartadd(Request $request){
        $this->_isPOST();
        $data = $request->post();
        $goodsid = $data['goodsid'];
        $number = $data['num'];
        if(empty($goodsid)) $this->outPut('', 1001, ":缺少参数 goodsid");
        $result = CartModel::instance()->addToCart($goodsid,$this->uid,$number,$data);
        $this->jsonOut($result);
    }
    /**
     * 购物车提交
     */
    public function cartsubmit(Request $request){
        $this->_isPOST();
        $cartid = $request->post();
        if(empty($cartid)) $this->outPut('', 1001, ":缺少参数 cartid");
        $result = CartModel::instance()->cartsubmit($cartid['cartid'],$this->uid);
        $this->jsonOut($result);

    }
    /**
     * 删除购物车
     */
    public function delete(Request $request){
        $this->_isGET();
        $cartid = $request->get('cartid');
        if($cartid){
            $result = CartModel::instance()->del($cartid) ? 1 : 0;
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }
    }
   /**
     * 购物车数量操作
     */
    public function edit(Request $request){
        $this->_isPOST();
        $cartid = $request->post('cartid');
        $type = $request->post('type');
        if(empty($cartid)) $this->outPut(null,1001,"cartid");
        if(empty($type)) $this->outPut(null,1001,"type");
        $result = CartModel::instance()->cartedit($cartid,$type);
        if($result == 1){
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }

    }
    /**
     * 测试
     */
    public function text(){
        $data = Db::query("select * from hjmall_order");
        var_dump($data);die;
    }
}
