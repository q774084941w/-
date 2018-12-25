<?php
namespace app\home\model;

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
    public function show($bid){
        $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
        $list = Db::name('goods')->field($field)->where($bid)->order('sort asc')->paginate(15);
        $list->toArray();
        foreach($list as $k=>$v){
            $data = [];
            $data = $v;
            $data['pic'] = uploadpath('goods',$v['pic']);
            if($v['status'] == 1){
                $data['status'] = '已上架';
            }elseif ($v['status'] == 0){
                $data['status'] = '已下架';
            }
            if($v['recom'] == 1 && $v['special_offer'] == 1){
                $data['recomm'] = '热销商品/特价商品';
            }elseif ($v['recom'] == 1 || $v['special_offer'] == 1){
                if($v['recom'] == 1){
                    $data['recomm'] = '热销商品';
                }elseif ($v['special_offer'] == 1){
                    $data['recomm'] = '特价商品';
                }else{
                    $data['recomm'] = '正常';
                }
            }else{
                $data['recomm'] = '正常';
            }
            
            $list->offsetSet($k,$data);
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
    public function menus($bid){
        $field = 'tid,bid,name,status';
        $list = Db::name('menus')->field($field)->where($bid)->select();
        return $list;
    }
    /**
     *
     * 添加页面  运费模板
     */
    public function freight($bid){
        $field = 'freid,bid,title,status,unit,type';
        $list = Db::name('freight')->field($field)->where($bid)->select();
        return $list;
    }
    /**
     * 添加数据库
     */
    public function addgoods($data,$imgs){
        $data['pics'] = $imgs;
        $data['type'] = $data['menu'];
        $data['menu'] = $data['datatype_one'];

        if(isset($data['recom'])) $data['recom'] = $data['recom'];
        if(isset($data['unify_freight'])){
            $data['freight_unify'] = $data['unify_freight'];
        }else{
            $data['template'] = $data['datatype_two'];
            unset($data['freight_unify']);
        }

        $data['createtime'] = time();
        $data['shelves_time'] = time();
        //print_r($data);exit;
        $commtype = $data['caidan'];
        unset($data['datatype_one']);
        unset($data['file']);
        unset($data['unify_freight']);
        unset($data['unify_fre']);
        unset($data['datatype_two']);
        unset($data['caidan']);
        unset($data['imgurl']);
        if(!empty($commtype)){
            $data['open_rule'] = 1;
        }else{
            $data['open_rule'] = 0;
        }

        $goodsid = Db::name('goods')->insertGetId($data);

        if(!empty($goodsid) && $data['open_rule'] == 1){
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
                return 0;
            }

        }else{
            return $goodsid;
        }
        
    }
    /**
     * 添加数据库
     */
    public function editinsert($data,$imgs){
        $data['pics'] = $imgs;

        if(isset($data['unify_freight']) && $data['unify_freight'] != 0){
            $data['freight_unify'] = $data['unify_freight'];
            $data['template'] = 0;
        }else{
            $data['template'] = $data['datatype_two'];
            $data['freight_unify'] = 0;
        }

        $data['createtime'] = time();
        $data['shelves_time'] = time();
        //print_r($data);exit;
        //if($data['freight_unify'] > 0) $data['template'] = 0;
        unset($data['datatype_one']);
        unset($data['file']);
        unset($data['unify_freight']);
        unset($data['unify_fre']);
        unset($data['datatype_two']);
        unset($data['caidan']);
        unset($data['imgurl']);
        if(!empty($commtype)){
            $data['open_rule'] = 1;
        }else{
            $data['open_rule'] = 0;
        }

        //print_r($data);exit;special_offer recom
        if(!isset($data['recom'])) $data['recom'] = 0;
        if(!isset($data['special_offer'])) $data['special_offer'] = 0;
       
        $goodsid = Db::name('goods')->update($data);

            return $goodsid;


    }

    /**
     * 详情
     *
     */
    public function details($goodsid){
        $field = 'g.recom,g.special_offer,g.goodsid,g.bid,g.pic,g.name,g.unit,g.explain,g.max_price,g.min_price,g.readnum,
        g.favournum,g.stock,g.sales,g.recom,g.content,g.updatetime,g.createtime,g.shelves_time,g.integral,
        g.need_integral,g.gain_integral,g.status,g.discount,g.pics,g.type,g.menu,g.weight,g.template,g.sort,
        g.freight_count,g.freight_unify,g.open_rule,m.name as mname,m.tid,c.name as ptname,c.ptid';
        //$list = Db::name('goods')->field($field)->where('goodsid',$goodsid)->find();
        $list = Db::name('goods')
            ->alias('g')
            ->where('goodsid',$goodsid)
            ->field($field)
            ->join('135k_menus m','g.menu = m.tid')
            ->join('135k_comm_type c','g.type = c.ptid')
            ->find();
        if($list['open_rule'] == 1){
            $commType = Db::name('goodsAttr')->field('attrid,rule,rule1,rule2,price,stock')->where('goodsid',$goodsid)->select();
            $list['specification'] = $commType;
            $freight = Db::name('freight')->field('title')->where('freid',$list['template'])->find();
            $list['template'] = $freight;
            return $list;
        }else{
            return $list;
        }

    }
    /**
     * 上下架
     */
    public function soldOut($type){
        $type = Db::name('goods')->where('goodsid',$type['goodsid'])->update(['status'=>$type['status']]);
        return $type;
    }


    /**
     * 编辑图片
     */
    public function editajax($id){

        $resylt = Db::name('goods')->field('pics')->find($id);
        if($resylt['pics']){
            $pics = explode(',',$resylt['pics']);
            foreach($pics as $key=>&$val){
                $val = uploadpath('goods',$val);
            }

        }else{
            $pics = [];
        }

        return $pics;
    }
/**
     * 改库存
     */
    public function inventory($data){
        $type = Db::name('goods')->where('goodsid',$data['id'])->update(['stock'=>$data['val']]);
        return $type;
    }
    /**
     * 搜索
     */
    public function goodssearch($data,$bid){
        if(isset($data['name'])){
            $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
            $list = Db::name('goods')->field($field)->where('name','like',"%".$data['name']."%")->where(['bid'=>$bid,'status'=>1,'integral'=>0])->order('sort asc')->paginate(200);
            $list->toArray();
            $result = $this->searchsult($list);
            return $result;
        }elseif (is_numeric($data['pid_one']) && !is_numeric($data['pid_two']) && !is_numeric($data['pid_three'])){
            $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
            $list = Db::name('goods')->field($field)->where('menu',$data['pid_one'])->where(['bid'=>$bid,'status'=>1])->order('sort asc')->paginate(200);
            $list->toArray();
            $result = $this->searchsult($list);
            return $result;
        }elseif (is_numeric($data['pid_one']) && is_numeric($data['pid_two']) && !is_numeric($data['pid_three'])){
            $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
            $list = Db::name('goods')->field($field)->where('type',$data['pid_two'])->where(['bid'=>$bid,'status'=>1])->order('sort asc')->paginate(200);
            $list->toArray();
            $result = $this->searchsult($list);
            return $result;
        }elseif (is_numeric($data['pid_one']) && is_numeric($data['pid_two']) && is_numeric($data['pid_three'])){
            $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
            $list = Db::name('goods')->field($field)->where('type',$data['pid_three'])->where(['bid'=>$bid,'status'=>1])->order('sort asc')->paginate(200);
            $list->toArray();
            $result = $this->searchsult($list);
            return $result;
        }else{
            $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
            $list = Db::name('goods')->field($field)->where(['bid'=>$bid,'status'=>1])->order('sort asc')->paginate(200);
            $list->toArray();
            $result = $this->searchsult($list);
            return $result;
        }

    }
    public function searchsult($list){

        foreach($list as $k=>$v){
            $data = [];
            $data = $v;
            $data['pic'] = uploadpath('goods',$v['pic']);
            if($v['status'] == 1){
                $data['status'] = '已上架';
            }elseif ($v['status'] == 0){
                $data['status'] = '已下架';
            }
            if($v['recom'] == 1 && $v['special_offer'] == 1){
                $data['recomm'] = '热销商品/特价商品';
            }elseif ($v['recom'] == 1 || $v['special_offer'] == 1){
                if($v['recom'] == 1){
                    $data['recomm'] = '热销商品';
                }elseif ($v['special_offer'] == 1){
                    $data['recomm'] = '特价商品';
                }else{
                    $data['recomm'] = '正常';
                }
            }else{
                $data['recomm'] = '正常';
            }

            $list->offsetSet($k,$data);
        }

        return $list;
    }
    /**
     * 删除
     */
    public function deleteban($banid){
        $result = Db::name('goods')->where('goodsid',$banid)->update(['delete'=>1]);
        return $result;
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

    /**
     * 距离金额
     **/
    public function getFreight($bid){
        $field = 'first,freight,next,freight1';
        $res = db('run_dis_freight')->field($field)->where(['bid'=>$bid,'status'=>1])->find();
        return $res;
    }
}
