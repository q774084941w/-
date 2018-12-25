<?php
namespace app\index\model;

use think\Controller;
use think\Db;

class CommTypeModel
{
    /**
     * 单例模式
     * @return CommTypeModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CommTypeModel();
        }
        return $m;
    }
    /**
     * 页面展示
     *
     */
    public function select(){
        $field = 'goodsid,pic,name,unit,min_price,stock,sales,status';
        $list = Db::name('goods')->field($field)->where('status',1)->select();
        foreach ($list as $key=>&$val){
            $val['pic'] = uploadpath('goods',$val['pic']);
        }
        
        return $list;
    }
    /**
     * 添加分类展示（3级）
     */
    public function add(){
        $field = 'ptid,level,name,pid,status';
        $list = Db::name('commType')->field($field)->where(['status'=>1,'level'=>0])->select();
        foreach ($list as $eky=>&$val){
            $listtwo = Db::name('commType')->field($field)->where(['status'=>1,'level'=>1,'pid'=>$val['ptid']])->select();
            foreach ($listtwo as $k=>&$v){
                $listthr = Db::name('commType')->field($field)->where(['status'=>1,'level'=>2,'pid'=>$v['ptid']])->select();
                $listtwo[$k]['sub'] = $listthr;
            }
            $val['sub'] = $listtwo;
        }
        return $list;
    }

    /**
     * 添加数据库
     */
    public function upload($info){
        $data = [
            'pid' => $info['Ptid'],
            'name' => $info['namm'],
            'solt' => $info['solt'],
            'level' => $info['Level'],
            'createtime' => time(),
        ];
        $list = Db::name('commType')->insert($data);
        return $list;
    }
    /**
     * api 二级菜单
     */
    public function twomenus($bid,$field,$order){
        $list = Db::name('commType')->field($field)->where(['status'=>1,'level'=>0,'bid'=>$bid['bid'],'tid'=>$bid['tid']])->order($order)->select();
        foreach ($list as $eky=>&$val){
            $listtwo = Db::name('commType')->field($field)->where(['status'=>1,'level'=>1,'pid'=>$val['ptid'],'bid'=>$bid['bid'],'tid'=>$bid['tid']])->order($order)->select();
            foreach ($listtwo as $k=>&$v){
                $listthr = Db::name('commType')->field($field)->where(['status'=>1,'level'=>2,'pid'=>$v['ptid'],'bid'=>$bid['bid'],'tid'=>$bid['tid']])->order($order)->select();
                $listtwo[$k]['two'] = $listthr;
            }
            $val['two'] = $listtwo;
        }
        return $list;

    }

}
