<?php
namespace app\home\model;
use think\model;
use app\home\model\CommonModel;
class CardModel {
    /**
     * @param $data
     * @return int|string
     * 添加银行卡类型
     */
    public static function add($data){
        $data['pic']=CommonModel::instance()->upload('card');
        $result=db('Card')->insert($data);
        return $result;
    }
    /**
     * 添加银行卡类型列表
     */
    public static function CardList($bid){
        $result=db('Card')->where(['bid'=>$bid,'status'=>1])->order('id desc')->select();
        foreach ($result as $key=>$val){
            $result[$key]['pic']=uploadpath('card',$val['pic']);
        }
        return $result;
    }
    /**
     * 添加银行卡类型详情
     */
    public static function info($id){
        $result=db('Card')->where('id',$id)->order('id desc')->find();
        $result['pic']=uploadpath('card',$result['pic']);
        return $result;
    }
    /**
     * 添加银行卡类型修改
     */
    public static function edit($data){
        if(!empty($_FILES['image']['tmp_name'])){
            $data['pic']=CommonModel::instance()->upload('card');
        }
        $result=db('Card')->where('id',$data['id'])->update($data);
     
        return $result;
    }

    /**
     * @param $data
     * @return int|string
     * 微信端添加银行卡
     */
    public static function  wxAdd($data){
        $result=db('CardForm')->insert($data);
        return $result;

    }

    /**
     * @param $uid
     * @return mixed
     * 微信端银行卡列表
     */
    public static function wxlist($uid){
        $field='a.id,a.click,a.cardnumber,c.uname,c.pic';
        $result=db('CardForm')->alias('a')
            ->join('Card c','c.id=a.cid')
            ->field($field)
            ->where('a.uid',$uid)
            ->order('a.click desc')
            ->order('a.id desc')
            ->select();
        foreach ($result as $key=>$val){
            $result[$key]['pic']=uploadpath('card',$val['pic']);
        }
        return $result;

    }

    /**
     * @param $id
     * @param $uid
     * @return int|string
     * 微信端设置默认银行卡
     */
    public static function Defaults($id,$uid){
        db('CardForm')->where(['uid'=>$uid,'click'=>1])->update(['click'=>0]);
        $result=db('CardForm')->where('id',$id)->update(['click'=>1]);

        return $result;
    }

    /**
     * @param $uid
     * @return mixed
     * 微信端默认银行卡
     */
    public static function CardDefault($uid){
        $field='a.id,a.click,a.name,a.cardnumber,c.uname';
        $result=db('CardForm')->alias('a')
            ->join('Card c','c.id=a.cid')
            ->field($field)
            ->where('a.uid',$uid)
            ->where('a.click',1)
            ->find();

        return $result;
    }
}

?>