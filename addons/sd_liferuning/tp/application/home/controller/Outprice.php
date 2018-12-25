<?php
namespace app\home\controller;
use think\Controller;
use app\home\model\OutpriceModel;
/**
 * 用户端提现
 */
class Outprice extends Controller{
    public function index(){
        $bid = $this->_getBid();
        $data=OutpriceModel::priceList($bid);
        return view('',['data'=>$data]);
    }
    public function consent($id){
        if(intval($id)<1){
            return $this->error('非法操作！');
        }
        $result=OutpriceModel::consent($id);
        if($result){
           return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
    public function refuse($id){
        if(intval($id)<1){
            return $this->error('非法操作！');
        }
        $result=OutpriceModel::refuse($id);
        if($result){
            return $this->success('操作成功');
        }else{
            return $this->error('操作失败');
        }
    }
    public function weixin(){
        $rs=db('Business_pay')->alias('b')
            ->join('User u','b.uid=u.uid')
            ->order('b.out_id desc')
            ->field('u.nickname,b.*')
            ->paginate(10);
        return view('',['data'=>$rs]);

    }
    public function start($id,$start){
        if(request()){
            if($start=='off'){
                $rs=OutpriceModel::off($id);
                if($rs)exit(json_encode(['code'=>1]));
            }
            if($start=='on'){
                $rs=OutpriceModel::on($id);
                if($rs)exit(json_encode(['code'=>1]));
            }
        }
    }
}

?>