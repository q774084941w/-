<?php
		header("Content-Type: text/html; charset=UTF-8");

    	const url ="https://dx.ipyy.net/I18NSms.aspx";
		//发送短信
		function send($account,$password,$mobile,$extno,$content,$code,$sendtime)
    	{
    		$data=array(
    				'action'=>'send',
    				'userid'=>'',
    				'account'=>$account,
    				'password'=>$password,
    				'mobile'=>$mobile,
    				'extno'=>$extno,
					'code'=>$code,
    				'content'=>$content,
    				'sendtime'=>$sendtime    				
    		);
    		$ch=curl_init();
    		curl_setopt($ch, CURLOPT_URL,url);
    		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
    		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,false);
    		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
    		$result = curl_exec($ch);
    		curl_close($ch); 
    		return $result;
    	}
	 $account="SHGJ32";		//账户名
	 $password="msjsms123";		//密码
	 $mobile='85363416028';		//目标手机号码，多个用半角“,”分隔
	 $extno = "";

	 $results= '您的驗證碼是'.rand(1000,9999).'!'.'【澳門黑騎士】';//短信内容注意签名
	 $content=strtoupper(bin2hex(iconv('utf-8','UCS-2BE',$results)));
	 $code="8";
	 //定时短信发送时间,格式 2017-08-01T08:09:10+08:00，null或空串表示为非定时短信(即时发送)
	 $sendtime = date('Y-m-d H:i:s',time());

	 $result =send($account,$password,$mobile,$extno,$content,$code,$sendtime);
	 $xml = simplexml_load_string($result);
	 if($xml->returnstatus=="Faild")
    {
		// 打印出错信息  
    	echo "接口调用失败---原因是:".$xml->message."</br>";
		echo "</br>";
		echo "请认真检查代码！";
    }
    else
    {
	 echo "返回信息提示：".$xml->message."</br> ";
	 echo "返回状态为：".$xml->returnstatus."</br> ";
	 echo "返回信息：".$xml->message."</br> ";
	 echo "返回余额：".$xml->balance."</br> ";
	 echo "返回本次任务ID：".$xml->taskID."</br> ";
	 echo "返回本次扣费金额：".$xml->BillingAmount."</br> ";
	 echo "返回成功短信数：".$xml->successCounts."</br> ";
	}
?>