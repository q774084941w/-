<?php
namespace app\api\controller;
use app\home\model\CardModel;
use think\Controller;
class Card extends Controller{
    public function index($bid){
        $data=CardModel::CardList($bid);
        $this->jsonOut($data);
    }
    public function wxAdd($cardnumber,$cid,$name,$uid){
      if(request()->isPost()){

          $data['cardnumber']=$cardnumber;
          $data['cid']=$cid;
          $data['name']=$name;
          $data['uid']=$uid;
          $result=CardModel::wxAdd($data);
          $this->jsonOut($result);
      }
    }
    public function wxlist($uid){
        $data=CardModel::wxlist($uid);
        $this->jsonOut($data);
    }
    public function Defaults($id,$uid){
        $data=CardModel::Defaults($id,$uid);
        $this->jsonOut($data);
    }
    public function del($id){
        $result=db('CardForm')->where('id',$id)->delete();
        $this->jsonOut($result);
    }
    public function CardDefault($uid){
        $data=CardModel::CardDefault($uid);
        if($data){
            $this->jsonOut($data);
        }else{
            $this->outPut('',0,'没有设置默认银行卡');
        }
    }

    /**
     * 跑腿端银行卡列表
     */
    public function cardlist(){
        $uid = input('uid');
        $bid = input('bid');
        $list = db('card_form')->alias('cf')->join('card c','cf.cid=c.id','left')
            ->field('c.*,cf.id as cfid,cf.cardnumber,cf.uid,cf.name,cf.cid,cf.click')
            ->where(['cf.uid'=>$uid])
            ->where(['c.bid'=>$bid])
            ->order('click desc')
            ->select();
        foreach ($list as $k =>$v){
            $list[$k]['pic'] = uploadpath('card',$v['pic']);
        }
        $this->jsonOut($list);
    }
    /**
     * 跑腿端设置默认银行卡
     *
     */
    public function cardmoren(){
        $id = input('id');
        $uid = input('uid');
        $card = db('card_form')->where('uid',$uid)->select();
        foreach ($card as $k=>$v){
            if($v['id'] == $id){
                db('card_form')->where('id',$v['id'])->update(['click'=>1]);
            }else{
                db('card_form')->where('id',$v['id'])->update(['click'=>0]);
            }
        }
        $this->jsonOut(1);
    }
}


?>