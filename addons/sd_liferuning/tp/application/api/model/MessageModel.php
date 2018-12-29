<?php

namespace app\api\model;


class MessageModel
{
    public static function curl_get($url){
        $curl = curl_init(); // 启动一个CURL会话
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_HEADER,0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,false);// 跳过证书检查
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,2);// 从证书中检查SSL加密算法是否存在
        $tmpInfo = curl_exec($curl);     //返回api的json对象
        //关闭URL请求
        curl_close($curl);
        return $tmpInfo;    //返回json对象
    }



    public static function postMsg($accesstoken,$data){

        $url="https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token=".$accesstoken;
        $post_data = json_encode($data,JSON_UNESCAPED_UNICODE);
        sleep(1);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, 1); // 发送一个常规的Post请求
        curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        if (curl_errno($curl)) {
            return 'Errno'.curl_error($curl);//捕抓异常
        }
        curl_close($curl);
        return json_decode($output,true);

    }

    public static function sendMsg($openId,$formId,$data,$templateId,$page="service/pages/service/index/index"){
        $datass=array(
            "touser"=>$openId,//openid
            "template_id"=>$templateId,
            "form_id"=>$formId,
            "page"=>$page,
            "data"=>array(
                "keyword1"=>array(
                    "value"=> $data[0],
                    "color"=>"#173177"
                ),
                "keyword2"=>array(
                    "value"=> $data[1],
                    "color"=>"#173177"
                ),
                "keyword3"=>array(
                    "value"=> $data[2],
                    "color"=>"#173177"
                ),
                "keyword4"=>array(
                    "value"=> $data[3],
                    "color"=>"#173177"
                )
            )
        );
        $accesstoekn=self::curl_get("https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=wx18d3e1aa8b3fb5dd&secret=530202d48fb9b9b0160ffedf43b9696f");
        $accesstoekn = json_decode($accesstoekn);
        $msg = self::postMsg($accesstoekn->access_token,$datass);
        return $msg;
    }
}