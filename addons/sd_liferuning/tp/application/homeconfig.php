<?php
                    //配置文件
                    return [
                        'wxalipay' => [
                            'APPID'           => 'wxa834360f88210bea',
                            'MCHID'           => '1304850801',     //商户号（必须配置，开户邮件中可查看）
                            'KEY'             => 'F01F2D2665587FEA663AF6F2596CC2BB',        //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
                            'APPSECRET'       => '28bfcfcdb0bd24cb49d936d9402dc750',        //公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）
                            'SSLCERT_PATH'    => '/cert/apiclient_cert.pem',   //设置商户证书路径
                            'SSLKEY_PATH'     => '/cert/apiclient_key.pem',   //设置商户证书路径66666
                        ]
                    ];