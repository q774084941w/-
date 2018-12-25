<?php
namespace app\home\controller;

use app\home\model\CommonModel;
use app\home\model\MenusModel;
use think\Controller;
use think\Request;

class Menus extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 菜单展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $data = MenusModel::instance()->show();
        
        return view('menus/index',['data'=>$data]);
    }

    /**
     * 菜单添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        return view('menus/add');
    }

    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){
        $pic = CommonModel::instance()->upload('menus');
        $data = $request->post();
        if($pic){
            $data['logo'] = $pic;
            $data['createtime'] = time();
            MenusModel::instance()->insert($data);
            echo '添加成功';
        }else{
            echo '图片为上传';
        }


    }
    
    
    
}
