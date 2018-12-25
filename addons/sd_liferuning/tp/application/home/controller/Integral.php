<?php
namespace app\home\controller;

use app\home\model\FreightModel;
use app\home\model\IntegralModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;
use think\Cache;


class Integral extends Controller
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
        $bid = [
            'bid' => $this->_getBid(),
            'integral' => 1,
            'delete' => 0,
        ];
        $data = IntegralModel::instance()->show($bid);

        return view('integral/index',['data'=>$data]);
    }
    /**
     * 编辑
     * @return \think\response\View
     *
     */
    public function edit(Request $request)
    {
        $goodsid = $request->get('id');
        $bid = [
            'bid' => $this->_getBid(),
            'status' => 1
        ];
        $details = IntegralModel::instance()->intdetails($goodsid);

        $details['pic'] = uploadpath('goods',$details['pic']);

        if($details['template'] != 0){
            $freigh = FreightModel::instance()->showdata($details['template']);
            $details['freid'] = $freigh['freid'];
            $details['title'] = $freigh['title'];
        }else{
            $details['freid'] = '';
            $details['title'] = '';
        }
            $menus = IntegralModel::instance()->menus($bid);
            $details['template'] == 0 ? $details['aaxa'] = 0 : $details['aaxa'] = 1;
            $details['freight_unify'] == 0 ? $details['freight_unify'] = '' : $details['freight_unify'] = $details['freight_unify'];

            return view('integral/edit',['menus'=>$menus,'data'=>$details]);

    }
    /**
     * 详情
     */
    public function show(Request $request){
        $id = $request->get('id');
        $details = IntegralModel::instance()->intdetails($id);

        $details['pics'] ? $details['pics'] = explode(',',$details['pics']) : $details['pics'] = [];
        $details['pic'] = uploadpath('goods',$details['pic']);
        $details['freight_count'] == 0 ? $details['freight_count'] = '重量' : $details['freight_count'] = '件数';
        $details['shelves_time'] = date('Y-m-d H:i:s',$details['shelves_time']);;
        $details['createtime'] = date('Y-m-d H:i:s',$details['createtime']);;
        $details['updatetime'] == 0 ? $details['updatetime'] = '' : $details['freight_count'] = date('Y-m-d H:i:s',$details['freight_count']);
        foreach ($details['pics'] as &$val){
            $val = uploadpath('goods',$val);
        }
        return view('integral/show',['data'=>$details]);
    }


    /**
     * 商品添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        $bid = [
            'bid' => $this->_getBid(),
            'status' => 1,
        ];
        $bida = [
            'bid' => $this->_getBid(),
            'status' => 1,
            'type' =>1
        ];

            $menus = IntegralModel::instance()->menus($bid);
            $freight = IntegralModel::instance()->freight($bida);
            return view('integral/add',['menus'=>$menus,'freight'=>$freight]);
    }
    /**
     * 商品添加
     * @return \think\response\View
     *
     */
    public function templateajax()
    {

        $bida = [
            'bid' => $this->_getBid(),
            'status' => 1,
            'type' =>1
        ];

        $freight = IntegralModel::instance()->freight($bida);

        return $freight;
    }
    

    /**
     * 添加数据库
     * @    param Request $request
     */
    public function insert(Request $request){
        header('Content-type:text/html;charset=utf-8');
        $data = $request->post();
        
        $pic = CommonModel::instance()->upload('goods');
        $data['pic'] = $pic;
        $data['bid'] = $this->_getBid();
        $imgs = [];
        
        foreach (explode('_)(_',$data['imgurl']) as $base64_image_content){
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
                $type = $result[2];
                $new_file = "uploads/goods/".date('Ym',time())."/";
                if(!file_exists($new_file))
                {
                    mkdir($new_file, 0700);
                }
                $savename = date('Ymd') . randCode(6) . str_replace('.','',microtime(true));
                $new_file = $new_file.$savename.".{$type}";
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                    $imgs[] = $savename.".$type";
                }else{
                    $imgs[] = '';
                }
            }

        }
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
        $imgs = implode(',',$imgs);

        $result = IntegralModel::instance()->addgoods($data,$imgs);
        if($result > 0){
            $this->success('添加成功', 'integral/index');
        }else{
            $this->error('新增失败');
        }
        
    }
    /**
     * 添加数据库
     * @    param Request $request
     */
    public function editinsert(Request $request){
        header('Content-type:text/html;charset=utf-8');
        $data = $request->post();

        $pic = CommonModel::instance()->upload('goods');
        if($pic != 0) $data['pic'] = $pic;

        $data['bid'] = $this->_getBid();
        $imgs = [];
        foreach (explode('_-_',$data['imgurl']) as $base64_image_content){
            if(substr($base64_image_content,0,4) == 'http'){
                $imgs[] = substr(strstr($base64_image_content,'//20'),9);
                unset($base64_image_content);
            }else {
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
                    $type = $result[2];
                    $new_file = "uploads/goods/" . date('Ym', time()) . "/";
                    if (!file_exists($new_file)) {
                        mkdir($new_file, 0700);
                    }
                    $savename = date('Ymd') . randCode(6) . str_replace('.', '', microtime(true));
                    $new_file = $new_file . $savename . ".{$type}";
                    if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                        $imgs[] = $savename . ".$type";
                    } else {
                        $imgs[] = '';
                    }
                }
            }
        }
       

        $imgs = implode(',',$imgs);
        $result = IntegralModel::instance()->editinsert($data,$imgs);
        if($result > 0){
            $this->success('添加成功', 'integral/index');
        }else{
            $this->error('新增失败');
        }

    }
    /**
     *
     * 上下架
     */
    public function soldOut(Request $request){
        $type = $request->post();
        $result = IntegralModel::instance()->soldOut($type);
        return $result;
    }
    /**
     * 编辑图片
     */
    public function editajax(Request $request){
        $id = $request->get('id');

        $result = IntegralModel::instance()->editajax($id);
        return $result;
    }
    /**
     * 改库存
     */
    public function inventory(Request $request){
        $data = $request->get();
        $result = IntegralModel::instance()->inventory($data);
        return $result;
    }
    /**
     * 搜索
     */
    public function goodssearch(Request $request){
        $data = $request->get();
        $bid = $this->_getBid();
        $result = IntegralModel::instance()->goodssearch($data,$bid);
        return view('integral/index',['data'=>$result]);
    }
    /**
     * 删除
     */
    public function delete(Request $request){
        $banid = $request->get('id');
        $result = IntegralModel::instance()->deleteban($banid);
        return $result;
    }






}
