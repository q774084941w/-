<?php
namespace app\api\model;

use think\Model;
class CodeModel extends Model{
     public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CodeModel();
        }
        return $m;
    }
 public function getQcode($seller_id){
        $access_token = 'prGflyonDTNAxY7rf3TVdIiCM6wQMpPomHhopj1-ZbXdXBLGBNexFoYIYClgSz8NXmEw2QVO6P8VQObZwvSmib4G8OUE6upir10392-hI9oMPLdAFAEHD';//$this->cache->get("wx_access_token");

        $url ='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token= '.$access_token;
        if($seller_id === 'shouye'){//首页
            $url = 'https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token= '.$access_token;
            $data_arr = array(
                "scene"=> "apple?seller_id=".$seller_id,
                "width"=> 430
            );
        }
        else{
            $data_arr = array(//店铺
                "scene"=> "boy?seller_id=".$seller_id,
                "width"=> 430
            );
            // if ($type === 1){//详情

            //     $data_arr = array(
            //         "scene"=> "girl?goods_id=".$seller_id,
            //         "width"=> 430
            //     );

            // }
        }

        $res = $this->post_http($url, json_encode($data_arr));
        var_dump($res);
        if ($res) {
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
}