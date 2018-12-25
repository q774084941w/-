<?php
namespace app\index\model;

use think\Controller;
use think\Db;

class GoodsModel  
{
    /**
     * 单例模式
     * @return GoodsModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new GoodsModel();
        }
        return $m;
    }
    /**
     * 页面展示
     *
     */
    public function show(){
        $field = 'goodsid,pic,name,sort,favournum,recom,discount,unit,min_price,stock,sales,status';
        $list = Db::name('goods')->field($field)->where('status',1)->select();
        foreach ($list as $key=>&$val){
            $val['pic'] = uploadpath('goods',$val['pic']);
            $val['member'] = $val['discount'] * $val['min_price'];
        }
        return $list;
    }
    /**
     *
     * 添加页面
     */
    public function addition(){
        $field = 'tid,bid,name,status';
        $list = Db::name('menus')->field($field)->where('status',1)->select();
    }
    /**
     *
     * 添加页面  菜单
     */
    public function menus(){
        $field = 'tid,bid,name,status';
        $list = Db::name('menus')->field($field)->where('status',1)->select();
        return $list;
    }
    /**
     *
     * 添加页面  运费模板
     */
    public function freight(){
        $field = 'freid,bid,title,status,unit';
        $list = Db::name('freight')->field($field)->where('status',1)->select();
        return $list;
    }
    /**
     * 添加数据库
     */
    public function addgoods($data){

        $data['type'] = $data['datatype_one'];
        $data['pics'] = $data['file'];
        $data['template'] = $data['datatype_two'];
        $data['freight_unify'] = $data['unify_freight'];
        $data['createtime'] = time();

        $commtype = $data['caidan'];
        unset($data['datatype_one']);
        unset($data['file']);
        unset($data['unify_fre']);
        unset($data['datatype_two']);
        unset($data['caidan']);
        if(!empty($commtype)) $data['open_rule'] = 1;
        $goodsid = Db::name('goods')->insertGetId($data);

        if(!empty($goodsid) || $data['open_rule'] == 1){
            foreach ($commtype as $key=>&$val){
                foreach ($val as $k=>&$v){
                    if($k == 0){
                        $attr['rule'] = $v;
                    }elseif ($k == 1){
                        $attr['rule1'] = $v;
                    }elseif ($k == 2){
                        $attr['rule2'] = $v;
                    }elseif ($k == 3){
                        $attr['price'] = $v;
                    }elseif ($k == 4){
                        $attr['stock'] = $v;
                    }

                }
                $val = $attr;
                $val['goodsid'] = $goodsid;
                $goodsttr[] = $val;
            }
            $attrdata = Db::name('goodsAttr')->insertAll($goodsttr);

            if(!empty($attrdata)){
                return $attrdata;
            }else{
                echo '添加失败';
            }

        }else{
            echo '添加失败';
        }
        
    }

    /**
     * 详情
     *
     */
    public function details($goodsid){
        $field = 'goodsid,bid,pic,name,unit,max_price,min_price,readnum,favournum,stock,sales,recom,content,updatetime,createtime,shelves_time,integral
        need_integral,gain_integral,status,discount,pics,type,menu,weight,template,sort,freight_count,freight_unify,open_rule';
        $list = Db::name('goods')->field($field)->where('goodsid',$goodsid)->find();
        if($list){
            $commType = Db::name('commType')->field('attrid,rule,rule1,rule2,price,stock')->where('goodsid',$goodsid)->select();
            $list['specification'] = $commType;
            $freight = Db::name('freight')->field('title')->where('freid',$list['template'])->find();
            $list['template'] = $freight;
            return $list;
        }else{
            return false;
        }

    }

    /**
     * 商家详情
     */
    public function buslist($map,$fieid){
        $list = Db::name('business')->field($fieid)->where($map)->find();
        return $list;
    }
    /**
     * 商品列表
     */
    public function goodslist($map,$fieid){
        $list = Db::name('goods')->field($fieid)->where($map)->select();
        return $list;
    }
    /**
     * 特价商品
     */
    public function specialOffer($map,$fieid){
        $list = Db::name('goods')->field($fieid)->where($map)->select();
        return $list;
    }
    /**
     * 商品详情
     */
    public function getDetail($where,$field){
        Db::name('goods')->where($where)->setInc('readnum', 1,180);
        $goodsinfo = Db::name('goods')->field($field)->where($where)->find();
        $pics = explode(',',$goodsinfo['pics']);
        foreach ($pics as &$v){
            $v = uploadpath('goods',$v);
        }
        $goodsinfo['pics'] = $pics;
        if($goodsinfo['open_rule'] == 1){
            $goodsAttr = Db::name('goodsAttr')->field('attrid,goodsid,rule,rule1,rule2,price,stock,pic,sales')->where('goodsid',$goodsinfo['goodsid'])->select();

            foreach ($goodsAttr as $key=>$val){

                if(isset($val['rule'])){
                    $rulea[$key] = $val['rule'];
                }

                if(isset($val['rule1'])){
                    $ruleb[$key] = $val['rule1'];
                }
                if(isset($val['rule2'])){
                    $rulec[$key] = $val['rule2'];
                }
            }
	
            $rulea = array_unique($rulea);

            $rule1 = array_unique($ruleb);

            $rule2 = array_unique($rulec);

            $goodsinfo['specification'] = [$rulea,$rule1,$rule2];
        }else{
            $goodsinfo['specification'] = (object)null;
        }
        
        return $goodsinfo;
    }


}
