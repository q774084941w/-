<?php

namespace app\home\model;


class Sms
{

    /**
     * 单例模式
     * @return GoodsModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new Sms();
        }
        return $m;
    }

    /**
     * 发送短信
     * @param $phone
     * @param $code1
     * @return false|mixed|string
     */
    public function index($phone,$code1)
    {
        if (empty($phone))
        {
            return false;
        }
        $lenth = strlen($phone);
        switch ($lenth)
        {
            case 11:
                $number = '86';
                break;
            default:
                $number = '853';
        }

        $account="SHGJ32";		//账户名
        $password="abc123";		//密码
        $mobile= $number.$phone;		//目标手机号码，多个用半角“,”分隔
        $extno = "";
        $results= '親,當您在收貨時請給予黑騎士的收貨码'.$code1.'!【暗號】';//短信内容注意签名
        $content=strtoupper(bin2hex(iconv('utf-8','UCS-2BE',$results)));
        $code="8";
        //定时短信发送时间,格式 2017-08-01T08:09:10+08:00，null或空串表示为非定时短信(即时发送)
        $sendtime = date('Y-m-d H:i:s',time());
        $result   = $this -> send($account,$password,$mobile,$extno,$content,$code,$sendtime);

        $xml = simplexml_load_string($result);
        if($xml->returnstatus=="Faild")
        {
            // 打印出错信息
            return $mobile.'---'.$xml->message;
        }
        else
        {
            return true;
        }
    }

    protected function send($account,$password,$mobile,$extno,$content,$code,$sendtime)
    {
        $url = "https://dx.ipyy.net/I18NSms.aspx";

        $data=array(
            'action'=>'send',
            'userid'=>'',
            'account'=>$account,
            'password'=>$password,
            'mobile'=>$mobile,
            'extno'=>$extno,
            'code'=>$code,
            'content'=>$content,
            'sendtime'=>$sendtime
        );
        $ch=curl_init();
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }
}