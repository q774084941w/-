<?php
namespace app\home\controller;

use app\home\model\CommtypeModel;
use app\home\model\GoodsModel;
use app\home\model\CommonModel;
use app\home\model\MenusModel;
use think\Controller;
use think\Request;
use think\Cache;


class Commtype extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 首页展示
     * @return \think\response\View
     *
     */

    public function index()
    {
        return view('comm_type/index');
    }

    public function ajaxindex()
    {
        $bid = $this->_getBid();
        $data = CommtypeModel::instance()->commindex($bid);

        return $data;
    }

  
    /**
     * 添加
     */
    public function add()
    {
        return view('comm_type/add');
    }

    /**
     * AJAX添加数据
     *
     */
    public function addaxax()
    {
     
        $bid = $this->_getBid();
        $data = CommtypeModel::instance()->add($bid);
        
        return $data;
    }
    /**
     * ajax 运费计算   模板选择
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function templateajax()
    {
        $data = GoodsModel::instance()->freight($this->_getBid());
        return $data;
    }

//    /**
//     * 添加数据库
//     * @param Request $request
//     */
//    public function insert(Request $request){
//        if(Cache::get('nameajax',''))Cache::rm('nameajax'); ;
//        $data = $request->post();
//        $list = CommtypeModel::instance()->upload($data);
//        if($list){
//            echo '添加成功';
//        }else{
//            echo '添加失败';
//        }
//
//    }
    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){
        $data = $request->post();
        $pic = CommonModel::instance()->upload('commtype');
        $list['bid'] = $this->_getBid();
        $list['name'] = $data['txt'];
        $list['solt'] = $data['solt'];
        $list['createtime'] = time();
        $list['updatetime'] = time();
        if (!$data['class_one']){
            $list['logo'] = $pic;
            $result = MenusModel::instance()->insert($list);
        }elseif($data['class_one']  && !$data['class_two']){
            $list['pic'] = $pic;
            $list['tid'] = $data['class_one'];
            $result = CommtypeModel::instance()->insert($list);
        }elseif ($data['class_one']  && $data['class_two']){
            $list['pic'] = $pic;
            $list['tid'] = $data['class_one'];
            $list['pid'] = $data['class_two'];
            $list['level'] = 1;
            $result = CommtypeModel::instance()->insert($list);
        }
        if($result){
            $this->success('添加成功', 'commtype/index');
        }else{
            $this->error('新增失败');
        }


    }
    /**
     * 开启/关闭
     */
    public function soldOut(Request $request){
        $data = $request->get();
      
        if(isset($data['ptid'])){
            $result = CommtypeModel::instance()->soldOut($data);
        }else{
            
            $result = CommtypeModel::instance()->handle($data,$this->_getBid());
        }
        return $result;
    }
    /**
     * 编辑
     */
    public function edit(Request $request){
        $id = $request->get();
        if($id['v'] == 1){
            $result = CommtypeModel::instance()->edit($id);
        }else{
            $result = MenusModel::instance()->edit($id);
        }
        $result['v'] = $id['v'];
        return view('comm_type/edit',['data'=>$result]);
    }
    /**
     * 编辑添加数据库
     */
    public function editinse(Request $request){
        $data = $request->post();
        
        $pic = CommonModel::instance()->upload('commtype');

        if(isset($data['ptid'])){
            if($pic != 0){
                $list['pic'] = $pic;
            }
            $list['ptid'] = $data['ptid'];
            $list['name'] = $data['name'];

            $list['updatetime'] = time();
            
            $result = CommtypeModel::instance()->editinse($list);
        }else{
            if($pic != 0){
                $list['logo'] = $pic;
            }
            $list['name'] = $data['name'];
            $list['name'] = $data['name'];
            $list['tid'] = $data['tid'];
            $list['updatetime'] = time();
            $result = MenusModel::instance()->editinse($list);
        }
        if($result == 1){
            $this->success('编辑成功', 'commtype/index');
        }else{
            $this->error('编辑失败');
        }
    }
    /**
     * 编辑排序
     */
    public function editsolt(Request $request){
        $data = $request->get();
        $result = CommtypeModel::instance()->editsolt($data);
        return $result;
    }
    /**
     * 三级分类 ajax 绝对路径url
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function addajax(Request $request)
    {
        $id = $request->get('id');
        $data = CommtypeModel::instance()->addajax($this->_getBid(),$id);
        return $data;
    }
   

    
}
