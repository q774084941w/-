<?php
namespace app\api\model;

use think\Db;

class CollectModel
{
    /**
     * 单例模式
     * @return CollectModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CollectModel();
        }
        return $m;
    }
    /**
     * 添加收藏
     */
    public function addcollect($goodsid,$uid){
        $data = [
            'uid' => $uid,
            'goodsid' => $goodsid,
            'createtime' => time()
        ];
        $result = Db::name('collect')->insert($data);
        Db::name('goods')->where('goodsid', $goodsid)
            ->setInc('favournum');
        return $result;
    }
    /**
     * 收藏列表
     */
    public function collectlist($uid){
        $fieid = 'c.colid,c.uid,c.goodsid,c.createtime,g.pic,g.name,g.unit,g.min_price,g.explain';
        $list = Db::name('collect')->alias('c')->join('135k_goods g','c.goodsid = g.goodsid')
            ->field($fieid)
            ->where('uid',$uid)
            ->select();
        return $list;
    }
    /**
     * 删除收藏
     */
    public function delecoll($collid){
        $result = Db::name('collect')->delete($collid);
        return $result;
    }

}
