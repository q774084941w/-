<?php
namespace app\home\controller;
use think\Controller;
use app\home\model\CardModel;
class  Card extends Controller{
    public function _initialize(){

    }
    public function index(){
        $bid = $this->_getBid();
        $data=CardModel::CardList($bid);

        return view('',['data'=>$data]);
    }
    public function add(){
        $bid = $this->_getBid();
        if(request()->isPost()){
            $data=input('post.');
            $data['bid']=$bid;
            $result=CardModel::add($data);
            if($result){
                return $this->success('上传成功');
            }else{
                return $this->error('上传失败');
            }
        }
        return view('');
    }
    public function edit($id){
        $data=CardModel::info($id);
        if(request()->isPost()){
            $data=input('post.');
            $data['id']=$id;
            $result=CardModel::edit($data);
            if($result){
                return $this->success('更新成功');
            }else{
                return $this->error('更新失败');
            }
        }
        return view('',['data'=>$data]);
    }
    public function delete($id){
        $data['status']=-1;
        $result=db('Card')->where('id',$id)->update($data);
        if($result){
            echo 1;
        }
    }
}

?>