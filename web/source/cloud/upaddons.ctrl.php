<?php
/**
 * [WeEngine System] Copyright (c) 2014 WE7.CC
 * WeEngine is NOT a free software, it under the license terms, visited http://www.we7.cc/ for more details.
 */
 

load()->func('communication');
load()->model('cloud');
load()->func('file');
load()->func('up');

cache_write('we7:module:all_uninstall', $cache, CACHE_EXPIRE_LONG);
$json = @json_decode(base64_decode($_GET['dataurl']),true);
$ver_burl=$_SERVER['SERVER_NAME']; 

$uppaddons_xx=CLOUD_GATEWAY_URL_ADDONSKEY($json['sid'],$json['biaoshi'],$json['version'],$ver_burl);

if ($uppaddons_xx['type']=="error"){
itoast($uppaddons_xx['message'], url('module/manage-system/not_installed'), 'error');
}else{

$pr=$uppaddons_xx['data']['files'];
$prcl = explode('||',$pr);
$packet['files'] = $prcl;
$packet['type'] = '';  

$pathurl=$uppaddons_xx['data']['azurl'];
$updatenowinfo=$uppaddons_xx['data']['zip'];
$updatedir = IA_ROOT.'/data/update';

		if (strstr($updatenowinfo, 'zip')){		
			
				$pathurl=$uppaddons_xx['data']['azurl'];
				$updatedir = IA_ROOT.'/data/update';
				
				if(!is_dir($updatedir)) {mkdirs($updatedir);}	

				$isgot = get_file($pathurl, $updatenowinfo, $updatedir);
				
				if($isgot){		
						
					$updatezip = $updatedir . '/' . $updatenowinfo;
					require_once IA_ROOT.'/framework/library/phpexcel/PHPExcel/Shared/PCLZip/pclzip.lib.php';
					$thisfolder = new PclZip($updatezip);
					$isextract = $thisfolder->extract(PCLZIP_OPT_PATH, $updatedir);
					if ($isextract == 0){ itoast('更新解压失败，请查看是否支持PHP解压！', url('module/manage-system/not_installed'), 'error');} 
					$archive = new PclZip($updatezip);
					$list = $archive->extract(PCLZIP_OPT_PATH, IA_ROOT, PCLZIP_OPT_REPLACE_NEWER); 
					
					if ($list == 0) {itoast('远程更新文件不存在或站点没有读写权限,下载更新失败！', url('module/manage-system/not_installed'), 'error');} 
					
		  }
				
	}

				
}

template('cloud/upaddons');
