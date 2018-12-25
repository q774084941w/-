<?php
    //配置文件
    return [
        'wxalipay' => [
            'APPID'           => 'wx117c47ffda22ae7a',
            'MCHID'           => '1501855201',     //商户号（必须配置，开户邮件中可查看）
            'KEY'             => '2wzctx1i5wftyt4jxqtqeqglcixzptx2',        //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
            'APPSECRET'       => '89f3ee77fad04022442c6dac561611f4',        //公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）
            'SSLCERT_PATH'    => '/cert/apiclient_cert.pem',   //设置商户证书路径
            'SSLKEY_PATH'     => '/cert/apiclient_key.pem',   //设置商户证书路径
        ]
    ];