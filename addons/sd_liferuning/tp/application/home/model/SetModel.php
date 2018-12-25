<?php
namespace app\home\model;

use think\Db;

class SetModel
{
    /**
     * 单例模式
     * @return UserModel
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new SetModel();
        }
        return $m;
    }
    /**
     * 基本设置
     */
    public function SetList($bid){
        $list = Db::name('run_rules')->where('bid',$bid)->find();
        return $list;
    }
    /**
     * 添加基本设置
     */
    public function Setadd($bid,$data){
        $list = Db::name('run_rules')->where('bid',$bid)->find();
        if(empty($list)){
            $lists = Db::name('run_rules')->insert($data);
        }else{
            $lists = Db::name('run_rules')->where('bid',$bid)->update($data);
        }
        return $lists;
    }
    /*
     * 添加区域选择
     * */
    public function areaAdd($data,$del,$bid){
        $num = 0;
        foreach ($del as $v){
            $res = Db::name('run_area')->delete($v);
            if ($res){
                $num ++;
            }
        }
        foreach ($data as $k => $v){
            $res = Db::name('run_area')->where(['area'=>$v[2],'city'=>$v[1],'bid'=>$bid])->find();
            if (!$res){
                Db::name('run_area')->insert(['bid'=>$bid,'province'=>$v[0],'city'=>$v[1],'area'=>$v[2]]);
                $num ++;
            }
        }
        return $num;
    }
    /*
     * 获取已选择区域列表
     * */
    public function areaList($bid){
        return Db::name('run_area')->where(['bid'=>$bid])->select();
    }
}
