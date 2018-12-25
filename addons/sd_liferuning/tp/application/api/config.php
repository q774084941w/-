<?php
//配置文件
return [
    // 定义当前请求的系统常量
    define('NOW_TIME',      $_SERVER['REQUEST_TIME']),
    define('REQUEST_METHOD',$_SERVER['REQUEST_METHOD']),
    define('IS_GET',        REQUEST_METHOD =='GET' ? true : false),
    define('IS_POST',       REQUEST_METHOD =='POST' ? true : false),
    define('IS_PUT',        REQUEST_METHOD =='PUT' ? true : false),
    define('IS_DELETE',     REQUEST_METHOD =='DELETE' ? true : false),
    //ftp
];