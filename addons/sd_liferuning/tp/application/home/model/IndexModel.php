<?php
namespace app\home\model;

use think\Db;
use app\home\model\CommonModel;
use think\helper\hash\Md5;

class IndexModel
{

    /**
     * 统计类
     * @param $object  数组
     */



    /**
     * 单例模式
     * @return GoodsModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new IndexModel();
        }
        return $m;
    }
    /**
     * 昨日访问页面
     * @param $object  数组
     */
    public function visit_url($bid)
    {
        $data = CommonModel::instance()->readLog($bid);
        if($data !== false){
            $data = $this->object_array($data);
            foreach($data as $key=>&$val){
                if(empty($val)){
                    unset($data[$key]);
                }
            }

            return $data;
        }else{
            return '0';
        }
    }
    /**
     * stdClass转换数组
     * @param $object  数组
     */
    function object_array(&$object) {
        $object =  json_decode( json_encode( $object),true);
        return  $object;
    }

    /**
     * 用户统计累计访问
     * @param
     */
    function all_visit() {
        $data = Db::query('select creattime from visit_log ');
        foreach($data as $k=>$v){
            $share[$v['creattime']][] = $v;
        }
        return $share;
    }

    /**
     * 新用户访问数（昨天）
     * @param
     */
    function new_user() {
        $result = Db::table('visit_log')->field('creattime')->where(function($query){
            $query->where('creattime','>=', date('Y-m-d',time()-86400));
            $query->where('creattime','<',date('Y-m-d',time()));
        })->count();
        return $result;
    }

    /**
     * 用户分析
     * @param
     */
    function user_analyze() {
        //昨天注册
        $data['yesterday'] = Db::table('user')->where(function($query){
            $query->where('regtime','>=', strtotime(date('Y-m-d',time()-86400)));
            $query->where('regtime','<',strtotime(date('Y-m-d',time())));
        })->count();
        //上一个月注册
        $data['month'] = Db::table('user')->where(function($query){
            $query->where('regtime','>=', strtotime(date('Y-m-d',strtotime('-1 month', strtotime(date('Y-m', time()).'-01 00:00:00')))));
            $query->where('regtime','<',strtotime(date('Y-m-d',strtotime(date('Y-m', time()).'-01 00:00:00'))));
        })->count();
        //上一周注册
        $data['week'] = Db::table('user')->where(function($query){
            $query->where('regtime','>=', strtotime(date('Y-m-d',strtotime('-2 monday', time()))));
            $query->where('regtime','<',strtotime(date('Y-m-d',(time()-((date('w')==0?7:date('w'))-1)*24*3600))));
        })->count();
        //总用户
        $data['all'] = Db::table('user')->count();
        return $data;
    }

    /**
     * 用户分析->新用户数
     * @param
     */
    function new_analyze() {
        //总用户
        $data = Db::table('user')->field('regtime')->select();
        foreach($data as $key=>$val){
            $val['regtime'] = date('Y-m-d',$val['regtime']);
            $all[$val['regtime']][] = $val;
        }
        return $all;
    }

    /**
     * 用户分析->访问人数
     * @param
     */
    function new_visit() {
        $data = Db::table('visit_log')->field('creattime')->where('status','1')->select();
        foreach($data as $k=>$v){
            $share[$v['creattime']][] = $v;
        }
        return $share;
    }
    /**
     * 检测登录
     */
    public function checkLogin(){
      return header('Location:'.'https://'.$_SERVER['HTTP_HOST']);
    }
    /*
     * 微擎數據插入
     */
    public function insertWei($data,$wid){
        $datas = Db::name('business')->field('bid,uniacid')->where('uniacid',$wid)->find();
        //var_dump($wid);die;
//        var_dump($datas['bid']);die;
        if($datas){
            return $datas;
        }
        else{
            $data['content'] = 0;
            $data['subscribe_money'] = 0;
            $data['subscribe_num'] = 0;
            $data['help'] = 0;
            $data['about_us'] = 0;
            $data['aftermarket'] = 0;
            $result = Db::name('business')->insertGetId($data);
            return ['bid' => $result];
        }


    }
}
