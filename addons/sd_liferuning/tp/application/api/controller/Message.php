<?php

namespace app\api\controller;
use think\Controller;
class Message extends Controller{
    public function listMe($bid){
        $data=db('Notice')->where('bid',$bid)->order('times desc')->select();
        foreach ($data as $key=>$val){
            $data[$key]['times']=date('Y-m-d H:i:s',$val['times']);
        }
        exit(json_encode(['code'=>1,'data'=>$data]));
    }
    public function info($id){
        $data=db('Notice')->where('id',$id)->order('times desc')->find();
        $data['times']=date('Y-m-d H:i:s',$data['times']);
        exit(json_encode(['code'=>1,'data'=>$data]));
    }
    public function clause($bid,$type){
        if(empty($type))$type=1;
        $result=db('Clause')->where(['bid'=>$bid,'type'=>$type])->find();
        if($result){
            exit(json_encode(['code'=>1,'data'=>$result]));
        }else{
            exit(json_encode(['code'=>0,'data'=>'没有条款']));
        }
    }
    /**
     * 语音提醒
     */

    private static function getToken()
    {
        $appid = 'wx18d3e1aa8b3fb5dd';
        $secret = '530202d48fb9b9b0160ffedf43b9696f';
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$appid}&secret={$secret}";
        return $data = self::curlGet($url);
    }

    private static function curlGet ($url) {
        $ch  = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        $obj=json_decode($data);
        if(empty($obj->errcode)){
            return $obj->access_token;//返回的token
        }else{
            return false;
        }
    }

    private static function getHttpArray($url,$post_data) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        // curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);   //没有这个会自动输出，不用print_r();也会在后面多个1
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $output = curl_exec($ch);
        curl_close($ch);
        $out = json_decode($output);
        return $out;
    }

    public static function smallWXmessage()
    {
        $data = <<<END
            {
              "touser": "OPENID",
              "template_id": "TEMPLATE_ID",
              "page": "index",
              "form_id": "FORMID",
              "data": {
                  "keyword1": {
                      "value": "339208499"
                  },
                  "keyword2": {
                      "value": "2015年01月05日 12:30"
                  },
                  "keyword3": {
                      "value": "粤海喜来登酒店"
                  } ,
                  "keyword4": {
                      "value": "广州市天河区天河路208号"
                  }
              },
              "emphasis_keyword": "keyword1.DATA"
            }
END;
        $access = json_decode(self::getToken(),true);  //获取token
        $access_token= $access['access_token'];
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=" . $access_token;
        $data = self::getHttpArray($url,$data);  //post请求url
        return $data;
    }


}

?>