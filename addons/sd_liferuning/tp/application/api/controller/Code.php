<?php
namespace app\api\Controller;

use think\Controller;
use think\Db;
//use think\Model;
use think\Request;
//use app\api\model\CodeModel;
class Code extends Controller{ 

    public function createCode(Request $request){
        $this->_isPOST();
        $get_data = $request->post();
        $return_arr = array(
        "msg" => "操作失败",
        "code" => -1,
    );
        //$get_data = file_get_contents("php://input");//小程序post方法,只能用流来接收
        //$get_data_arr = json_decode($get_data, true);
        //var_dump($get_data);   
        //模型
        //$user_model = User_Model::load_model();
        //$wxapp_model = WxApp_Model::load_model();
        //传递过来的用户信息

        $seller_id = $get_data['seller_id'];
        //$type = $get_data_arr['type'];
        //$use_id = $user_model->get_session();
     //  echo 111;
        if(!$seller_id){
            $return_arr['msg'] = "没有找到用户信息";
            echo json_encode($return_arr);
            return ;
        }
        //先查看文件夹是否已经有该店铺二维码

           /* if(is_file(PUBLIC_PATH.'upload/goods/qcode/**+++--'.$seller_id.'+++--qc.jpg')){

                $return_arr['msg'] = '二维码已经存在';
                $return_arr['qcUrl'][] = 'http://pub.com/upload/goods/qcode/**+++-- 

'.$seller_id.'+++--qc.jpg';
                $return_arr['code'] = 0;


            }else{*/
                $res = $this->getQcode($seller_id);
                //var_dump($res);
                $res = file_put_contents(ROOT_PATH.'public/upload/goods/qcode/***+++--'.$seller_id.'+++--qc.jpg',$res);
                
                if($res){
                    $return_arr['res']=urldecode($res);
                    $return_arr['msg'] = '二维码成功';
                    $return_arr['qcUrl'][] = 'https://135k.zijunxcx.cn/upload/goods/qcode/***+++--'.$seller_id.'+++--qc.jpg';
                    $return_arr['code'] = 0;
                }
           /* }*/
        echo json_encode($return_arr);

    }
     public function getQcode($seller_id){  
        $appId="wxa834360f88210bea";
        $appSecret="28bfcfcdb0bd24cb49d936d9402dc750";
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appId."&secret=".$appSecret;
                //2、初始化
        //$token=$this->http_curl($url);
        $access_token = $this->http_curl($url);
       // var_dump($access_token);
        //$seller_id="64";
        $url ='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$access_token['access_token'];
        $data_arr = array(//店铺
                "scene"=> "code?seller_id=".$seller_id,
                "width"=> 430
        );
        $data_arr1=json_encode($data_arr);
       // var_dump($data_arr1);
        $res = $this->post_http($url,$data_arr1);
        //var_dump($res);
        if($res) {
            return $res;
        }
            return false;
    }
    function post_http($url,$data){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
        curl_close($ch);
        //打印获得的数据
        return  $output;
    }
     function http_curl($url){
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
        $res=json_decode(curl_exec($ch),true);
        curl_close($ch);
       return  $res;
    }
    public function getid(){
        $id = input('param.');
        //查寻这个扫码用户的id是否有f_id
       //var_dump($id['ids']['seller_id']);
        $data = Db::name('user')->field('f_id,regtime')->where('uid',$id['uid'])->find();
        // echo 'ids是二维码id，uid是自己的id,$data[regtime]是自己的注册时间，$data1[regtime]是扫描得到的二维码注册时间';
        // echo "65的注册时间小于66的注册时间";
        // var_Dump($data);
        //查询扫码获得的id
        $data1= Db::name('user')->field('f_id,regtime')->where('uid',$id['ids'])->find();
        // var_dump($data1);
        if($data['f_id']==0 &&($data['regtime']>$data1['regtime'])){
                $data2=Db::name('user')->where('uid',$id['uid'])->update(['f_id'=>$id['ids']]);
                if($data2){
                $result['code']=1;
                echo json_encode($result);
                return;
            }
        }else{
            $result['code']=2;
            echo json_encode($result);
            return;
        }
    }


    function getPoints(){
        $id = input('f_id');
        //一级
        $data = Db::name('user')->field('uid')->where('f_id',$id)->select();
         foreach($data as $value){
            // var_dump($key);
             //var_dump($value);
        $price = Db::name('goods_order')->field('money')->where('uid',$value['uid'])->where('status',4)->sum('money');
        var_dump($price);
        foreach($price as $value){
            var_dump($value);
            // $value +=$value; 
            // var_dump($value);
            
        }

       
        
        // $data1=Db::name('user')->field('uid')->where('f_id','in',$value)->select();
        // var_dump($data1);
    }

    }
}