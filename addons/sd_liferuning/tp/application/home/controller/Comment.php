<?php
namespace app\home\controller;

use app\home\model\CommentModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;
use think\Cache;


class Comment extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 评论列表
     * @return \think\response\View
     *
     */
    public function index(){
        $bid = $this->_getBid();
        $result = CommentModel::instance()->commentlist($bid);
        foreach($result as $k=>$val){
            $data = [];
            $data = $val;
            if($val['isshow'] == 0){
                $data['isshow'] = '显示';
            }else{
                $data['isshow'] = '不显示';
            }

            $data['status'] = '已关闭';
            $val['createtime'] == 0 ? $data['createtime'] = '' :$data['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $result->offsetSet($k,$data);
        }
    
        return view('comment/index',['data'=>$result]);
    }


    /**
     * 回复评论
     * @return \think\response\View
     *
    */
    public function add(Request $request)
    {
        $id = $request->get('id');
        $result = CommentModel::instance()->adddata($id);
        $result['createtime'] = date('Y年m月d日 H:i:s',$result['createtime']);
        !empty($result['pics']) ? $result['pics'] = explode(',',$result['pics']) : $result['pics'] = [];
        return view('comment/add',['data'=>$result]);
    }

    /**
     * 添加数据库
     * @return \think\response\View
     *
     */
    public function insert(Request $request)
    {
        $data = $request->post();
        $data['replytime'] = time();
        $result = CommentModel::instance()->updatecomm($data);
        if($result == 1){
            $this->success('添加成功', 'comment/index');
        }else{
            $this->error('新增失败');
        }
    }

    /**
     * 评论显示
     * @return \think\response\View
     *
     */
    public function show(Request $request)
    {
        $id = $request->get('id');
        $result = CommentModel::instance()->showdata($id);
        $result['createtime'] = date('Y-m-d H:i:s',$result['createtime']);
        $result['pics'] ? $result['pics'] = explode(',',$result['pics']) : $result['pics'] = [];
        $result['reply_pics'] ? $result['reply_pics'] = explode('_-_',$result['reply_pics']) : $result['reply_pics'] = [];
        foreach ($result['pics'] as &$val){
            $val = uploadpath('goods',$val);
        }
        foreach ($result['reply_pics'] as &$val){
            $val = uploadpath('goods',$val);
        }
        return view('comment/show',['data'=>$result]);
    }



        
}
