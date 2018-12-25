<?php
namespace app\index\model;

use think\Controller;
use think\Db;

class GoodsAttrModel 
{
    /**
     * 单例模式
     * @return GoodsAttrModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new GoodsAttrModel();
        }
        return $m;
    }
  
    /**
     * 检查库存
     */
    public function checkStock($where){
        $fieid = 'attrid,goodsid,sales,rule,rule1,rule2,price,stock';
        $list = Db::name('goodsAttr')->field($fieid)->where($where)->find();
        return $list;
    }

}
