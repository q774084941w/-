<?php
namespace app\home\controller;

use app\home\model\BusinessModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;

class Business extends Controller{
    public function _iniaialize(Request $request = null)
    {


    }
   public function index(){
       $bid = $this->_getBid();
       $field = 'bid,name,address,logo,phone,longitude,latitude,num';
       $result = BusinessModel::instance()->buslist($bid,$field);
       foreach($result as &$val){
           $val['logo'] = uploadpath('business',$val['logo']);
       }
      // var_dump($result);exit;
       return view('business/index',['data'=>$result]);
   }


    public function edit(Request $request){

       $id = $this->_getBid();
//        $id = $request->get('id');
        $result = BusinessModel::instance()->busonelist($id);
        $result['logo'] = uploadpath('business',$result['logo']);
        //var_dump($result);exit;

        return view('business/edit',['data'=>$result]);
    }



    public function editer(Request $request){


        $data = $request->post();
        if(!empty($_FILES['image']['tmp_name'])){
            $pic = CommonModel::instance()->upload('business');
            $data['logo'] = $pic;
        }


        $bid =  $data['bid'];
//        $field = 'name,address,phone';
        $result = BusinessModel::instance()->buseditlist($bid,$data);
        if($result){
            $this->success('修改成功', 'business/index');
        }else{
            $this->error('修改失败');
        }
    }
}