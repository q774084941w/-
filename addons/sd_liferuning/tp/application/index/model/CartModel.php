<?php
namespace app\index\model;

use think\Controller;
use think\Db;


class CartModel 
{
    /**
     * 单例模式
     * @return CartModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CartModel();
        }
        return $m;
    }
    /**
     * 购物车列表
     */
    public function getCartList($uid){
        $result = Db::name('cart')->distinct(true)->field('bid')->where('uid',$uid)->order('createtime desc')->select();
        foreach ($result as  &$value){
            $busin = Db::name('business')->field('bid,name,address,logo')->where('bid',$value['bid'])->find();
            $busin['logo'] = uploadpath('business',$busin['logo']);
            $value['name'] = $busin['name'];
            $value['address'] = $busin['address'];
            $value['logo'] = $busin['logo'];
            $value['goods'] = Db::name('cart')->alias('c')->join('135k_goods g','c.goodsid = g.goodsid')
                ->field('c.rule,c.price,c.rule1,c.rule2,c.num,c.cartid,c.goodsid,g.name,g.pic,g.gain_integral')
                ->where(['c.uid'=>$uid,'c.bid'=>$value['bid']])->select();
            foreach ($value['goods'] as &$val){
                $val['pic'] = uploadpath('goods',$val['pic']);
            }

        }

        return $result;
       
    }

    /**
     * 加入购物车
     * 
     */
    public function addToCart($goodsid,$uid,$number,$attr=[]){
        $map['goodsid'] = $goodsid;
        $map['uid']     = $uid;
        $map['rule'] = isset($attr['rule']) ? $attr['rule'] : '';
        $map['rule1'] = isset($attr['rule1']) ? $attr['rule1'] : '';
        $map['rule2'] = isset($attr['rule2']) ? $attr['rule2'] : '';

        //判断该用户购物车里是否已经添加过该商品
        $nowCart = Db::name('cart')->field('cartid,num')->where($map)->find();
        if(!empty($nowCart)) {
            //若以添加过则该商品数量+1
            $nowCart['num'] += $number;
            $nowCart['updatetime'] = time();
            if (Db::name('cart')->update($nowCart)) {
                $result = $nowCart['cartid'];
            }
        }else{
            $goods = Db::name('goods')->field('goodsid,bid,open_rule,min_price,pic,open_rule')->where('goodsid',$goodsid)->find();
            if($goods['open_rule'] == 0){
                $attr = [
                    'rule' => '',
                    'rule1'=> '',
                    'rule2' => ''
                ];
            }else{
                $goodsAttr = Db::name('goodsAttr')->field('pic,price')->where(['goodsid'=>$goodsid,'rule'=>$map['rule'],'rule1'=>$map['rule'],'rule2'=>$map['rule']])->find();
                $goods['min_price'] = $goodsAttr['price'];
                $goods['pic']       = $goodsAttr['pic'];
            }
            $data = array(
                'goodsid' => $goodsid,
                'uid' => $uid,
                'num' => $number,
                'bid' => $goods['bid'],
                'price'=>$goods['min_price'],
                'pic'  =>$goods['pic'],
                'createtime' => time(),
            );
            $data = array_merge($data,$attr);
            $result = Db::name('cart')->insertGetId($data);
        }
        return $result;
        
    }
    /**
     * 删除购物车
     */
    public function del($cartid){
        $result = Db::name('cart')->delete($cartid);
        if($result !== false){
            return $result;
        }
    }

}
