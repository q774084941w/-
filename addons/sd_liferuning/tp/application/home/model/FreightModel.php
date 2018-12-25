<?php
namespace app\home\model;

use think\Db;

class FreightModel 
{
    /**
     * 单例模式
     *
     * @return FreightModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new FreightModel();
        }
        return $m;
    }
    /**
     * 页面展示
     *
     */
    public function show($bid){
        $field = 'freid,bid,title,unit,first,freight,next,freight1,createtime,status,type,handletime';
        $list = Db::name('freight')->field($field)->where('bid',$bid)->select();

        return $list;
    }
    /**
     * 距离模板页面展示
     *
     */
    public function distance($bid){
        $field = 'freid,bid,title,unit,first,freight,next,freight1,createtime,status,type,handletime';
        $list = Db::name('run_dis_freight')->field($field)->where('bid',$bid)->select();

        return $list;
    }
    /**
     * 添加数据库
     *
     */
    public function insert($data){
        $res = Db::name('freight')->where(['bid'=>$data['bid']])->select();
        if ($res){
            return 1;
        }
        $list = Db::name('freight')->insert($data);
        if($list){
            return $list;
        }else{
            echo '添加失败';
        }
    }
    /**
     * 添加距离模板
     *
     */
    public function dinsert($data){
        $res = Db::name('run_dis_freight')->where(['bid'=>$data['bid']])->select();
        if ($res){
            return 1;
        }
        $list = Db::name('run_dis_freight')->insert($data);
        if($list){
            return $list;
        }else{
            echo '添加失败';
        }
    }
    /**
     * 详情
     */
    public function showdata($id){
        $list = Db::name('freight')->field('freid,bid,title,city,unit,first,freight,next,freight1,status,createtime')->where('freid',$id)->find();
        return $list;
    }
    /**
     * 详情
     */
    public function dshowdata($id){
        $list = Db::name('run_dis_freight')->field('freid,bid,title,city,unit,first,freight,next,freight1,status,createtime')->where('freid',$id)->find();
        return $list;
    }
    /**
     * 上下架
     */
    public function soldOut($type){
        $type = Db::name('freight')->where('freid',$type['goodsid'])->update(['status'=>$type['status']]);
        return $type;
    }
    /**
     * 距离模板上下架
     */
    public function dsoldOut($type){
        $type = Db::name('run_dis_freight')->where('freid',$type['goodsid'])->update(['status'=>$type['status']]);
        return $type;
    }
    /**
     * 编辑
     */
    public function editinsert($data){
        $type = Db::name('freight')->update($data);
        return $type;
    }
    /**
     * 添加时间
     */
    public function InsertDate($data){
        $datas = Db::name('run_setdate')->where('storeid',$data['storeid'])->field('name')->find();
        if(empty($datas)){
            $result = Db::name('run_setdate')->insert($data);
        }else{
            $result = Db::name('run_setdate')->where('storeid',$data['storeid'])->update($data);
        }
        return $result;
    }
    /**
     * 页面初始化时间
     */
    public function GetDate($bid){
        $result = Db::name('run_setdate')->where('storeid',$bid)->find();
        return $result;
    }

}
