<?php

namespace app\home\controller;

use app\home\model\IndexModel;

use think\Controller;

use think\Request;

use think\Session;

use think\Db;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Origin, No-Cache, X-Requested-With, If-Modified-Since, Pragma, Last-Modified, Cache-Control, Expires, Content-Type, X-E4M-With");
class Index extends Controller

{

    /**

     * 登录

     */

    public function _iniaialize(Request $request = null)

    {
    }

    public function test(){

        $file=file_get_contents(ROOT_PATH.'/wxlog.txt');

        print_r(json_decode($file));

    }

    public function login(){

        global $_W;

        echo   header('Location:'.'https://'.$_SERVER['HTTP_HOST']);

    }

    /**

     * 获取token

     */

    public function stati(){

        if (session('access_token') && session('expire_time') > time()) {



            return session('access_token');

        } else {

            $result = $this->_getBid();

            $ress = Db::name('business')->where('bid', $result)->find();

            $appid = $ress['appid'];

            $appsecret = $ress['secret'];

            $session_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;

            $res = file_get_contents ( $session_url );

            $ress = json_decode($res);

            if(empty($ress)){

                $res = file_get_contents ( $session_url );

                $ress = json_decode($res);

                $access_token = $ress->access_token;

                session('access_token',$access_token);

                echo 1;

                session('expire_time',time() + 3600);

                return $access_token;

            }

            $access_token = $ress->access_token;

            //将重新获取到的access_token存到session里

            session('access_token',$access_token);

            echo 1;

            session('expire_time',time() + 3600);

            return $access_token;

        }

    }

    public function WeChatprogram($url,$data){

        $datas = json_encode($data);

        $options = array(

            'http' => array(

                'method' => 'post',

                'header' => 'Content-type:application/x-www-form-urlencoded',

                'content' =>$datas,

                'timeout' => 15 * 60 // 超时时间（单位:s）

            )

        );

        $context = stream_context_create($options);

        $result = file_get_contents($url, false, $context);

        //return $result;

        $res = json_decode($result);

        return $res;

    }

    /**

     * 登录检测

     */

    public function checkLogin(){

        echo   header('Location:'.'https://'.$_SERVER['HTTP_HOST']);

    }

    /**

     * 退出登录

     */

    public function quitLogin(){

        Session::delete('bus_bid');

        Session::delete('name');

        $this->success('退出成功', 'index/login');

    }

    /**

     * 概况分析

     * @param

     */

    public function index(){

        global $_W;

        $UserSession = Session::get('we7_account');

        if(empty($UserSession))

            header('Location:'.'https://'.$_SERVER['HTTP_HOST']);

        else

            $data = [

                'appid' => $UserSession['key'],

                'secret' => $UserSession['secret'],

                'uniacid' =>$UserSession['uniacid']

            ];

        //$results = IndexModel::instance()->checkLogin($data);

        $result = IndexModel::instance()->insertWei($data,$UserSession['uniacid']);

        Session::set('bus_bid',$result['bid']);

        //var_dump($result);die;

        $result = $this->_getBid();

        if(!$result){

            $this->success('未登录', 'index/login');

        }

        $token = $this->stati();

        $url = 'https://api.weixin.qq.com/datacube/getweanalysisappiddailysummarytrend?access_token='.$token;

        $data =  [  'begin_date' =>date("Y-m-d",strtotime("-1 day")),'end_date' =>date("Y-m-d",strtotime("-1 day"))];

        $datas = json_encode($data);

        $options = array(

            'http' => array(

                'method' => 'post',

                'header' => 'Content-type:application/x-www-form-urlencoded',

                'content' =>$datas,

                'timeout' => 15 * 60 // 超时时间（单位:s）

            )

        );

        $context = stream_context_create($options);

        $results = file_get_contents($url, false, $context);

        //return $result;

        $res = json_decode($results);

        if(empty($res->list)){

            return view('index/index',['stitc' => $res->list]);

        }

        return view('index/index',['stitc' => $res->list[0]]);

    }



    /**

     * 用户分析

     * @param

     */

