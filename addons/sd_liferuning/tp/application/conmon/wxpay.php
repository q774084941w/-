<?php
namespace app\conmon;

require_once(dirname(__FILE__) . '/wxpaylib/WxPay.Api.php');
require_once(dirname(__FILE__) . '/wxpaylib/WxPay.Notify.php');

class wxpay extends \WxPayNotify
{
    /**
     * 微信数据
     *
     * @var string
     */
    private $data = '';

    

    /**
     * 统一下单
     *
     * @param $order 订单号
     * @param $fee 总金额
     * @param $body 商品描述
     * @param $callback_url 微信回调url
     */
    public function unifiedOrder($order,$fee,$body,$callback_url,$openId,$setAttach){
        //统一下单

        $input = new \WxPayUnifiedOrder();
        $input->SetBody($body);                 //订单描述
        $input->SetTotal_fee($fee);             //订单金额
        $input->SetOut_trade_no($order);        //订单号
        //$input->SetSpbill_create_ip($ip);     //支付提交用户端ip
        $input->SetNotify_url($callback_url);   //微信回调地址
        $input->SetTrade_type("JSAPI");
      
        $input->SetOpenid($openId);             //用户openid
        $input->SetTime_start(date('YmdHis'));       //订单生成时间
        $input->SetTime_expire(date('YmdHis',time() + 1800));      //订单失效时间（30分钟内有效）

        $result = \WxPayApi::unifiedOrder($input);

        if(!array_key_exists("appid", $result) ||
            !array_key_exists("mch_id", $result) ||
            !array_key_exists("prepay_id", $result))
        {
            file_put_contents( 'runtime/weixin.log',$result,FILE_APPEND);
            return false;
        }
        return $result;
    }
    /**
     * 获取支付参数
     * @param
     */
    public function getAppData($order,$fee,$body,$callback_url,$openId,$setAttach){
        $result = $this->unifiedOrder($order,$fee,$body,$callback_url,$openId,$setAttach);
        if($result===false){
            return false;
        }
        $jsapi = new \WxPayJsApiPay();
        $jsapi->SetAppid($result["appid"]);
        $timeStamp = time();
        $jsapi->SetTimeStamp("$timeStamp");
        $jsapi->SetNonceStr(\WxPayApi::getNonceStr());
        $jsapi->SetPackage("prepay_id=" . $result['prepay_id']);
        $jsapi->SetSignType("MD5");
        $jsapi->SetPaySign($jsapi->MakeSign());
      
        $result = $jsapi->GetValues();
        return $result;

    }
    /**
     * 退款
     */
    public function out_price($result){
        $input=new \WxPayRefund();
        $input->SetAppid($result['appid']);//公众账号ID
        $input->SetMch_id($result['mchid']);//商户号
        $input->SetOut_trade_no($result['order_no']);
        $input->SetOut_refund_no($result['trade_no']);
        $input->SetTotal_fee($result['price']*100);
        $input->SetRefund_fee($result['price']*100);
        $input->SetOp_user_id($result['mchid']);
        $result = \WxPayApi::refund($result['bid'],$input);

        return $result;
    }
    /**
     * 将xml转化成数组
     */
    public function verfy($xml){
        $verify_result = $this->FromXml($xml);
        if ($verify_result) {
            return $verify_result;
        } else {
            return false;
        }
    }

}