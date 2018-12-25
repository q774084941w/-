<?php
namespace app\index\controller\home;

use app\index\model\CommonModel;
use app\index\model\FreightModel;
use app\index\model\MenusModel;
use think\Controller;
use think\Request;

class Freight extends Controller
{
    /**
     * 菜单展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $data = FreightModel::instance()->show();

        return view('home/freight/index',['data'=>$data]);
    }

    /**
     * 菜单添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        return view('home/freight/add');
    }

    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){

        $data = $request->post();
        //var_dump($data);exit;
        if($data['city']){
            $data['createtime'] = time();
            FreightModel::instance()->insert($data);
            echo '添加成功';
        }else{
            echo '未选择配送地址';
        }


    }
    
}