    public function user(){

        $result = $this->_getBid();

        if(!$result){

            $this->success('未登录', 'index/login');

        }

        //昨日

        $token = $this->stati();

        $url = 'https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token='.$token;

        $data = [  'begin_date' =>date("Y-m-d",strtotime("-1 day")),'end_date' =>date("Y-m-d",strtotime("-1 day"))];

        $res = $this->WeChatprogram($url,$data);

        if(empty($res->list)){

            return view('index/user',['stitc' => $res->list]);

        }

        if($ress = db('yestoday')->where('times', $res->list[0]->ref_date)->find()){

            $this->assign('stitc',$res->list[0]);

            $this->fetch('index/user');



        }else{

            db('yestoday')->insert([

                'times'=>$res->list[0]->ref_date,          //时间

                'yestody'=>$res->list[0]->visit_uv_new,  //新用户

                'opens'=>$res->list[0]->session_cnt,     //打开次数

                'visit_uv'=>$res->list[0]->visit_uv,       //访问人数

                'stay_time_uv'=>$res->list[0]->stay_time_uv,         //停留时间

                'visit_pv'=>$res->list[0]->visit_pv,   //访问次数

            ]);

        }

        $this->assign('stitc',$res->list[0]);

        return view('index/user');

    }



    /**

     * 访问分析

     * @param

     */

    public function visit(){

        return view('home/statistics/visit');

    }



    public function stics(){

        $data =  db('stitc')->limit(5)->field('times,open')->order('id desc')->select();

        echo json_encode(['res'=>$data]);

    }

    public function userStics(){

        $data =  db('yestoday')->limit(5)->field('times,visit_uv')->order('id desc')->select();

        echo json_encode(['res'=>$data]);

    }



    /**

     * 清理缓存

     */

    public function clearCache(){



        if(cache(NUll)){

            delDirAndFile(ROOT_PATH.'runtime/cache');

            delDirAndFile(ROOT_PATH.'runtime/log');

            delDirAndFile(ROOT_PATH.'runtime/temp');



            exit(json_encode(['code'=>1,'msg'=>'清理成功']));

        }else{

            exit(json_encode(['code'=>0,'msg'=>'清理失败']));

        }

    }
    /*
     * 首页自定义
     */

    public function TempletManage(){
        return view('TempletManage');
    }
    public function createApi( Request $request)
    {
        if($request->isPost()){
            $data = input('post.');
            $bid = $this->_getBid();
            $list = db('diy')->where('bid',$bid)->find();
            if(!empty($list)){
                $res = Db::table('135k_diy')->where('bid', $bid)
                    ->update([
                        'diy'  =>json_encode($data),
                        'update_time' => date('Y-m-d H:i:s',time()),
                    ]);
            }else{
                $data = ['bid'=>$bid,'diy' => json_encode($data),'update_time'=>date('Y-m-d H:i:s',time())];
                $res = Db::name('diy')->insertGetId($data);
            }
            if($res){
                return json_encode($data);
            }else{
                return false;
            }
        }
    }


    public function diy(){
        $bid = $this->_getBid();
        $list = db('diy')->where('bid',$bid)->find();
        if(!empty($list)){
            return json_encode([
                'status' => true,
                'diy'    => $list['diy']
            ]);
        }else{
            //$newList = db('diy')->where('bid',0)->find();
            return json_encode([
                'status' => false,
                'diy'    => ''
            ]);
        }
    }

    public function img(Request $request){
        if($request->isPost()){
            $data = input('post.');
            $imgUrl = array();
            foreach($data['images'] as $file){
                $url = $this->uploadOne($file);
                if($url){
                    $imgUrl[] = $url;
                }
            }
            if(count($imgUrl) > 0){

                $bid = $this->_getBid();
                $arr = array();
                foreach($imgUrl as $k=> $v){
                    $arr[$k]['bid'] = $bid;
                    $arr[$k]['url'] = $v;
                }
                Db::name('diy_img')->insertAll($arr);

                return json_encode([
                    'status'  => 1,
                    'msg'     => '上传图片成功',
                    'img'     => $imgUrl
                ]);
            }else{
                return json_encode([
                    'status'  => 0,
                    'msg'     => '上传图片失败',
                    'img'     => $imgUrl
                ]);
            }
        }
    }


    public function getImg(){
        $bid = $this->_getBid();
        $list = Db::table('135k_diy_img')->where('bid',$bid)->order('id desc')->column('url');

        if(!empty($list)){
            return json_encode([
                'status' => true,
                'url'    => $list
            ]);
        }else{
            return json_encode([
                'status' => false,
                'url'    => array()
            ]);
        }
    }

