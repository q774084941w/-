<?php
namespace app\api\controller;

use app\api\model\TakeoutModel;
use think\Controller;
use think\Request;
use think\Db;
use app\home\model\CardModel;

class Takeout extends Controller
{
   /**
    * 提现记录类
    *
    * 提交
    */
    public function MoneySubmit(Request $request){
        $data = $request->post();
        if(empty($data['name'])) $this->outPut('', 1001, ":缺少参数name" );
        if(empty($data['alipay'])) $this->outPut('', 1001, ":缺少参数alipay" );
        if(empty($data['money'])) $this->outPut('', 1001, ":缺少参数money" );
        if(empty($data['bid'])) $this->outPut('', 1001, ":缺少参数bid" );
        if($data['money'] < 500) $this->outPut(null,0);;
        list($result,$info) = TakeoutModel::instance()->MoneySubmit($data,$this->uid);
        if($result == false)  $this->outPut(null,$info);
        $this->jsonOut($info);
    }
    /**
     * 提现记录
     */
    public function Moneyrecord(Request $request){
        $bid = $request->get('bid');
        if(empty($bid)) $this->outPut('', 1001, ":缺少参数bid" );
        $result = TakeoutModel::instance()->Moneyrecord($this->uid,$bid);
        $this->jsonOut($result);
    }
    /**
     * 提交提现到银行卡
     */
    public function BrankMoneySub(Request $request){
        $data = $request->post();

        if(empty($data['money'])) $this->outPut('', 1001, ":缺少参数money" );
        if(empty($data['uid'])) $this->outPut('', 1001, ":缺少参数uid" );
        if(empty($data['bid'])) $this->outPut('', 1001, ":缺少参数bid" );

        $uidmoney = db('cust_user')->where(['uid'=>$data['uid']])->find();

        if($uidmoney['money'] < $data['money']) {
            echo json_encode([false,7001]);die;  //余额不足
        }
		$Card=CardModel::CardDefault($data['uid']);
      	if(empty($Card))$this->outPut('', 1001, "没有设置银行卡" );
        Db::startTrans();
        $moneys = $uidmoney['money'] - $data['money'];
     
        $vn = [
            'uid'=>$data['uid'],
            'bid'=>$data['bid'],
            'money'=>$data['money'],
            'name'=>$Card['name'],
            'status'=>0,
            'createtime'=>time(),
            'is_brank'=>1,
            'brank'=>$Card['cardnumber'],
            'kaihuhang'=>$Card['uname']
        ];
        $add=[
            'uid'=>$data['uid'],
            'money'=>$data['money'],
            'msg'=>'提现操作',
            'paytype'=>'pay',
            'createtime'=>time(),
            'type'=>2,
            'cust'=>1
        ];

        $takeout = Db::name('takeOut')->insert($vn);
        $rs=Db::name('PriceMsg')->insert($add);
        $usermo = Db::name('cust_user')
            ->where('uid',$data['uid'])
            ->update(['money'=>$moneys]);
        if(!$takeout || !$usermo){
            Db::rollback(); //回滚事务
            $this->outPut('', 1001, ":数据添加失败" );
        }else{
            Db::commit(); // 提交事务
            $this->jsonOut($data['money'],'提现申请成功');
        }
    }
    /**
     * 提现记录
     */
    public function BrankMoneyList(Request $request){
        $data = $request->post();
        if(empty($data['uid'])) $this->outPut('', 1001, ":缺少参数uid" );
        if(empty($data['bid'])) $this->outPut('', 1001, ":缺少参数bid" );
        $result = db('take_out')->where(['uid'=>$data['uid'],'bid'=>$data['bid']])
            ->order('takeid desc')
            ->select();
        foreach ($result as $k => $v){
            $result[$k]['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
        }
        $this->jsonOut($result,'提现申请成功');
    }
}























