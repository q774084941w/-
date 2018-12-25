<?php
namespace app\home\controller;

use app\home\model\CartModel;
use think\Controller;
use think\Request;

class Cart extends Controller
{
   /**
    * 购物车类
    *
    */
    public function _iniaialize(Request $request = null)
    {


    }
    public function index(){
        echo '购物车类';
        
    }

}
