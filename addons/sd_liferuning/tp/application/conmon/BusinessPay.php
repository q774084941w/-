<?php
namespace app\conmon;
use think\Db;
class BusinessPay
{
    protected $appid='';
    protected $mch_id='';
    protected $mch_no='';
    protected $apiclient_cert='';
    protected $apiclient_key='';
    public function __construct($bid)
    {
        $field='appid,mchid,key';
        $Business=Db::name('business')->where('bid',$bid)->field($field)->find();
        $this->appid=$Business['appid'];
        $this->mch_id=$Business['mchid'];
        $this->mch_no=$Business['key'];
        $this->apiclient_cert=ROOT_PATH.'application/conmon/wxpaylib/cert/apiclient_cert'.$bid.'.pem';
        $this->apiclient_key=ROOT_PATH.'application/conmon/wxpaylib/cert/apiclient_key'.$bid.'.pem';

    }

    public function Deposit($money,$openid,$trade_no){
        $isrr = array(
            'error' => 0,
      
        );
        return $isrr;
        $arr = array();
        $arr['mch_appid'] = $this->appid;
        $arr['mchid'] = $this->mch_id;
        $arr['nonce_str'] = random(20);//随机字符串，不长于32位
        $arr['partner_trade_no'] = $trade_no;//商户订单号
        $arr['openid'] = $openid;
        $arr['check_name'] = 'NO_CHECK';//是否验证用户真实姓名，这里不验证
        $arr['amount'] = $money*100;//付款金额，单位为分
        $arr['amount'] =100;
        $desc = "提现";
        $arr['desc'] = $desc;//描述信息
        $arr['spbill_create_ip'] = $_SERVER['SERVER_ADDR'];//获取服务器的ip
        //封装的关于签名的算法
        $notify = new Notify_pub($this->mch_no);
        $arr['sign'] = $notify->getSign($arr);//签名
        $var = $notify->arrayToXml($arr);
        $xml = $this->curl_post_ssl('https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers', $var, 30, array(), 1);
        $disableLibxmlEntityLoader=libxml_disable_entity_loader(true);
        $rdata = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        libxml_disable_entity_loader($disableLibxmlEntityLoader);
        $return_code = (string)$rdata->return_code;
        $result_code = (string)$rdata->result_code;
        $return_code = trim(strtoupper($return_code));
        $result_code = trim(strtoupper($result_code));
        if ($return_code == 'SUCCESS' && $result_code == 'SUCCESS') {
            $isrr = array(
                'con'=>'ok',
                'error' => 0,
            );
        } else {

//            $returnmsg = (string)$rdata->return_msg;
            $err_code_des = (string)$rdata->err_code_des;
            $isrr = array(
                'error' => 1,
                'errmsg'=>$err_code_des
            );

        }
        return $isrr;
    }
    function curl_post_ssl($url, $vars, $second = 30, $aHeader = array())
    {
        $ch = curl_init();//初始化curl
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);//设置执行最长秒数
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_URL, $url);//抓取指定网页
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);// 终止从服务端进行验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);//
        curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');//证书类型
        curl_setopt($ch, CURLOPT_SSLCERT, $this->apiclient_cert);//证书位置
        curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');//CURLOPT_SSLKEY中规定的私钥的加密类型
        curl_setopt($ch, CURLOPT_SSLKEY, $this->apiclient_key);//证书位置
        if (count($aHeader) >= 1) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $aHeader);//设置头部
        }
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);//全部数据使用HTTP协议中的"POST"操作来发送

        $data = curl_exec($ch);//执行回话
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "call faild, errorCode:$error\n";
            curl_close($ch);
            return false;
        }
    }

}
class Notify_pub{
    private $mch_no='';
    public function __construct($mch_no)
    {
        $this->mch_no=$mch_no;
    }
    public function getSign($arr){
        ksort($arr);
        $string='';
        foreach ($arr as $k => $v)
        {
            if($k != "sign" && $v != "" && !is_array($v)){
                $string .= $k . "=" . $v . "&";
            }
        }
        $string = trim($string, "&");
        $string = $string ."&key=".$this->mch_no;
        $string = md5($string);

        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }
    function arrayToXml($data){
        $xml = "<xml>";
        foreach ($data as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }
}


