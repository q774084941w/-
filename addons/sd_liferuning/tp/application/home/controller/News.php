<?php
namespace app\home\controller;

use app\home\model\NewModel;
use think\Controller;
use think\Request;
use think\Cache;


class News extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 首页展示
     * @return \think\response\View
     *
     */
    public function index(){

        return view('news/index');
    }
    /**
     * 首页展示
     * @return \think\response\View
     *
     */
    public function edit()
    {


        return view('news/edit');
    }


    /**
 * 商品添加
 * @return \think\response\View
 *
 */
    public function add()
    {

        return view('news/add');
    }
    /**
     * 商品添加
     * @return \think\response\View
     *
     */
    public function show()
    {

        return view('news/show');
    }

    /**
     * 添加数据库
     * @    param Request $request
     */
    public function insert(Request $request){

        $data = $request->post();
        //print_r($data);exit;
        if($data['caidan']){
            $maxmenu = explode(',',$data['caidan']);
            foreach ($maxmenu as $key=>&$val){
                $minmunu = explode('/*max*/',$val);
                foreach ($minmunu as $k=>&$v){
                    $list[$key][] = $v;
                }
            }
            $data['caidan'] = $list;
        }
        GoodsModel::instance()->addgoods($data);
        echo '添加成功';
        
    }


        
}
