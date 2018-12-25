<?php
namespace app\api\controller;

use app\api\model\CartModel;
use think\Controller;
use think\Request;

class Classlist extends Controller{
    /**
     * 服务分类
     */
    public function clist(){
        $bid = input('bid');
        $list = db('class')->where('bid',$bid)
            ->field('cid,name,moban')
            ->order('paixu asc')
            ->select();
        foreach ($list as $k => $v ){
            $list[$k]['child'] = db('service s')
                ->field('s.*,c.moban')
                ->join('class c','s.cid=c.cid','left')
                ->where(['s.cid'=>$v['cid'],'s.bid'=>$bid])->select();
            foreach ($list[$k]['child'] as $key=>$val){
                $list[$k]['child'][$key]['pic'] = uploadpath('service',$val['pic']);
            }
        }
        return json_encode($list);
    }



}