<?php
namespace app\api\controller;

use app\api\model\CommtypeModel;
use think\Controller;
use think\Request;


class Commtype extends Controller
{
    /**
     * api 下级菜单
     */
    public function twomenus(Request $request){
        $bid = $request->get();
        if(empty($bid['bid'])) $this->outPut(null,1001,"缺少商家id");
        if(empty($bid['tid'])) $this->outPut(null,1001,"缺少菜单id");
        $field = 'ptid,bid,level,name,pid,status,pic,nid,solt,tid';
        $result = CommtypeModel::instance()->twomenus($bid,$field,'solt desc');
     
        $this->jsonOut($result);
    }
    
}
