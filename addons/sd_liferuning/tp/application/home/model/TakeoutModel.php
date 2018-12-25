<?php
namespace app\home\model;

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
     * 提现记录
     */
    public function deposit($bid){
        $list = Db::name('takeOut')
            ->alias('t')
            ->join('135k_user u','u.uid = t.uid')
            ->field('t.*,u.nickname as uname')
            ->where('t.bid',$bid)
            ->order('createtime desc')
            ->paginate(15);
        $list->toArray();
        foreach($list as $k=>$v){
            $data = [];
            $data = $v;
            $data['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $v['verifytime'] != 0 ? $data['verifytime'] = date('Y-m-d H:i:s',$v['verifytime']) : $data['verifytime'] = '';
            if($v['status'] == 0){
                $data['status'] = '发起提现';
                $data['class'] = 'putaway';
            }elseif($v['status'] == 1){
                $data['status'] = '同意';
                $data['class'] = 'putaway';
            }elseif ($v['status'] == -1){
                $data['status'] = '拒绝';
                $data['class'] = 'sold';
            }
            $list->offsetSet($k,$data);
        }
        return $list;
    }
    /**
     * 提现操作
     */
    public function recordposit($bid){
        $result= Db::name('takeOut')
            ->field('takeid,createtime')
            ->where(['bid'=>$bid,'status'=>0])
            ->select();
        foreach ($result as $key=>$val){
            if(($val['createtime'] + 7*24*60*60) < time()){
                unset($val['createtime']);
                $val['status'] = -1;
                $val['verifytime'] = time();
                $records = $val;
                Db::name('takeOut')->update($records);
            }
        }

        $list = Db::name('takeOut')
            ->alias('t')
            ->join('135k_user u','u.uid = t.uid')
            ->field('t.takeid,t.uid,t.bid,t.money,t.name,t.alipay,t.status,t.createtime,t.verifytime,u.nickname as uname')
            ->where(['t.bid'=>$bid,'t.status'=>0])
            ->order('createtime desc')
        ->paginate(15);
        $list->toArray();
        foreach($list as $k=>$v){
            $data = [];
            $data = $v;
            $data['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $list->offsetSet($k,$data);
        }
        return $list;
    }
    /**
     * 提现审核操作  同意
     */
    public function taconsent($id){
        $result = Db::name('takeOut')->where('takeid',$id)->update(['status'=>1]);
        return $result;
    }
    /**
     * 拒绝
     * 拒绝提现之后返还提现金额
     */
    public function tarefuse($id){
        $takeOut = Db::name('takeOut')->field('takeid,uid,money')->where('takeid',$id)->find();
        Db::startTrans();
        $result = Db::name('takeOut')->where('takeid',$id)->update(['status'=>-1]);
        $user = Db::name('user')->where('uid',$takeOut['uid'])->setInc('balance',$takeOut['money']);
        if (!$result || !$user) {
            Db::rollback(); //回滚事务
            return [false,0];
        }
        Db::commit();
        return [true,1];
    }
    /**
     * 拒绝
     * 拒绝提现之后返还提现金额 跑腿端
     */
    public function tarefuses($id){

        $takeOut = Db::name('takeOut')->field('takeid,uid,money')->where('takeid',$id)->find();
        $msg=[
            'uid'=>$takeOut['uid'],
            'money'=>$takeOut ['money'],
            'paytype'=>'outpay',
            'msg'=>'拒绝退款退回余额',
            'createtime'=>time(),
            'type'=>2,
            'cust'=>1
        ];
        Db::startTrans();
        $rss=db('PriceMsg')->insert($msg);
        $result = Db::name('takeOut')->where('takeid',$id)->update(['status'=>-1]);
        $user = Db::name('cust_user')->where('uid',$takeOut['uid'])->setInc('money',$takeOut['money']);
        if (!$result || !$user || !$rss) {
            Db::rollback(); //回滚事务
            return [false,0];
        }
        Db::commit();
        return [true,1];
    }

}
