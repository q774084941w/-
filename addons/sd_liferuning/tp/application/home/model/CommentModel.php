<?php
namespace app\home\model;

use think\Db;

class CommentModel
{
    /**
     * 单例模式（控制器调用 类名::instance()->方法名）
     * @return CommentModel|null
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
    public function commentlist($bid){
        $list = Db::name('comment')->alias('c')
            ->field('c.coid,c.uid,c.goodsid,c.content,c.isshow,c.sum_grade,c.createtime,c.replytime,c.reply,u.nickname,g.name')
            ->join('user u','c.uid = u.uid')
            ->join('goods g','c.goodsid = g.goodsid')
            ->where('c.bid',$bid)->paginate(10);
        return $list;
    }
    /**
     * 回复评论展示
     */
    public function adddata($id){
        $list = Db::name('comment')->alias('c')
            ->field('c.coid,c.uid,c.goodsid,c.ogid,c.content,c.isshow,c.sum_grade,c.createtime,c.reply,u.nickname,g.name')
            ->join('user u','c.uid = u.uid')
            ->join('goods g','c.goodsid = g.goodsid')
            ->where('c.coid',$id)->find();
        return $list;
    }
    /**
     * 评论展示
     */
    public function showdata($id){
        $list = Db::name('comment')->alias('c')
            ->field('o.name,o.phone,o.address,o.createtime as or_createtime,c.coid,c.uid,c.goodsid,c.ogid,c.content,c.isshow,c.sum_grade,c.pics,c.createtime,c.replytime,o.paytime,o.sendtime,o.taketime,o.remark,c.reply,c.reply_pics,u.nickname,g.name as gname,o.order_no,o.orderid,o.num,o.money')
            ->join('user u','c.uid = u.uid')
            ->join('goodsOrder o','c.ogid = o.orderid')
            ->join('goods g','c.goodsid = g.goodsid')
            ->where('c.coid',$id)->find();
        return $list;
    }
    /**
     * 添加数据库
     */
    public function updatecomm($data){
        $result = Db::name('comment')->update($data);
        return $result;

    }




}
