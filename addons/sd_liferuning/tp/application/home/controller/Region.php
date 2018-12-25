<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
class Region extends Controller{
    public function _initialize(){

    }
    public function index(){
        $bid = $this->_getBid();
        $data=db('Proxy')->where('bid',$bid)->order('proxy_cretime desc')->paginate(10);
        foreach ($data->all() as $key=>$val){
            $val['proxy_cretime']=date('Y-m-d H:i:s',$val['proxy_cretime']);
            $val['proxy_status']=$val['proxy_status']==1?'开启':'关闭';
            $data[$key]=$val;
        }

       return view('',['data'=>$data]);
    }
    public function add(){
        $bid = $this->_getBid();
        if(request()->isPost()){

            $data=json_decode(input('data'),1);
            $rs=model('region')->add($data,$bid);
            if($rs){
                exit(json_encode(['code'=>1,'msg'=>'添加成功']));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'添加失败']));
            }
        }
        return view();
    }
    public function status($id,$status){
       if(request()->isAjax()){
           Db::startTrans();
           $result=db('Proxy')->where(['proxy_id'=>$id])->update(['proxy_status'=>$status]);
           $rs=db('Region')->where(['proxy_id'=>$id])->update(['status'=>$status]);
           if($result&&$rs){
               Db::commit();
               exit(json_encode(['code'=>1,'msg'=>'执行成功']));
           }else{
               Db::rollback();
               exit(json_encode(['code'=>0,'msg'=>'操作失败']));
           }

       }
    }
    public function delete($id){
        if(request()->isAjax()){
            Db::startTrans();
            $result=db('Proxy')->where(['proxy_id'=>$id])->delete();
            $rs=db('Region')->where(['proxy_id'=>$id])->delete();
            if($result&&$rs){
                Db::commit();
                exit(json_encode(['code'=>1,'msg'=>'删除成功']));
            }else{
                Db::rollback();
                exit(json_encode(['code'=>0,'msg'=>'删除失败']));
            }
        }
    }
    public function share($id,$share){
      if(request()->isAjax()){
          if(!is_numeric($share)||$share>100){
              exit(json_encode(['code'=>0,'msg'=>'请输入正确数值1~100']));
          }
          $result=db('Proxy')->where(['proxy_id'=>$id])->update(['share'=>$share]);
          if($result)exit(json_encode(['code'=>1,'msg'=>'配置成功']));
          exit(json_encode(['code'=>0,'msg'=>'配置失败']));
      }
    }
}


