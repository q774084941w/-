<?php
namespace app\api\model;

use think\Db;

class CommentModel
{
    /**
     * 单例模式
     * @return CommentModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CommentModel();
        }
        return $m;
    }
    /**
     * 评论列表
     */
    public function commlists($where){
        $fieid = 'c.coid,c.uid,c.goodsid,c.ogid,c.content,c.sum_grade,c.express_grade,c.goods_grade,c.serve_grade,
        c.pics,c.isshow,c.reply,c.reply_pics,c.createtime,c.replytime,u.nickname,u.head';
        $list = Db::name('comment')->alias('c')->join('135k_user u','c.uid = u.uid')
            ->field($fieid)
            ->where($where)->select();
        foreach($list as $key=>&$val){
            if($val['pics']) $val['pics'] = explode(',',$val['pics']);
            if($val['reply_pics']) $val['reply_pics'] = explode('_-_',$val['reply_pics']);
            $val['head'] = uploadpath('user',$val['head']);
            $val['createtime'] = Date('Y/m/d',$val['createtime']);
        }
        return $list;
    }
    /**
     * 添加评论
     */
 public function commentdata($data,$uid){
        $ordergoods = Db::name('orderGoods')->field('ogid,orderid,goodsid')->where('ogid',$data['ogid'])->find();

        $order = Db::name('goodsOrder')->field('orderid,uid,status,paytime,taketime')->where(['uid'=>$uid,'orderid'=>$ordergoods['orderid'],'status'=>4])->find();


        $comment = Db::name('comment')->field('coid')->where(['uid'=>$uid,'ogid'=>$data['ogid']])->find();
        if(!empty($comment)) return [false,4003];
        if(!$order) return [false,4002];
        if(time() - $order['taketime'] >= 24*60*60*7) return [false,4001];
        $data['createtime'] = time();
        $result = Db::name('comment')->insert($data);
        return [$result,''];
    }

}
