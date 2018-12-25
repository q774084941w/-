<?php
namespace app\home\controller;

use app\home\model\ExpressModel;
use think\Controller;
use think\Request;

class Express extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
   /**
    * 快递
    *
    */
    public function index(){
        $bid = $this->_getBid();
        $result = ExpressModel::instance()->exprlist($bid);
        foreach ($result as &$val){
            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
        }
        return view('express/index',['data'=>$result]);

    }
    /**
     * 删除
     */
    public function delete(Request $request){
        $id = $request->get('id');
        $result = ExpressModel::instance()->exdelete($id);
        return $result;
    }
    /**
     * 添加
     */
    public function add(){
        return view('express/add');
    }
    /**
     * 添加数据库
     */
    public function insert(Request $request){
        $name = $request->post();
        $name['bid'] = $this->_getBid();
        $result = ExpressModel::instance()->exinsert($name);
        if($result == 1){
            $this->success('添加成功', 'express/index');
        }else{
            $this->error('新增失败');
        }
    }
}
