<?php
namespace app\api\controller;
use think\View;

class Regist extends \think\Controller
{
    public function reg($phone,$strs)
    {
        try {
            require_once "SmsSender.php";
            $appid = 1400038619;
            $appkey = "cd83aabd579774a79be2d9a38278624d ";
            $phoneNumber2 = $phone;
            $templId = 7839;
            $singleSender = new SmsSingleSender($appid, $appkey);
            header("Content-type:text/html;charset=utf-8");


            // 普通单发
            $result = $singleSender->send(0, "86", $phoneNumber2, $strs, "", "");
            $rsp = json_decode($result);
//            echo $result;
        } catch (\Exception $e) {
            echo var_dump($e);
        }
    }
}