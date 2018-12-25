<?php
namespace app\api\controller;

use app\api\model\CommentModel;
use think\Controller;
use think\Request;

class Comment extends Controller
{
   /**
    * 评论列表
    *
    */
    public function commentLists(Request $request){
        $goodsid = $request->get('goodsid');
        $where = [
            'c.goodsid' => $goodsid,
            'c.isshow' => 0
        ];
        $result = CommentModel::instance()->commlists($where);
        $this->jsonOut($result);

    }
    /**
     * 评论
     */
    public function commentdata(Request $request){
        $data = $request->post();
        if(empty($data['ogid'])) $this->outPut('', 1001, ":缺少订单id" );
        if(empty($data['content'])) $this->outPut('', 1001, ":缺少评论内容" );
        list($result,$code) = CommentModel::instance()->commentdata($data,$this->uid);
        if($result == false)  $this->outPut(null,$code);
        if($result == 1){
            $this->jsonOut($result);
        }else{
            $this->outPut(null,0);
        }

    }
}
