<?php
namespace app\home\controller;


use app\home\model\AddressModel;
use think\Controller;
use think\Request;

class Address extends Controller
{
   /**
    * 地址类
    */
    public function _iniaialize(Request $request = null)
    {


    }
    public function index(){
        echo '地址类';
    }

}
