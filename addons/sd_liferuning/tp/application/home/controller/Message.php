<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Request;

class Message extends Controller{
    public function _initialize(Request $request = null){

    }
    public function index(){

        $bid = $this->_getBid();
        if(\request()->isPost()){
            $data=input('post.');
            $rs=db('Mess')->where('bid',$bid)->update($data);
            if($rs){
                return $this->success('更新成功');
            }else{
                return $this->error('更新失败');
            }
        }else{
            $result=db('Mess')->where('bid',$bid)->find();
            if(!$result){
                db('Mess')->where('bid',$bid)->insert(['bid'=>$bid]);
                $result=db('Mess')->where('bid',$bid)->find();
            }
            return view('message/index',['data'=>$result]);
        }



    }
    public function notice(){
        $bid = $this->_getBid();
        $data=db('Notice')->where('bid',$bid)->order('times desc')->paginate(10);
        foreach ($data->all() as $key=>$val){
            $val['content']=mb_substr(strip_tags($val['content']),0,20,'utf-8');
            $data[$key]=$val;
        }

        return $this->fetch('',['data'=>$data]);
    }
    public function add(){
        if(\request()->isPost()){
            $data=input('post.');
            $data['times']=time();
            $data['bid']=$this->_getBid();
            if(empty($data['title'])){
                $this->error('标题不能为空');
            }
            $result=db('Notice')->insert($data);
            if($result){
                return $this->success('添加成功','notice');
            }else{
                return $this->error('添加失败');
            }
        }else{
            return view('add');
        }
    }
    public function edit($id){
        $result=db('Notice')->where('id',$id)->find();
        if(empty($result)){
            return $this->error('修改的内容不存在');
        }else{
            if(\request()->isPost()){
                $save=input('post.');
                if(db('Notice')->where('id',$id)->update($save)){
                    return $this->success('更新成功','notice');
                }
            }else{
                return view('edit',['data'=>$result]);
            }

        }

    }
    public function delete($id){
        if(db('Notice')->where('id',$id)->delete()){
            echo 1;
        }
    }
    public function clause(){
        return view('');
    }


    /*
          服务号
     * */
    public function service(){
        $bid=$this->_getBid();
        $result=db('WxService')->where('bid',$bid)->find();
        if(empty($result))db('WxService')->insert(['bid'=>$bid]);
        if(\request()->isPost()){
            $data=input('post.');
            $rs=db('WxService')->where('bid',$bid)->update($data);
            if(!empty($_FILES['file'])){
                if($_FILES['file']['tmp_name'][0]){
                    if($_FILES['file']['type'][0]!='text/plain')return $this->error('请上传正确文件');
                    $rs=move_uploaded_file($_FILES['file']['tmp_name'][0],$_SERVER['DOCUMENT_ROOT'].'/'.$_FILES['file']['name'][0]);
                    if(!$rs)return $this->error('文件上传失败');
                }
                if($_FILES['file']['tmp_name'][1]){
                    if($_FILES['file']['type'][1]!='text/plain')return $this->error('请上传正确文件');
                    $rs=move_uploaded_file($_FILES['file']['tmp_name'][1],$_SERVER['DOCUMENT_ROOT'].'/'.$_FILES['file']['name'][1]);
                    if(!$rs)return $this->error('文件上传失败');
                }
            }
            if($rs){
                return $this->success('更新成功');
            }else{
                return $this->error('更新失败');
            }
        }
    	return view('message/service',['data'=>$result]);
    }
}

