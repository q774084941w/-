<?php
namespace app\api\controller;
use think\Db;
use think\Controller;
use think\Request;
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
class Index extends Controller
{
    public function store()
    {
        $acid = input('param.');
        if($acid){
            $bid  = Db::name('business')->where(['uniacid'=>$acid['_uniacid']])->field('bid')->find();
            echo json_encode(['bid' =>$bid]);
        }else{
            echo json_encode(['bid' =>'参数错误']);
        }
    }
    public function stores()
    {
        $acid = input('param.');
        if($acid){
            $bid  = Db::name('business')->where(['uniacid'=>$acid['_uniacid']])->field('distype')->find();
            echo json_encode(['bid' =>$bid['distype']]);
        }else{
            echo json_encode(['bid' =>'参数错误']);
        }
    }
    public function site()
    {
         $site = 'https://'.$_SERVER['HTTP_HOST'];
         $search = array(" ","　","\n","\r","\t");
         $replace = array("","","","","");
          echo json_encode(['site' =>$site]);

    }
    public function service()
    {
        $acid = input('param.');
        if($acid){
            $service = Db::name('service')->where(['bid'=>$acid['bid']])->field('name,pic,title')->select();
            //var_dump($service);die;
            foreach ($service as &$val){
                $val['pic'] = uploadpath('service',$val['pic']);
            }
            echo json_encode(['data' =>$service]);
        }else{
            echo json_encode(['data' =>'参数错误']);
        }
    }
//   分享宣传图
    public function adImg(){
        $bid = input('bid');
        $data = Db::name('run_rules')->where(['bid'=>$bid])->find();
//        这个函数时好时坏
//        $data['poster'] = uploadpath('poster',$data['poster']);
//        $data['homepage'] = uploadpath('homepage',$data['homepage']);
        $data['homepage'] = config('uploadPath') . 'homepage/'.$data['homepage'];
        $data['poster'] = config('uploadPath') . 'poster/'.$data['poster'];
        echo json_encode($data);
    }

    public function diy(Request $request){
        if($request->isPost()){
            $bid = input('post.bid');
            $arr = [
                'status' => false,
                'msg'    => '获取信息失败,非法参数',
                'diy'    => array()
            ];
            if($bid && is_numeric($bid)){
                $list = db('diy')->where('bid',$bid)->find();
                $arr['status']  = true;
                $arr['msg']     = '获取信息成功';
                if(!empty($list)){
                    $arr['diy'] = $list['diy'];
                }else{
                    $newList = db('diy')->where('bid',0)->find();
                    $arr['diy'] = $newList['diy'];
                }
            }
            echo json_encode($arr);
        }
    }
    public function diy_order(Request $request){
        if($request->isPost()){
            $bid = input('post.bid');
            $arr = [
                'status' => false,
                'msg'    => '获取信息失败,非法参数',
                'diy'    => array()
            ];
            if($bid && is_numeric($bid)){
                $list = db('diy_order')->where('bid',$bid)->find();
                $arr['status']  = true;
                $arr['msg']     = '获取信息成功';
                if(!empty($list)){
                    $arr['diy'] = $list['diy'];
                }else{
                    $newList = db('diy_order')->where('bid',0)->find();
                    $arr['diy'] = $newList['diy'];
                }
            }
            echo json_encode($arr);
        }
    }
//  请求是否区域限制
    public function areaLimit(){
        $bid = input('bid');
        if($bid){
            $limit = Db::name('business')->where('bid',$bid)->find()['area_limit'];
            echo json_encode(['limit' =>$limit]);
        }else{
            echo json_encode(['bid' =>'参数错误']);
        }
    }
}
