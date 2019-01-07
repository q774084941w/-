<?php
namespace app\api\model;

use think\Db;

class UserModel  
{
    /**
     * 单例模式
     * @return UserModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new UserModel();
        }
        return $m;
    }
    /**
     * 登录
     */
    public function login_data($goodsid){
        $loginId  = Db::name('business')->where(['bid'=>$goodsid,'status'=>1])->field('appid','secret')->select();
        echo $loginId;
    }
/**
     * 用户列表
     */
    public function userlist($uid){
        $list  = Db::name('user')->where(['uid'=>$uid])->field('uid,money,integral')->find();
        return $list;
    }
  /**
   * 导航
   */
    public function navList($bid){
        $navigation=db('Wxnav')->where(['bid'=>$bid,'navtype'=>'navigation'])->value('value');
        if(empty($navigation)){
            return $this->IntList();
        }
        $navigation=json_decode($navigation,1);
        $data=db('Wxnav')->where(['bid'=>$bid,'navtype'=>'navbar'])->select();
        $result=[];
        $result['navbar']=[];
        foreach ($data as $key=>$val){
            $result['navbar'][]=json_decode($val['value'],1);
        }
        $result=array_merge($result,$navigation);

        return $result;

    }
    public function IntList(){
        $url=config('uploadPath').'icon/';
        $data=[
            'backgroundColor'=>'#ffffff',          //背景色
            'frontColor'=>'#000000'                //文字颜色
        ];
        $data['navbar'][0]=[
            'text'=>'跑腿',
            'active_color'=>'#20AD20',
            'icon'=>$url.'icon1.png',
            'active_icon'=>$url.'icon1-1.png',
            'url'=>'/sd_liferuning/pages/constmer/index/index',
            'open_type'=>'redirect'
        ];
        $data['navbar'][1]=[
            'text'=>'订单',
            'active_color'=>'#20AD20',
            'icon'=>$url.'icon3.png',
            'active_icon'=>$url.'icon3-1.png',
            'url'=>'/sd_liferuning/pages/constmer/order-list/index',
            'open_type'=>'redirect'
        ];
        $data['navbar'][2]=[
            'text'=>'个人',
            'active_color'=>'#20AD20',
            'icon'=>$url.'icon2.png',
            'active_icon'=>$url.'icon2-1.png',
            'url'=>'/sd_liferuning/pages/constmer/user/index',
            'open_type'=>'redirect'
        ];
        return $data;
    }
    public static function tagadd($id,$uid){
        $data=db('User')->where('uid',$uid)->value('like');
        $data!=''?$data=explode(',',$data):$data=[];
        array_push($data,$id);
        $data=array_unique($data);
        $data=implode(',',$data);
        $result=db('User')->where('uid',$uid)->update(['like'=>$data]);
        return $result;
    }
    public static function tagdel($id,$uid){
        $data=db('User')->where('uid',$uid)->value('like');
        $data!=''?$data=explode(',',$data):$data=[];
        unset($data[$id]);
        $data=array_unique($data);
        $data=implode(',',$data);
        $result=db('User')->where('uid',$uid)->update(['like'=>$data]);
        return $result;
    }
    /*
     * 会员折扣
     */
    public function get_vip($bid,$uid){
        $filed='135k_member.zhekou';
        $list=Db::name('user')->field($filed)->join('135k_member','135k_user.member_grade=135k_member.member_grade')->where('135k_user.bid',$bid)
            ->where('135k_user.uid',$uid)->find();
        return $list;
    }
}
