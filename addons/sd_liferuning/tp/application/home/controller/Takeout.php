<?php
namespace app\home\controller;

use app\home\model\TakeoutModel;
use think\Controller;
use think\Request;
use app\api\model\OrderModel;
use think\Db;
class Takeout extends Controller
{
    public function _iniaialize(Request $request = null)
    {
        $result = $this->_getBid();
        if(!$result){
            $this->success('未登录', 'index/login');
        }
    }
    /**
     * 提现审核
     */
    public function index(){
        $bid = $this->_getBid();
        $list = TakeoutModel::instance()->recordposit($bid);
        
        return view('takeout/index',['data'=>$list]);
    }
    /**
     * 提现记录
     */
    public function record()
    {
        $bid = $this->_getBid();
        $list = TakeoutModel::instance()->deposit($bid);
        return view('takeout/record',['data'=>$list]);
    }
    /**
     * 提现审核操作  同意
     *
     */
    public function consent(Request $request){
        $id = $request->get('id');
        $result = TakeoutModel::instance()->taconsent($id);
        return $this->success('操作成功','takeout/record');
        return $result;
    }
    /**
     * 拒绝
     * 拒绝提现之后返还提现金额
     */
    public function refuse(Request $request){
        $id = $request->get('id');
        $result = TakeoutModel::instance()->tarefuses($id);
        return $this->success('操作成功','takeout/record');
        return $result;
    }
    /**
     * 保证金退款
     */
    public function price_out(){

        $field='a.*,u.uname,u.bank,u.bankname,u.bankaccount,u.cid,u.cashstatus';
        $data=db('Out_balance')->alias('a')->join('CustUser u','u.cid=a.cid')->field($field)->order('createtime desc')->paginate(10);
        return $this->fetch('',['data'=>$data]);
    }
    /**
     * 审核
     */
    /**
     * 审核状态
     */
    public function status($id,$status,$cid){
        if(db('Out_balance')->where('id',$id)->update(['status'=>$status,'updatetime'=>time()])){
            db('CustUser')->where('cid',$cid)->update(['cashstatus'=>0]);
            echo 1;
        }else{
            echo 0;
        }
    }
}
