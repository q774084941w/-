<?php
namespace app\api\model;

use think\Db;

class TakeoutModel  
{
    /**
     * 单例模式
     * @return TakeoutModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new TakeoutModel();
        }
        return $m;
    }
    /**
     * 提现
     */
    public function MoneySubmit($data,$uid){
        $uidmoney = Db::name('user')->field('uid,balance')->where('uid',$uid)->find();
        if($uidmoney['balance'] < $data['money']) return [false,7001];   // 金额不足
        $data['status'] = 0;
        $data['createtime'] = time();
        $data['uid'] = $uid;
        Db::startTrans();
        $moneys = 0;
        $moneys = $uidmoney['balance'] - $data['money'];
        $takeout = Db::name('takeOut')->insert($data);
        $usermo = Db::name('user')->update(['uid'=>$uid,'balance'=>$moneys]);
        if (!$takeout || !$usermo) {
            Db::rollback(); //回滚事务
            return [false,0];
        }
        Db::commit();
        return [$data['money'],1];
      
    }


    /**
     * 提现记录
     */
    public function Moneyrecord($uid,$bid){
        $result = Db::name('takeOut')->field('takeid,uid,bid,money,name,alipay,status,createtime,verifytime')->where(['bid'=>$bid,'uid'=>$uid])->order('createtime desc')->select();
        foreach ($result as &$valuel){
            $valuel['createtime'] = date('Y-m-d H:i:s',$valuel['createtime']);
            $valuel['verifytime'] == 0 ? $valuel['verifytime'] = '' : date('Y-m-d H:i:s',$valuel['verifytime']);
        }
        return $result;
    }
    

}