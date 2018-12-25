<?php
namespace app\api\controller;

use think\Cache;
use app\api\model\UserModel;
use think\Controller;
use think\Request;
use app\conmon\WXBizDataCrypt;
use think\Db;

class Login extends Controller
{
   /**
    * 用户类
    *
    * 我的订单  type = 0:全部  -1:关闭  1:待付款  2:待收货  3:已完成
    */
    public function login(Request $request){
        $goodsid = $request->post();
        UserModel::instance()->login_data($goodsid);

    }
    public function logins(){
        $type = 1;//1:正式，2:审核
        switch ($type){
            case 1:
                $content = file_get_contents ( 'php://input' );
                $content=json_decode($content,true);
                // $utoken=$content["utoken"];
                // if(!empty($utoken)&&Cache($utoken)){
                //     $result["success"]=11;
                //     $result['utoken']=$utoken;
                //     echo json_encode($result);
                //     return;
                // }
                $code=$content["code"];
                $bid = $content["bid"];
                $encryptedData=$content["encryptedData"];
                $iv = $content['iv'];
                /*获取session_key*/
                $s_result=$this->getSession($code,$bid);
                $WxData = new WXBizDataCrypt($s_result['appid'],$s_result['session_key']);
                /*解密用户数据*/
                $errCode = $WxData->decryptData($encryptedData, $iv, $user_data);
                // $wxap_key = md5(uniqid(md5(microtime(true)),true));

                $result=array();
                if($errCode==0){
                    $user_data=json_decode($user_data,true);
                    $result = $user_data;
                    // $result["success"]=10;
                    //  $result['utoken']=$wxap_key;
                    // $result['openid'] = $s_result['openid'];
                    //$user_id = $this->wxUserAdd($user_data,$bid);
//            $result['uid'] = $user_id['uid'];
                    /*if($user_id < 1 || empty($user_id)){
                        $result["success"]=-1;
                        $result['errCode']=0;
                        $result['msg']="获取用户信息出错！";
                        echo json_encode($result);
                        return;
                    }*/
                    //$user_data['uid']=$user_id;
                    //var_dump($user_data['uid']);
                    //Cache::set($wxap_key,$user_data,7200);
                    echo  json_encode($result);
                    return;
                }else{
                    $result["success"]=-1;
                    $result['errCode']=$errCode;
                    $result['msg']="获取用户信息出错！！";
                    echo json_encode($result);
                    return;
                }
                break;
            case 2:
                //审核实模仿数据
                $result=array('purePhoneNumber' => '18380425296');
                echo  json_encode($result);
                break;
            default:
                echo  json_encode(array('code'=>0,'msg'=>'错误操作'));
        }

    }
    
	public static function removeEmoji($text) {
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }
    /*code 换取 session_key*/
    public function getSession($code,$bid) {
        $loginId  = Db::name('business')->where(['bid'=>$bid])->field('appid,secret')->find();

        $s_data['appid'] = $loginId['appid'];
        $s_data['secret'] = $loginId['secret'];
        $s_data['js_code'] = $code;
        $s_data['grant_type']="authorization_code";
        $session_url = 'https://api.weixin.qq.com/sns/jscode2session?'.http_build_query ( $s_data );
        // echo $session_url.'<br/>';
        $content = file_get_contents ( $session_url );
        // echo $content;
        $content = json_decode ( $content, true );
        $content['appid']=$s_data['appid'];
        return $content;
    }
    public function wxUserAdd($data,$bid){
		
        $t_user = db('user');
        $data1['openid'] = $data['openId'];
        //var_dump($data);
        //echo 1;
        //var_dump($user_info['user_id']);
        if($user_info = $t_user->where('openid',$data1['openid'])->find()){
            $data2['nickname'] = self::removeEmoji($data['nickName']);
            $data2['head'] = $data['avatarUrl'];
  
            $insert = $t_user->where('openid',$data1['openid'])->update($data2);
            return $user_info['uid'];
        }
        // if($user_info = $t_user->where('openid',$data1['openid'])->find())
        //echo 1;
        $data1['bid'] = $bid;
        $data1['nickname'] = self::removeEmoji($data['nickName']);
        $data1['sex'] = $data['gender'];
//        $data1['language'] = $data['language'];
        $data1['address'] = $data['city'];
//        $data1['province'] = $data['province'];
        $data1['head'] = $data['avatarUrl'];
        $data1['regtime'] = time();
        $data1['status'] = 1;
        //$insert_id =
        if($insert_id=$t_user ->insert($data1)){
            return  $insert_id;
        }else{
            return 0;
        }

    }
    public function getUid() {
        $openid = input('param.openid');
//        var_dump($openid);
        if(empty(input('param.openid'))){
            echo json_encode(['data'=>'没有获取权限']);
        }else{
            $loginId  = Db::name('user')->where('openid',$openid)->field('uid,phone,head')->find();
           echo json_encode(['data'=>$loginId['uid'],'phone'=>$loginId['phone'],'head'=>$loginId['head']]);
        }
    }
}
