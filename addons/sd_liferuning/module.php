<?php
/**
 * 超人跑腿模块微站定义
 *
 * @author 135kxcx
 * @url http://bbs.we7.cc
 */
global $_W;
$url = $_W['siteroot'] . 'addons/' . $_W['current_module']['name'] . '/tp/index.php';
include "tp/thinkphp/base.php";
//var_dump($uel);die;
\think\Session::set('we7_user',$_W['user']);
\think\Session::set('we7_account',$_W['account']);
//var_dump($_W['account']);die;
header('Location:'.$url);
exit;


