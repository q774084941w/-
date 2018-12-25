<?php
namespace app\home\controller;

use app\home\model\RewardModel;
use think\Controller;
use think\Request;

class Reward extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /*
     * 主页
     * */
    public function index(Request $request){
        $bid = $this->_getBid();
        $data = db('reward')->where('bid',$bid)
            ->order('rid desc')
            ->select();
        foreach ($data as $k => $v){
            if($v['type'] == 1){
                $data[$k]['type'] = '满额';
            }else{
                $data[$k]['type'] = '满单';
            }
            if($v['status'] == 1){
                $data[$k]['status'] = '开启';
            }else{
                $data[$k]['status'] = '关闭';
            }
            $data[$k]['restartime'] = date('Y-m-d',$v['restartime']);
            $data[$k]['reendtime'] = date('Y-m-d',$v['reendtime']);
        }
        return $this->fetch('reward/index',['data'=>$data]);
    }
    public function add(){

        return $this->fetch('reward/add');
    }
    /**
     *  添加数据到数据库
     */
    public function insert(Request $request){
        $data = $request->post();
        $bid = $this->_getBid();
        $data['bid'] = $bid;
        $data['restartime'] = strtotime($data['restartime']);
        $data['reendtime'] = strtotime($data['reendtime']);
        $result = db('reward')->insert($data);
        if($result){
            return $this->success('添加成功','reward/index');
        }else{
            return $this->success('添加失败','reward/index');
        }
    }
    /**
     * 0 开启  1 关闭
     */
    public function soldOut(Request $request){
        $id = $request->post('goodsid');
        $status = $request->post('status');
        $result  = RewardModel::instance()->soldOut($status,$id);
        return $result;
    }
    /**
     * 编辑
     */
    public function edit(Request $request){
        $id = $request->get('id');
        $bid = $this->_getBid();
        $result = db('reward')->where(['rid'=>$id,'bid'=>$bid])->find();
        $result['restartime'] = date('Y-m-d',$result['restartime']);
        $result['reendtime'] = date('Y-m-d',$result['reendtime']);
        return $this->fetch('reward/fulllist',['data'=>$result]);
    }
    /**
     * @return mixed
     * 编辑插入数据
     */
    public function editInsert(Request $request){
        $data = $request->post();
        $bid = $this->_getBid();
        $data['restartime'] = strtotime($data['restartime']);
        $data['reendtime'] = strtotime($data['reendtime']);
        $insert = [
            'fulfil_the_quota'=>$data['fulfil_the_quota'],
            'reward'=>$data['reward'],
            'restartime'=>$data['restartime'],
            'reendtime'=>$data['reendtime'],
            'type'=>$data['type'],
            'status'=>$data['status'],
            'bid'=>$bid
        ];
        db('reward')->where('rid',$data['rid'])->update($insert);
        return $this->success('修改成功','reward/index');
    }
    /**
     * 删除数据
     */
    public function delect(Request $request){
        $id = $request->get('id');
        db('reward')->where('rid',$id)->delete();
        return $this->success('删除成功','reward/index');
    }
    //满单
	public function fulllist(){
        return $this->fetch('reward/fulllist');
    }
}
