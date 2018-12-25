<?php
namespace app\index\controller\home;

use app\index\model\CommonModel;
use app\index\model\MenusModel;
use think\Controller;
use think\Request;

class Menus extends Controller
{
    /**
     * 菜单展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $data = MenusModel::instance()->show();
        
        return view('home/menus/index',['data'=>$data]);
    }

    /**
     * 菜单添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        return view('home/menus/add');
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

    /**
     * api 首页菜单
     */
    public function menus(Request $request){
        $bid = $request->get('bid');
        if(empty($bid)) $this->outPut(null,1001,"缺少商家id");
        $fieid = 'tid,bid,name,logo,solt,status';
        $result = MenusModel::instance()->menus(['bid'=>$bid,'status'=>1],$fieid,'solt desc');
        foreach ($result as &$val){
            $val['logo'] = uploadpath('menus',$val['logo']);
        }
        $this->jsonOut($result);

    }
    
    
}
