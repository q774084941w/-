<?php
namespace app\home\controller;

use app\home\model\BannerModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;
use think\db;

class Moneys extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 配送时间
     * 列表
     */
    public function times(Request $request){
        //$bid = $request->get('bid');
        $bid = $this->_getBid();

        $field = 'week,wprice,time,id';
        $list = db('week')->field($field)->where('bid',$bid)->select();
        foreach ($list as $key=>&$va){
            $oneype = db('hour')->field('hour,hprice,hid')->where(['wid'=>$va['id'],])->select();
            $va['vdo'] = $oneype;
        }
        return view('moneys/index',['data'=>$list]);
    }
    /**
     * 地址计算距离
     */
    public function distance(Request $request){
        //var_dump(1);die;
        //$bid = $request->get('bid');
        $bid = $this->_getBid();
        $field = 'banid,bid,pic,url,sort,createtime';
        $result = BannerModel::instance()->banlist($bid,$field);
        //var_dump($result);die;
        foreach ($result as &$val){
            //var_dump( uploadpath('banner',$val['pic']));die;
            $val['pic'] = uploadpath('banner',$val['pic']);

            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
        }
        return view('banner/index',['data'=>$result]);
    }
    /**
     *
     * 添加
     */
    public function add(){
        return view('moneys/add');
    }
    public function hadd(){
        $wid = input('param.');
        $wid['wid'];
        return view('moneys/hadd',['wid' =>$wid['wid']]);
    }

    /**
     * 添加数据库 时间星期数
     */
    public function insert(Request $request){
        $bid = $this->_getBid();
        $data = input('param.');
        $data['bid'] = $bid;
        $data['time'] = time();
        $ins = db('week')->insert($data);
        if($ins)
            return $this->success('添加成功','moneys/times')    ;
        else
            return $this->success('抱歉，添加失败','moneys/index');
    }
    public function hourinsert(Request $request){
        $bid = $this->_getBid();
        $data = input('param.');
        if(empty($data['hour'])){
            return $this->success('抱歉，添加失败','moneys/index');
        }
        $data['bid'] = $bid;
        $ins = db('hour')->insert($data);
        if($ins)
            return $this->success('添加成功','moneys/times')  ;
        else
            return $this->success('抱歉，添加失败','moneys/index');
    }
    /**
     * 修改时间  星期
     */
    public function edit(){
        $bid = $this->_getBid();
        $data = input('param.');
        $result = db('week')->where(['id'=>$data['wid'],'bid'=>$bid])->find();
        return view('moneys/edit',['data'=>$result]);
    }
    /**
     * 修改数据 星期
      */
    public function wupdate(){
        $bid = $this->_getBid();
        $data = input('param.');
        $result = db('week')->where(['id'=>$data['id'],'bid'=>$bid])->update(['week'=>$data['week'],'wprice'=>$data['wprice'],'time'=>time()]);
        if($result){
            return $this->success('修改成功','moneys/times')  ;
        }else{
            return $this->success('修改失败','moneys/times')  ;
        }
    }
    /**
     * 修改时间  小时
     */
    public function hedit(){
        $bid = $this->_getBid();
        $data = input('param.');
        $result = db('hour')->where(['hid'=>$data['id'],'bid'=>$bid])->find();
        return view('moneys/hedit',['data'=>$result]);
    }
    /**
     * 修改数据 小时
     */
    public function hupdate(){
        $bid = $this->_getBid();
        $data = input('param.');
        $result = db('hour')->where(['hid'=>$data['hid'],'bid'=>$bid,'wid'=>$data['wid']])->update(['hour'=>$data['hour'],'hprice'=>$data['hprice']]);
        if($result){
            return $this->success('修改成功','moneys/times')  ;
        }else{
            return $this->success('修改失败','moneys/times')  ;
        }
    }
    /*
     * 提成页面
     * */
    public function percent(){
        $bid = $this->_getBid();
        $percent = db('percent')->where(['bid'=>$bid])->find();
        return view('moneys/percent',['percent'=>$percent['percent']]);
    }
    /*
     * 保存提成百分比
     * */
    public function savePercent(){
        $bid = $this->_getBid();
        $percent = input('percent');
        $res = db('percent')->where(['bid'=>$bid])->find();
        if(empty($res)){
            $data = [
                'bid' =>   $bid,
                'percent' => $percent
            ];
            $results = db('percent')->insert($data);
            if($results){
                return $this->success('修改成功','moneys/percent')  ;
            }
        }
        $result = db('percent')->where(['bid'=>$bid])->update(['percent'=>$percent]);
        if($result){
            return $this->success('修改成功','moneys/percent')  ;
        }else{
            return $this->success('修改失败','moneys/percent')  ;
        }
    }
}