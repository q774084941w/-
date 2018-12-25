<?php
namespace app\conmon;
use think\Config;
/**
 * User: atian write
 * Date: 16-9-9
 * Time: 下午2:59
 * 微信支付接口---以及其他的一些工具
 */
class Ext_WeixinApi
{

    //获取普通的access_token---调用的时候建议保存个2小时--在缓存中7200
    public static function commonAccessToken()
    {
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . Config::get('wchatf.appid') . "&secret=" . Config::get('wchatf.appSecret');
        $res = file_get_contents($url);
        $access_arr = json_decode($res, 1);
        $access_token = $access_arr['access_token'];
        return $access_token;
    }

    //网页授权获取code
    public static function code()
    {
        $u = urlencode('http://wap.zrwan.com/api/index');
        $url = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . Fn::$config['WXAPPID'] . '&redirect_uri=' . $u . '&response_type=code&scope=snsapi_userinfo&state=STATE#wechat_redirect';
        header('location:' . $url);
    }

    //获取网页授权access_token
    public static function authAccessToken($code)
    {
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid=' . Fn::$config['WXAPPID'] . '&secret=' . Fn::$config['WXAPPSECRET'] . '&code=' . $code . '&grant_type=authorization_code';
        $res = file_get_contents($url);
        $result = json_decode($res, 1);
        return $result;
    }

    //获取用户信息
    public static function userInfo($code)
    {
        $cache = Cache::load_model();
        $access_token = 'authAccessToken';
        if ($authAccessToken = $cache->get($access_token)) {
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $authAccessToken['access_token'] . '&openid=' . $authAccessToken['openid'] . '&lang=zh_CN';
            $res = file_get_contents($url);
            $result = json_decode($res, 1);
        } else {
            if (!$code) {
                Ext_WeixinApi::code();
                exit;
            }
            $authAccessToken = Ext_WeixinApi::authAccessToken($code);
            $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $authAccessToken['access_token'] . '&openid=' . $authAccessToken['openid'] . '&lang=zh_CN';
            $res = file_get_contents($url);
            $result = json_decode($res, 1);
            $cache->set($access_token, $authAccessToken, 7000);
        }
        return $result;

    }


    /**
     * 获取二维码的ticket
     * @param $common_access_token 普通的access_token
     * @param $scene_str 一般用用户ID标识
     * @param string $type 类型QR_SCENE为临时,QR_LIMIT_SCENE为永久,QR_LIMIT_STR_SCENE为永久的字符串参数值
     *
     */
    public static function getTicket($common_access_token, $scene_str, $type = 'QR_LIMIT_STR_SCENE')
    {
        $url = "https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=" . $common_access_token;
        $data_arr = array(
            "action_name" => $type,
            "action_info" => array("scene" => array("scene_str" => $scene_str))
        );

        $data_json = json_encode($data_arr);
        $res = self::post_curl($url, $data_json, 2);
        $res = json_decode($res, 1);
        return $res['ticket'];
    }

    //获取二维码的链接
    public static function getQrcode($ticket)
    {
        $url_2 = "https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket=" . urlencode($ticket);
        return $url_2;
    }

    //根据open_id获取微信用户基本信息

    public static function getUseInfo($common_access_token, $open_id)
    {
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token=" . $common_access_token . "&openid={$open_id}&lang=zh_CN";
        $use_info = json_decode(file_get_contents($url), 1);
        $use_info_arr = array();
        if ($use_info['nickname']) {
            $use_info_arr = array(
                "nick_name" => $use_info['nickname'],
                "access_token" => $use_info["openid"],
            );
        }

        return $use_info_arr;

    }


    /**
     * post发送普通的数据
     * @param $url
     * @param string $type 1->get 2=>post
     * @return string
     */
    private static function post_curl($url, $data, $type = 1)
    {
        if ($type == 1) {
            $type_str = "GET";
        } else {
            $type_str = "POST";
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type_str);
// curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (compatible; MSIE 5.01;
// Windows NT 5.0)');
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_AUTOREFERER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $info = curl_exec($ch);
        curl_close($ch);
        return $info;
    }


    //微信支付接口


