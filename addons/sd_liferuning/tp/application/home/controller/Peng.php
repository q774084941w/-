<?php
namespace app\api\Controller;
use app\conmon\Ext_WeixinApi;
use think\Controller;
use think\Config;
use think\Db;
class Peng extends Controller{
    public function _iniaialize(Request $request = null)
    {


    }
	public function goods(){
		$input = input('param.');
		$data = Db::name('goods')->where('status',1)->where('bid',$input['bid'])->where('goodsid','>',$input['lastid'])->limit($input['limit'])->select();
		//var_dump($data);
		//$info='https://'.$_SERVER['HTTP_HOST'];
		foreach($data as &$vo){
			$vo['pic'] = uploadpath('goods',$vo['pic']);
		}
		
		echo json_encode($data);
	}
	public function lists(){
		$input=input('param.');
		$where=array(
				'status'=>1,
				'recom'=>1,
				'bid'=>$input['bid']
			);
		$data = Db::name('goods')->where($where)->limit(5)->select();
		//var_dump($data);
		foreach($data as &$vo){
			$vo['pic'] = uploadpath('goods',$vo['pic']);
		}
		if($data){
			echo json_encode($data);
		}else{
			$result['code']=1;
			$result['error']=2;
			echo json_encode($result);
		}
	} 
	public function takegoods(){
		$input=input('param.');
		$feilv=array(
				'fei1'=>'0.15',
				'fei2'=>'0.1'
				);
		if(empty($input)){
			$result['code']=0;
			$result['msg']="数据为空";
			echo json_encode($result);
		}else{
			$status = Db::name('goodsOrder')->field('money,status,orderid,uid')->where(['uid'=>$input['uid'],'orderid'=>$input['orderid']])->find();
			if($status['status']==3){
				$time=time();
				$result = Db::name('goodsOrder')->where(['uid'=>$input['uid'],'orderid'=>$input['orderid']])->update(['status'=>4,'taketime'=>$time]);
				if($result){
					$data = Db::name('user')->field('f_id,balance')->where('uid',$input['uid'])->find();
					$data1 =Db::name('user')->field('balance,f_id,uid')->where(['uid'=>$data['f_id']])->find();
					//var_dump($data);die;
					if($data){
						$money = $status['money']*$feilv['fei1'];
						//return var_dump($money);
						$user  = Db::name('user')->where(['uid'=>$data1['uid']])->setInc('balance',$money);
					}
					if($data1){
						$money1 = $status['money']*$feilv['fei2'];
						$user  = Db::name('user')->where(['uid'=>$data1['f_id']])->setInc('balance',$money1);
					}
					$result1['code']=1;
					$result1['msg']="操作成功";
					 echo json_encode($result1);
				}else{
					$result2['code']=-1;
					$result2['msg']="操作失败";
					echo json_encode($result2);
				}
			}
		}
	}
	public function my(){
		$input=input('param.');
	if(empty($input)){
		$result['code']=0;
		$result['msg']="数据为空";
		echo json_encode($result);
	}else{
		//查询金牌的信息
		//1、金牌的
		$data = Db::name('user')->field('balance,head,nickname,regtime')->where(['uid'=>$input['uid']])->find();
		$money = Db::name('goods_order')->where(['uid'=>$input['uid'],'status'=>4,'bid'=>7])->sum('money');
		$num = Db::name('goods_order')->where(['uid'=>$input['uid'],'status'=>4,'bid'=>7])->count('orderid');
		$data['regtime']=date('Y-m-d H:i:s',$data['regtime']);
		$data['money'] = $money;
		$data['num'] = $num;
		//2、银牌的信息
		$data1= Db::name('user')->field('f_id,uid,head,nickname,regtime')->where(['f_id'=>$input['uid']])->select();
		$count=Db::name('user')->where(['f_id'=>$input['uid']])->count();
		if($data1){
			foreach($data1 as $key=>&$vo){
				$money1 = Db::name('goods_order')->where(['uid'=>$vo['uid'],'status'=>4,'bid'=>7])->sum('money');
		 		$num1 = Db::name('goods_order')->where(['uid'=>$vo['uid'],'status'=>4,'bid'=>7])->count('orderid');
		 		$vo['regtime'] = Date('Y-m-d H:i:s',$vo['regtime']);
				$vo['money'] = $money1;
	 			$vo['num'] = $num1;
			}
			$data11 = array_column($data1,"uid");
			//var_dump($data11);
			$data2 = Db::name('user')->field('uid,f_id,head,nickname,regtime')->where('f_id','in',$data11)->select();
			//var_dump($data2);
			$count1 = Db::name('user')->where('f_id','in',$data11)->count();
			foreach ($data2 as $key1 =>&$vo1) {
				$money2 = Db::name('goods_order')->where(['uid'=>$vo1['uid'],'status'=>4,'bid'=>7])->sum('money');
	 			$num2 = Db::name('goods_order')->where(['uid'=>$vo1['uid'],'status'=>4,'bid'=>7])->count('orderid');
	 			$vo1['regtime']=Date('Y-m-d H:i:s',$vo1['regtime']);
				$vo1['money'] =$money2;
	 			$vo1['num'] = $num2; 
			}
			$result['data']=$data;
			$result['count']=$count;
			$result['count1']=$count1;
			$result['data1']=$data1;
			$result['data2']=$data2;
	//var_dump($result['data1']);
	echo json_encode($result);
		}else{
			$count1=0;
			$data2=[];
			$result['data']=$data;
			$result['count']=$count;
			$result['count1']=$count1;
			$result['data1']=$data1;
			$result['data2']=$data2;
			echo json_encode($result);
		}
		//var_dump($data2);
	// 	foreach($data1 as $key=>&$vo){
	// 	//查询铜牌的信息
	// 	$data2 = Db::name('user')->field('uid,head,nickname,regtime')->where(['f_id'=>$vo['uid']])->select();
	// 	$count1=Db::name('user')->where(['f_id'=>$vo['uid']])->count();
	// 	foreach($data2 as $key1 =>&$vo1){
	// 		$money2 = Db::name('goods_order')->where(['uid'=>$vo1['uid'],'status'=>4,'bid'=>7])->sum('money');
	// 		$num2 = Db::name('goods_order')->where(['uid'=>$vo1['uid'],'status'=>4,'bid'=>7])->count('orderid');
	// 		$vo1['regtime']=Date('Y-m-d H:i:s',$vo1['regtime']);
	// 		$vo1['money'] =$money2;
	// 		$vo1['num'] = $num2; 
	// 	}
	// 	//银牌信息
	// 	$money1 = Db::name('goods_order')->where(['uid'=>$vo['uid'],'status'=>4,'bid'=>7])->sum('money');
	// 	$num1 = Db::name('goods_order')->where(['uid'=>$vo['uid'],'status'=>4,'bid'=>7])->count('orderid');
	// 	$vo['regtime'] = Date('Y-m-d H:i:s',$vo['regtime']);
	// 	$vo['money'] = $money1;
	// 	$vo['num'] = $num1;
	// unset($vo);
	// }

	
	}
}
    public function news(){
    	$input = input('param.');
    	//var_dump($input);
    	if(empty($input)){
    		$result['code'] = 0;
    		$result['message'] ="提交数据为空";
    		echo json_encode($result);
    	}else{
    		$input['createtime'] = time();
    		$data = Db::name('appointment')->insert($input);
    		if($data){
    		$result['code'] = 1;
    		$result['message'] ="提交成功";
    		echo json_encode($result);
    		}
    	}
    }
    public function appoint(){
    	$input = input('param.');
    	if(empty($input)){
    		$result['code'] = 0;
    		$result['message'] ="提交数据为空";
    		echo json_encode($result);
    	}else{
    		$data = Db::name('appointment')->field('id,status,name,tel,project,arrivetime')->where(['uid'=>$input['uid']])->select();
    		echo json_encode($data);
    	}
    }
    public function edit(){
    	$input = input('param.');
    	if(empty($input)){
    		$result['code'] = 0;
    		$result['message'] ="提交数据为空";
    		echo json_encode($result);
    	}else{
    		$data = Db::name('appointment')->where(['id'=>$input['id']])->update(['status'=>0]);
    		if($data){
    		$result['code'] = 1;
    		$result['message'] ="提交成功";
    		echo json_encode($result);
    		}
    	}
    }
    //如果再见不能红着眼，是否还能红着脸。就像那年匆促，刻下永远一起，那样美丽的谣言，如果过去还值得眷恋，别太快冰释前嫌，
    //谁甘心就这样，彼此无挂也无牵，我们要互相亏欠，要不然凭何怀缅
    public function payBackMoney(){
        //$user_model = User_Model::load_model();
    	$input=input('param.');
    	$appId = Config::get('wchatf.appid');
		$appSecret = Config::get('wchatf.appSecret');
    	if($input['money']<500){
    		$result['code']=0;
    		$result['message']='提交失败';
    		echo json_encode($result);
    	}else{
        $user_res = Db::name('user')->field('openid')->where('uid',$input['uid'])->find();//(退钱给该id用户)
        //var_dump($user_res);
        $openid = $user_res['openid'];
        $pay_data = array(
            "mch_appid" =>$appId,
            "mchid" => $appSecret,
            "nonce_str" => '7758521',
            "partner_trade_no" => time().mt_rand(10000,99999),
            "openid" => $openid ,
            "check_name" => 'NO_CHECK',
            "amount" => $input['money'] * 100 ,
            "desc" => '化妆品支出',
            "spbill_create_ip" => $_SERVER['REMOTE_ADDR']
        );
        $Ext_WeixinApi = new Ext_WeixinApi();
        $pay_data['sign'] =  $Ext_WeixinApi->MakeSign($pay_data);

        $send_data =  $Ext_WeixinApi->ToXml($pay_data);


        $url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
        $res =  $Ext_WeixinApi->postXmlCurl($send_data,$url,true);
        var_dump($res);
        $res =  $Ext_WeixinApi->xmlToArray($res);

        if($res['return_code'] == 'SUCCESS' && $res['result_code'] == 'SUCCESS'){
        	$data = Db::name('user')->where('uid',$input['uid'])->update()
            echo json_encode(11);

        }else{
        	echo json_encode(111);
            //return false;
        }
     }
    }
}
