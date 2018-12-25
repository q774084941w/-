<?php
namespace app\api\controller;

use app\api\model\CollectModel;
use think\Controller;
use think\Request;

class Collect extends Controller
{
   /**
    * 收藏类
    *
    * 添加收藏
    */
    public function addcollect(Request $request){
        $goodsid = $request->get('goodsid');
        if(empty($goodsid)) $this->outPut('', 1001, ":缺少参数goodsid" );
        $result = CollectModel::instance()->addcollect($goodsid,$this->uid);
        if($result == 1){
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }
    }

    /**
     * 收藏列表
     */
    public function collectlists(){
        $result = CollectModel::instance()->collectlist($this->uid);
        foreach($result as &$value){
            $value['pic'] = uploadpath('goods',$value['pic']);
        }
        $this->jsonOut($result);
    }
    /**
     * 删除收藏
     */
    public function delecoll(Request $request){
        $colid = $request->get('colid');
        $result = CollectModel::instance()->delecoll($colid);
        if($result == 1){
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }
    }
}
