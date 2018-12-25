<?php
namespace app\api\model;

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
    public function show(){
        $field = 'freid,bid,title,unit,first,freight,next,freight1,createtime,status';
        $list = Db::name('freight')->field($field)->where('status',1)->select();

        return $list;
    }
    /**
     * 添加数据库
     *
     */
    public function insert($data){
        
        $list = Db::name('freight')->insert($data);
        if($list){
            return $list;
        }else{
            echo '添加失败';
        }

    }

}