    public function getUrl(){
        return json_encode([
            'status' => true,
            'info'    => [
                [
                    'type'  => 1,
                    'name'  => '帮我送',
                    'url'   => '/sd_liferuning/pages/constmer/largess/index'
                ],
                [
                    'type'  => 2,
                    'name'  => '帮我买',
                    'url'   => '/sd_liferuning/pages/constmer/buy/index'
                ],
                [
                    'type'  => 3,
                    'name'  => '代排队',
                    'url'   => '/sd_liferuning/pages/constmer/line-up/index'
                ],
                [
                    'type'  => 4,
                    'name'  => '代办',
                    'url'   => '/sd_liferuning/pages/constmer/transact/index'
                ],
                [
                    'type'  => 5,
                    'name'  => '家政',
                    'url'   => '/sd_liferuning/pages/constmer/housekeeping/index'
                ],
                [
                    'type'  => 6,
                    'name'  => '代驾',
                    'url'   => '/sd_liferuning/pages/constmer/driving/index'
                ],
               [
                    'type'  => 7,
                    'name'  => '帮我取',
                    'url'   => '/sd_liferuning/pages/constmer/take-things/index'
                ]

            ]
        ]);
    }
    /**
     * 小程序订单自定义设置
     */
    public function OrderTempletManage(){

        return view('OrderTempletManage');
    }
    public function createApiOrder( Request $request)
    {
        if($request->isPost()){
            $data = input('post.');
            $bid = $this->_getBid();
            $list = db('diy_order')->where('bid',$bid)->find();
            if(!empty($list)){
                $res = Db::table('135k_diy_order')->where('bid', $bid)
                    ->update([
                        'diy'  =>json_encode($data),
                        'update_time' => date('Y-m-d H:i:s',time()),
                    ]);
            }else{
                $data = ['bid'=>$bid,'diy' => json_encode($data),'update_time'=>date('Y-m-d H:i:s',time())];
                $res = Db::name('diy_order')->insertGetId($data);
            }
            if($res){
                return true;
            }else{
                return false;
            }
        }
    }
    public function diy_order(){
        $bid = $this->_getBid();
        $list = db('diy_order')->where('bid',$bid)->find();
        if(!empty($list)){
            return json_encode([
                'status' => true,
                'diy'    => $list['diy']
            ]);
        }else{
            //$newList = db('diy_order')->where('bid',0)->find();
            return json_encode([
                'status' => false,
                'diy'    => ''
            ]);
        }

    }
    /**
     * @return string
     *调取协议
     */
    public function protocol(){
        $bid = $this->_getBid();
        $proList = db('Clause')
            ->field('title,type')
            ->where('bid',$bid)
            ->select();
        if(!empty($proList)){
            $arr = [];
            foreach ($proList as $key => $val){
                $v = [
                    'id'=>$val['type'],
                    'title'=>$val['title']
                ];
                array_push($arr,$v);
            }
            return json_encode($arr);
        }else{
            return json_encode([
                'id'=>'',
                'title'=>''
            ]);
        }
    }

    public function uploadOne($file)
    {

        header('Content-type:text/html;charset=utf-8');
        $base64_image_content = trim($file);
        //正则匹配出图片的格式
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];//图片后缀
            $urlpath='/diy/'.date('Ymd').'/';
            $uploadpath='/public/uploads'.$urlpath;
            $new_file = ROOT_PATH.$uploadpath;
            if (!file_exists($new_file)) {
                //检查是否有该文件夹，如果没有就创建，并给予最高权限
                mkdir($new_file, 0700);
            }
            $filename =  md5(time().rand(100,999)).".{$type}";
            $new_file = $new_file . $filename;
            //写入操作
            if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                return  config('uploadPath').$urlpath.$filename;  //返回文件名及路径
            } else {
                return false;
            }
        }
    }
    public function delsrc($src){
        $imgpath=explode('sd_liferuning/tp/',$src);
        if(empty($imgpath[1]))exit(json_encode(['code'=>0]));
        $list = Db::table('135k_diy_img')->where('url',$src)->delete();
        if($list){
            unlink(ROOT_PATH.$imgpath[1]);
            exit(json_encode(['code'=>1]));
        }else{
            exit(json_encode(['code'=>0]));
        }
    }
}

