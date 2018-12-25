<?php
namespace app\api\controller;

use app\api\model\MenusModel;
use think\Controller;
use think\Request;

class Menus extends Controller
{

    /**
     * api 首页菜单
     */
    public function menus(Request $request){
        $bid = $request->get('bid');
        if(empty($bid)) $this->outPut(null,1001,"缺少商家id");
        $fieid = 'tid,bid,name,logo,solt,status';
        $result = MenusModel::instance()->menus(['bid'=>$bid,'status'=>1],$fieid,'solt desc');
        foreach ($result as &$val){
            $val['logo'] = uploadpath('commtype',$val['logo']);
        }
        $this->jsonOut($result);

    }
    
    
}
