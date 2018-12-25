<?php
namespace app\home\controller;

use app\home\model\HzpnewsModel;
use think\Controller;
use think\Request;

class Hzpnews extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
   /**
    * 新闻类
    * 列表
    */
    public function index(){
        $bid = $this->_getBid();
        $result = HzpnewsModel::instance()->newslist($bid);
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $v['updatetime'] == 0 ? $data['updatetime'] = '' :$data['updatetime'] = date('Y-m-d H:i:s',$v['updatetime']);
            $result->offsetSet($k,$data);
        }
        return view('hzpnews/index',['data'=>$result]);

    }
    /**
     * 详情
     */
    public function show(Request $request){
        $id = $request->get('id');
        $details = HzpnewsModel::instance()->details($id);
        $details['createtime'] = date('Y-m-d H:i:s',$details['createtime']);
        $details['updatetime'] == 0 ? $details['updatetime'] = '' : $details['updatetime'] = date('Y-m-d H:i:s',$details['updatetime']);
        return view('hzpnews/show',['data'=>$details]);
    }
    /**
     * 新闻添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        return view('hzpnews/add');
    }
    /**
     * 添加数据库
     */
    public function insert(Request $request){
        $data = $request->post();
        $data['createtime'] = time();
        $data['bid'] = $this->_getBid();
        $result = HzpnewsModel::instance()->newsinsert($data);
        if($result > 0){
            $this->success('添加成功', 'hzpnews/index');
        }else{
            $this->error('新增失败');
        }
    }

    /**
     * 编辑
     */
    public function edit(Request $request){
        $id = $request->get('id');
        $result = HzpnewsModel::instance()->details($id);
        return view('hzpnews/edit',['data'=>$result]);
    }
    /**
     * 编辑 数据库操作
     */
    public function editinsert(Request $request){
        $data = $request->post();
        $data['updatetime'] = time();
        $result = HzpnewsModel::instance()->newsedit($data);
        if($result > 0){
            $this->success('编辑成功', 'hzpnews/index');
        }else{
            $this->error('编辑失败');
        }
    }
    /**
     * 删除
     */
    public function delete(Request $request){
        $id = $request->get('id');
        $result = HzpnewsModel::instance()->newsdelete($id);
        return $result;
    }
}
