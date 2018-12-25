<?php
namespace app\api\model;

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
            //$val['member'] = $val['discount'] * $val['min_price'];
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
        $list = Db::name('goods')->field($fieid)->where($map)->order('sort asc')->select();
        return $list;
    }
    /**
     * 商品列表
     */
    public function goodslistlimt($map,$fieid){
        $list = Db::name('goods')->field($fieid)->where($map)->order('sort asc')->limit(4)->select();
        return $list;
    }
    /**
     * 特价商品
     */
    public function specialOffer($map,$fieid){
        $list = Db::name('goods')->field($fieid)->where($map)->order('sort asc')->limit(4)->select();
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
                if($val['rule']){
                    $rulea[$key] = $val['rule'];
                }

                if($val['rule1']){
                    $ruleb[$key] = $val['rule1'];
                }
                if($val['rule2']){
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
/**
     * 搜索
     */
    public function search($title,$fieid){
        $list = Db::name('goods')->field($fieid)->where('name','like','%'.$title['name'].'%')->where('bid',$title['bid'])->order('sort desc')->select();
        return $list;
    }
/**
     * 热销 推荐 积分 商品   
     */
    public function goodsclassify($goods,$fieid){
        if($goods['type'] == 1){
            $list = Db::name('goods')->field($fieid)->where(['bid'=>$goods['bid'],'special_offer'=>1,'status'=>1])->order('sort desc')->select();
        }elseif ($goods['type'] == 2){
            $list = Db::name('goods')->field($fieid)->where(['bid'=>$goods['bid'],'recom'=>1,'status'=>1])->order('sort desc')->select();
        }elseif ($goods['type'] == 0){
            $list = Db::name('goods')->field($fieid)->where(['bid'=>$goods['bid'],'integral'=>1,'status'=>1])->order('sort desc')->select();
        }
        return $list;
    }
/**
     * 新闻列表
     */
    public function newslist($bid){
        $list = Db::name('hzpNews')->field('neid,title,status,createtime')->where(['status'=>0,'bid'=>$bid])->order('createtime desc')->select();
        return $list;
    }
    /**
     * 新闻详情
     */
    public function newsdetails($id){
        $list = Db::name('hzpNews')->field('neid,title,content,createtime,status')->where(['status'=>0,'neid'=>$id])->find();
        return $list;
    }
    /**
     * 时间
     */
    public function times($id){
        $field = 'week,wprice,time,id';
        $list = db('week')->field($field)->where('bid',$id)->select();
        foreach ($list as $key=>&$va){
            $oneype = db('hour')->field('hour,hprice,hid')->where(['wid'=>$va['id'],])->select();
            $va['vdo'] = $oneype;
        }
        return $list;
    }
    /**
     * 金额
     */
    public function price($id){
        //var_dump($id['wid']);die;
        $date = [
          '星期天','星期六,星期一,星期二，星期三，星期四，星期五'
        ];
        if(in_array($id['wid'],$date)){
            $price = 1;
        }else{
            $price = 0;
        }
        return $price;
    }
    /**
     * 距离计算金额
     */
    function addprice($id, $unit=2, $decimal=2){
        $EARTH_RADIUS = 6370.996; // 地球半径系数
        $PI = 3.1415926;
        $radLat1 = $id['myaddswd'] * $PI / 180.0;
        $radLat2 =$id['mudaddswd'] * $PI / 180.0;
        $radLng1 = $id['myaddsjd'] * $PI / 180.0;
        $radLng2 = $id['mudaddsjd'] * $PI /180.0;
      	$radLat1=round($radLat1,4);
      $radLat2=round($radLat2,4);
      $radLng1=round($radLng1,4);
      $radLng2=round($radLng2,4);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $distance = 2 * asin(sqrt(pow(sin($a/2),2) + cos($radLat1) * cos($radLat2) * pow(sin($b/2),2)));
        $distance = $distance * $EARTH_RADIUS * 1000;
        if($unit==2){
            $distance = $distance / 1000;
        }
        return round($distance, $decimal);
    }
    /**
     * 重量金额
     */
    public function WeightPrice($bid,$weight,$region_id){
        if($region_id!=0){
            $field='int_kg as first,int_kg_price as freight,on_kg as next,on_kg_price as freight1';
            $list=db('region')->where(['region_id'=>$region_id])->field($field)->find();
        }else{
            $field = 'first,freight,next,freight1';
            $list = db('freight')->field($field)->where('bid',$bid)->find();
        }
        if(empty($list)){
            $price = 0;
            return $price;
        }
        $price = $weight < $list['first'] ? $list['freight'] : $list['freight'] + ($weight-$list['first'])/$list['next']*$list['freight1'];
        return $price;
    }
    /**
     * 重量金额
     */
    public function Insprice($bid){
        $field = 'instype,insprice,bid,id';
        $list = db('run_rules')->field($field)->where('bid',$bid)->find();
        if($list['instype'] == 0){
            $code = 0;
            return $code;
        }
        //$price = $weight < $list['next'] ? $list['freight'] : $list['freight'] + $weight/$list['next']*$list['freight1'];
        return $list;
    }
    /**
     * 距离金额
     **/
    public function getFreight($bid){
        $field = 'first,freight,next,freight1';
        $res = db('run_dis_freight')->field($field)->where(['bid'=>$bid,'status'=>1])->find();
        return $res;
    }
    /**
     * 悬赏金额
     **/
    public function reward($bid){
        $field = 'reward';
        $res = db('run_rules')->field($field)->where(['bid'=>$bid])->find();
        return $res;
    }
    /**
     * 悬赏金额
     **/
    public function distype($bid){
        $field = 'distype';
        $res = db('run_rules')->field($field)->where(['bid'=>$bid])->find();
        return $res;
    }
    /**
     * 获取区域代理距离计算
     */
    public function region($adds,$bid){
        $mylocation=[
            'lng'=>$adds['myaddsjd'],
            'lat'=>$adds['myaddswd']
        ];
        $field='int_km as first,int_km_price as freight,on_km as next,on_km_price as freight1,region_id,proxy_id,location,region_id';
        $region=db('region')->field($field)->where(['bid'=>$bid,'status'=>1])->select();
        if($region){
            foreach ($region as $key=>$val){
                $location=json_decode($val['location'],1);
                $rs=is_point_in_polygon($mylocation,$location);
                if($rs){
                    return  $val;
                    break;
                }
            }
        }else{
            return false;
        }
    }
}
