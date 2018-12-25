<?php

namespace app\api\controller;
use think\Controller;
class Message extends Controller{
    public function listMe($bid){
        $data=db('Notice')->where('bid',$bid)->order('times desc')->select();
        foreach ($data as $key=>$val){
            $data[$key]['times']=date('Y-m-d H:i:s',$val['times']);
        }
        exit(json_encode(['code'=>1,'data'=>$data]));
    }
    public function info($id){
        $data=db('Notice')->where('id',$id)->order('times desc')->find();
        $data['times']=date('Y-m-d H:i:s',$data['times']);
        exit(json_encode(['code'=>1,'data'=>$data]));
    }
    public function clause($bid,$type){
        if(empty($type))$type=1;
        $result=db('Clause')->where(['bid'=>$bid,'type'=>$type])->find();
        if($result){
            exit(json_encode(['code'=>1,'data'=>$result]));
        }else{
            exit(json_encode(['code'=>0,'data'=>'没有条款']));
        }
    }
    /**
     * 语音提醒
     */





}

?>