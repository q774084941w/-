<?php
namespace app\index\controller\home;

use app\index\model\CommonModel;
use app\index\model\CommTypeModel;
use app\index\model\GoodsModel;
use think\Controller;
use think\Request;
use think\Cache;


class CommType extends Controller
{
    /**
     * 首页展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $data = CommTypeModel::instance()->select();
        return view('home/comm_type/index',['data'=>$data]);
    }

    /**
     * 菜单添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        $data = CommTypeModel::instance()->add();
        return view('home/comm_type/add',['data'=>json_encode($data)]);
    }

    /**
     * 三级分类 ajax 绝对路径url
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function addajax()
    {
        $cache = Cache::get('nameajax','');
        if($cache){
            return $cache;
        }else{
            $data = CommTypeModel::instance()->add();
            Cache::set('nameajax',$data,3600);
            return $data;
        }

    }

    /**
     * ajax 运费计算   模板选择
     * @return false|mixed|\PDOStatement|string|\think\Collection
     */
    public function templateajax()
    {
        $cache = Cache::get(' templateajax','');
        if($cache){
            return $cache;
        }else{
            $data = GoodsModel::instance()->freight();
            Cache::set('templateajax',$data,3600);
            return $data;
        }

    }

    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){
        if(Cache::get('nameajax',''))Cache::rm('nameajax'); ;
        $data = $request->post();
        $list = CommTypeModel::instance()->upload($data);
        if($list){
            echo '添加成功';
        }else{
            echo '添加失败';
        }
        
    }
    /**
     * api 下级菜单
     */
    public function twomenus(Request $request){
        $bid = $request->get();
        if(empty($bid['bid'])) $this->outPut(null,1001,"缺少商家id");
        if(empty($bid['tid'])) $this->outPut(null,1001,"缺少菜单id");
        $field = 'ptid,bid,level,name,pid,status,pic,nid,solt,tid';
        $result = CommTypeModel::instance()->twomenus($bid,$field,'solt desc');
        foreach ($result as &$val){
            $val['pic'] = uploadpath('commtype',$val['pic']);
        }
        $this->jsonOut($result);
    }
    
}
