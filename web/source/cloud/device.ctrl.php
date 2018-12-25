<?php
/**
 * [ System] Copyright (c) 
 *  is NOT a free software, it under the license terms, visited http:/// for more details.
 */
defined('IN_IA') or exit('Access Denied');
if ($do == 'online') {
	header('Location: //upwe7.weixin2015.cn/app/api.php?referrer='.$_W['setting']['site']['key']);
	exit;
} elseif ($do == 'offline') {
	header('Location: //upwe7.weixin2015.cn/app/api.php?referrer='.$_W['setting']['site']['key'].'&standalone=1');
	exit;
} else {
}
template('cloud/device');
