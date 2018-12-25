<?php
namespace app\home\model;

use think\Db;
use think\Request;

class CouponModel  
{
    /**
     * 单例模式
     * @return CouponModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CouponModel();
        }
        return $m;
    }
    /**
     * 优惠券列表
     */
    public function couponlist($bid){
        $result = Db::name('couponAdd')->field('name,disid,number,money,full_money,type,goodsid,bid,commid,starttime,endtime,status')->where(['bid'=>$bid,'delete'=>0])->order('endtime desc')->paginate(10);
        foreach($result as $k=>$val){
            $data = [];
            $data = $val;
            if($val['goodsid'] != 0){
                $goods = Db::name('goods')->field('goodsid,name')->where('goodsid',$val['goodsid'])->find();
                $data['goodsid'] = $goods['name'];
            }elseif ($val['commid'] != 0){
                $menus = Db::name('menus')->field('tid,name')->where('tid',$val['commid'])->find();
                $data['commid'] = $menus['name'];
            }
            if($val['status'] == 0){
                $data['types'] = '已开启';
            }else{
                $data['types'] = '已关闭';
            }
            $data['starttime'] = date('Y-m-d',$val['starttime']);
            $data['endtime'] = date('Y-m-d',$val['endtime']);

            if($val['type'] == 4) $data['type'] = '首单注册红包';
            if($val['type'] == 5) $data['type'] = '转发红包';
            if($val['type'] == 6) $data['type'] = '活动优惠券';
            $result->offsetSet($k,$data);
        }
       
        return $result;

    }
    /**
     * 0开启   1关闭
     */
    public function soldOut($status,$id){
        $result = Db::name('couponAdd')->where('disid',$id)->update(['status'=>$status]);
        return $result;
    }
    /**
     * 添加数据库
     */
    public function adddata($data){
        $result = Db::name('couponAdd')->insert($data);
        return $result;
    }
    /**
     * 修改数据库
     */
    public function updatedata($data){
        $result = Db::name('couponAdd')->update($data);
        return $result;
    }
    /**
     * 添加
     */
    public function addlist($bid){
        $goods = Db::name('goods')->field('goodsid,name')->where('bid',$bid)->select();
        $menus = Db::name('menus')->field('tid,name')->where('bid',$bid)->select();
        return [$goods,$menus];
    }
    /**
     * 编辑
     */
    public function couponedit($disid){
        $val = Db::name('couponAdd')->field('name as cname,sort,timelong,coupontype,disid,number,money,full_money,type,goodsid,bid,commid,starttime,endtime')->where('disid',$disid)->find();
        if($val['goodsid'] != 0){
            $goods = Db::name('goods')->field('goodsid,name')->where('goodsid',$val['goodsid'])->find();
            $val['name'] = $goods['name'];
            $val['dataid'] = $goods['goodsid'];
        }elseif ($val['commid'] != 0){
            $menus = Db::name('menus')->field('tid,name')->where('tid',$val['commid'])->find();
            $val['dataid'] = $menus['tid'];
            $val['name'] = $menus['name'];
        }else{
            $val['dataid'] = '';
            $val['name'] = '';
        }
        if ($val['starttime']){
            $val['starttime'] = date('Y-m-d',$val['starttime']);
            $val['endtime'] = date('Y-m-d',$val['endtime']);
        }
        if($val['type'] == 4) $val['menuss'] = '首单注册红包';
        if($val['type'] == 5) $val['menuss'] = '转发红包';
        if($val['type'] == 6) $val['menuss'] = '活动优惠券';
        return $val;

    }
    /**
     * 删除
     */
    public function deleteban($banid){
        $result = Db::name('couponAdd')->where('disid',$banid)->update(['delete'=>1]);
        return $result;
    }
    


}
