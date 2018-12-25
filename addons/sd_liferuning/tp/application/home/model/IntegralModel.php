<?php
namespace app\home\model;

use think\Db;

class IntegralModel  
{
    /**
     * 单例模式
     * @return IntegralModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new IntegralModel();
        }
        return $m;
    }
    /**
     * 页面展示
     *
     */
    public function show($bid){
        $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
        $list = Db::name('goods')->field($field)->where($bid)->order('sort asc')->paginate(10);
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

        $data['integral'] = 1;
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
     * 积分详情
     *
     */
    public function intdetails($goodsid){
        $field = 'g.recom,g.special_offer,g.goodsid,g.bid,g.pic,g.name,g.unit,g.explain,g.max_price,g.min_price,g.readnum,
        g.favournum,g.stock,g.sales,g.recom,g.content,g.updatetime,g.createtime,g.shelves_time,g.integral,
        g.need_integral,g.gain_integral,g.status,g.discount,g.pics,g.type,g.menu,g.weight,g.template,g.sort,
        g.freight_count,g.freight_unify,g.open_rule';
        //$list = Db::name('goods')->field($field)->where('goodsid',$goodsid)->find();
        $list = Db::name('goods')
            ->alias('g')
            ->where('goodsid',$goodsid)
            ->field($field)
        
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

            $field = 'goodsid,pic,name,sort,favournum,recom,discount,recom,special_offer,unit,min_price,stock,sales,status';
            $list = Db::name('goods')->field($field)->where('name','like',"%".$data['name']."%")->where(['bid'=>$bid,'status'=>1,'integral'=>1])->order('sort asc')->paginate(200);
            $list->toArray();
            $result = $this->searchsult($list);
            return $result;


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


}
