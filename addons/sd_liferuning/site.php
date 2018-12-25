<?php
/**
 * 超人跑腿模块微站定义
 * @author 135kxcx
 * @url http://bbs.we7.cc
 */
global $_W;
$uel = $_W['siteroot'] . 'addons/' . $_W['current_module']['name'] . '/tp/public/index.php/Index/index/index';
die;
\think\Session::set('we7_user',$_W['user']);
\think\Session::set('we7_account',$_W['account']);
header('Location:'.$uel);
exit;