    /**
     * //微信支付---直接调用此就支付了
     * @param $arr
     *
     * $arr = array(
     * "mch_appid"=>WxPayConfig::APPID,
     * "mchid"=>WxPayConfig::MCHID,
     * "device_info"=>'',//微信支付分配的终端设备号
     * "nonce_str"=>rand(100000000,999999999),//随机字符串，不长于32位
     * "sign"=>'',//签名，详见签名算法
     * "partner_trade_no"=>'',//商户订单号，需保持唯一性
     * "openid"=>'',//商户appid下，某用户的openid
     * "check_name"=>"NO_CHECK",
     * "re_user_name"=>"阿田",//收款用户真实姓名。
     * "amount"=>"10",//企业付款金额，单位为分，
     * "desc"=>"测试数据",//企业付款操作说明信息。必填。
     * "spbill_create_ip"=>$_SERVER["REMOTE_ADDR"],//调用接口的机器Ip地址
     *
     * );
     */
    public static function DoPay(Array $arr)
    {
        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $arr['sign'] = self::MakeSign($arr);
        $xml_data = self::ToXml($arr);
        $info = self::postXmlCurl($xml_data, $url, true);
        $data = self::FromXml($info);
        return $data;
    }


    //微信下单
    public static function DoOrder(Array $arr)
    {
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $arr['sign'] = self::MakeSign($arr);
        $xml_data = self::ToXml($arr);
        $info = self::postXmlCurl($xml_data, $url, false);


        $data = self::FromXml($info);
        return $data;
    }


    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public static function MakeSign($arr)
    {
        //签名步骤一：按字典序排序参数
        ksort($arr);
        $string = self::ToUrlParams($arr);
        //签名步骤二：在string后加入KEY
        $string = $string . "&key=" .Config::get('wchatf.wxpaysignkey');
        //签名步骤三：MD5加密
        $string = md5($string);
        //签名步骤四：所有字符转为大写
        $result = strtoupper($string);
        return $result;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public static function ToUrlParams($arr)
    {
        $buff = "";
        foreach ($arr as $k => $v) {
            if ($k != "sign" && $v != "" && !is_array($v)) {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }


    /**
     * 输出xml字符
     * @throws WxPayException
     **/
    public static function ToXml($arr)
    {
        if (!is_array($arr) || count($arr) <= 0) {
            throw new WxPayException("数组数据异常！");
        }

        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }

        $xml .= "</xml>";
        return $xml;
    }
    /**
     * xml转数组
     * @throws WxPayException
     **/
     public static function xmlToArray($xml){

        //禁止引用外部xml实体

        libxml_disable_entity_loader(true);

        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);

        $val = json_decode(json_encode($xmlstring),true);

        return $val;

    }

    /**
     * 以post方式提交xml到对应的接口url
     *
     * @param string $xml 需要post的xml数据
     * @param string $url url
     * @param bool $useCert 是否需要证书，默认不需要
     * @param int $second url执行超时时间，默认30s
     * @throws WxPayException
     */
    public static function postXmlCurl($xml, $url, $useCert = false, $second = 30)
    {

        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);

        //如果有配置代理这里就设置代理
        // if (Fn::$config['WXCURL_PROXY_HOST'] != "0.0.0.0" && Fn::$config['WXCURL_PROXY_PORT'] != 0) {
        //     curl_setopt($ch, CURLOPT_PROXY, Fn::$config['WXCURL_PROXY_HOST']);
        //     curl_setopt($ch, CURLOPT_PROXYPORT, Fn::$config['WXCURL_PROXY_PORT']);
        // }

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, TRUE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);//严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

        if ($useCert == true) {
            //设置证书
            //使用证书：cert 与 key 分别属于两个.pem文件
            curl_setopt($ch, CURLOPT_SSLCERTTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLCERT, ROOT_PATH.'cert'.DS.'apiclient_cert.pem');
            curl_setopt($ch, CURLOPT_SSLKEYTYPE, 'PEM');
            curl_setopt($ch, CURLOPT_SSLKEY, ROOT_PATH.'cert'.DS.'apiclient_key.pem');
        }
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);

        //运行curl
        $data = curl_exec($ch);


        curl_close($ch);
        //返回结果
        if ($data) {

            return $data;
        }

    }

    /**
     * 将xml转为array
     * @param string $xml
     * @throws WxPayException
     */
    public static function FromXml($xml)
    {
        if (!$xml) {
            return false;
        }
        //将XML转为array
        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        return $values;
    }
}