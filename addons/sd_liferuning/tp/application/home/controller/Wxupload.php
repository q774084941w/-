<?php
namespace app\home\controller;
use think\Controller;
class Wxupload extends Controller{
    protected static $url='cloud.yeahc.cn/api.php';
    protected static $key='5464dfd652de0f32df7cf78e9965bb79';
    public function _initialize(){
        $result = $this->_getBid();
        if(!$result){
            $this->success('未登录', 'induserindex.ex/login');
        }
    }
    public function index(){
        if(request()->isPost()){
            
        }
            return view();
    }
    public function ajax(){
        if(request()->isAjax()){
            if(input('data')=='qrcode-login'){
                $url=self::$url.'/index/login';
                $data=[
                    'appid'=>'wx95955bed4db919db',
                    'version'=>'v1',
                    'url'=>$_SERVER['HTTP_HOST'],
                    'key'=>self::$key,
                    'uniacid'=>45
                ];
                $reuslt=self::request_post($url,$data);
                exit($reuslt);
            }
            if(input('data')=='check'){
                $url=self::$url.'/index/checklogin';
                $data=[
                    'appid'=>'wx95955bed4db919db',
                    'version'=>'v1',
                    'key'=>self::$key,
                ];
                $reuslt=self::request_post($url,$data);
        
                exit($reuslt);
            }
        }
    }
    public function uploadwx(){
        if(request()->isPost()){
            $data=input('post.');
            if(intval($data['versions'])<1){
                exit(json_encode(['code'=>0,'msg'=>'非法版本号']));
            }
            $arr=[
                'appid'=>'wx95955bed4db919db',
                'version'=>'v1',
                'key'=>self::$key,
            ];

            $url=self::$url.'/index/uploadweixin';
            $result=self::request_post($url,array_merge($data,$arr));
            echo $result;
        }
    }
    public static function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            return false;
        }

        $postUrl = $url;
        $curlPost = $param;
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0);//设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($ch);//运行curl
        curl_close($ch);

        return $data;
    }
}