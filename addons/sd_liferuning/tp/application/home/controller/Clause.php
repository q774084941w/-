<?php
namespace app\home\controller;
use think\Controller;
class Clause extends Controller{
    public function _initialize(){

    }
    public function index(){
        $bid = $this->_getBid();
        $data=db('Clause')->where('bid',$bid)->select();
        if(empty($data)){
            $this->insert();
            $data=db('Clause')->where('bid',$bid)->select();
        }
        foreach ($data as $key=>$val){
            $data[$key]['name']=$data[$key]['type']==1?'客户使用条款':'跑腿使用条款';
            $data[$key]['content']=mb_substr(strip_tags($val['content']),0,20,'utf-8');
        }

        return view('',['data'=>$data]);
    }
    public function insert(){
        $bid = $this->_getBid();
        $insert=[
            'updatetime'=>time(),
            'bid'=>$bid
        ];
        $result=db('Clause')->where(['bid'=>$bid,'type'=>1])->find();
        if(empty($result)){
            $insert['type']=1;
            db('Clause')->insert($insert);
        }
        $result1=db('Clause')->where(['bid'=>$bid,'type'=>2])->find();
        if(empty($result1)){
            $insert['type']=2;
            db('Clause')->insert($insert);
        }
    }
    public function edit($id){
        $result=db('Clause')->where('id',$id)->find();
        if(!$result){
            return $this->error('非法操作!');
        }
        $result['name']=$result['type']==1?'客户使用条款':'跑腿使用条款';
        if(request()->isPost()){
            $save=input('post.');
            $save['updatetime']=time();
            $rs=db('Clause')->where('id',$id)->update($save);
            if($rs){
                return $this->success('更新成功','index');
            }else{
                return $this->error('更新失败');
            }
        }else{
            return view('',['data'=>$result]);
        }
    }
}

?>