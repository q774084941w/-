<?php
namespace app\api\controller;


use app\conmon\sendSms;
use think\Controller;
use think\Request;
use think\Db;
use app\conmon;

class AliyunSms extends Controller{


    /*
     * 短信验证码
     */
    public function GetSms(){
       
       // $bid = input('bid');
        $phone = input('phone');
        //获取商户设置的短信参数
        /*if(!$bid){
            return json_encode(['status'=>0,'code'=>0,'table'=>'未设置商户id']);
        };
        $data = db('alysend')->where('bid',$bid)->find();
        if(empty($data)){
            return json_encode(['status'=>0,'code'=>0,'table'=>'未设置短信配置信息']);
        }*/

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
        $code1 = rand(1000,9999);
        $results= '您的驗證碼是'.$code1.'!'.'【澳門黑騎士】';//短信内容注意签名
        $content=strtoupper(bin2hex(iconv('utf-8','UCS-2BE',$results)));
        $code="8";
        //定时短信发送时间,格式 2017-08-01T08:09:10+08:00，null或空串表示为非定时短信(即时发送)
        $sendtime = date('Y-m-d H:i:s',time());
        $result   = $this -> send($account,$password,$mobile,$extno,$content,$code,$sendtime);

        $xml = simplexml_load_string($result);
        if($xml->returnstatus=="Faild")
        {
            // 打印出错信息
            return json_encode(['data'=>$xml->message]);

        }
        else
        {
            return json_encode(['status'=>1,'code'=>$code1,'table'=>'发送验证码成功'.$mobile]);
        }

        /*$sendSms = new sendSms();
        $code = rand(1000,9999);
        $result = $sendSms->sendSms($data,$code,$phone);
        //var_dump($result);
        if($result['Code']=='OK'){
            return json_encode(['status'=>1,'code'=>$code,'table'=>'发送验证码成功']);
        }*/
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