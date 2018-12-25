<?php
namespace app\home\model;

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
     * 用户列表
     */
    public function userlist($bid,$where=[],$where1=''){
        $list = Db::name('user')->field('uid,bid,phone,address,nickname,sex,birthday,regtime,integral,step,member_grade,money,tel')->where('bid',$bid)->where($where)->where($where1)->order('uid desc')->paginate(10);
        return $list;
    }
    /**
     * 用户详情
     */
    public function details($uid){
        $result = Db::name('user')->field('uid,bid,phone,nickname,name,step,head,sex,birthday,address,regtime,vip,viptime,login_status')->where('uid',$uid)->find();
        return $result;
    }

    /**
     * 车手审核
     */
    public function details12($uid){
        $result = Db::name('CustUser')
            -> alias('c')
            -> join('user u','c.uid=u.uid')
            ->field('c.*,u.nickname,u.head')
            ->where('c.uid',$uid)
            ->find();
        return $result;
    }

    /**
     *  商家审核
     */
    public function details13($uid){
        $result = Db::name('CustSeller')
            -> alias('c')
            -> join('user u','c.uid=u.uid')
            ->field('c.*,u.nickname,u.head')
            ->where('c.uid',$uid)
            ->find();
        return $result;
    }

    /**
     * 会员详情
     */
    public function detailss($id){
        $result = Db::name('member')->where('id',$id)->find();
        return $result;
    }
    /**
     * 会员详情
     */
    public function recharge($uid,$number){
        return Db::name('user') -> where('uid',$uid) -> setInc('money',$number);
    }
    /**
     * 会员详情
     */
    public function bond($id,$number,$type=1){
        switch ($type)
        {
            case 2:
                return Db::name('CustUser')
                    -> where('cid',$id)
                    -> setInc('money',$number);
                break;
            default:
                $result  = Db::name('CustUser') ->  where('cid',$id) -> field('promisemoney') -> find();
                if ($result<=400)
                {
                    return Db::name('CustUser')->where('cid',$id)->update(['status'=>-1]);
                }
                return Db::name('CustUser')
                    -> where('cid',$id)
                    -> setDec('promisemoney',$number);
        }
		return false;
    }



    /**
     * 会员列表
     */
    public function memberlist($bid){
        $list = Db::name('user')->field('uid,bid,phone,address,nickname,sex,birthday,viptime,vip,member_grade')->where('bid',$bid)->where('step',1)->paginate(10);
        return $list;
    }
    /**
     * 会员等级列表
     */
    public function memberlists($bid){
        $list = Db::name('member')->where('bid',$bid)->paginate(10);
        return $list;
    }

    /**
     * 跑腿评论
     */
    public function hisComment($id){
        $where = array(
            'rid' => $id,
        );
        $last_month = Db::name('runorder')
            -> whereTime('comment_time','last month')
            -> where($where)
            -> field('num_star')
            -> select();
        return $this -> takeTheArray($last_month);
    }

    /**
     * 计算上月的差好评总数
     */
    protected function takeTheArray($array){
        if (!empty($array)) {
            $lost = 0;
            $vin = 0;
            foreach ($array as $val) {
                switch ($val['num_star']) {
                    case 1:
                        $lost++;
                        break;
                    default:
                        $vin++;
                }
            }

            return array('lost'=>$lost,'vin'=>$vin);

        } else {
          return false;
        }
    }

    /**
     * 跑腿用户详情
     */
    public function defaults($uid){

        $result = Db::name('user')
            -> alias('u')
            -> join('CustUser a','a.uid=u.uid')
            -> join('CustSeller b','b.uid=u.uid','LEFT')
            -> field('a.uid,bid,phone,nickname,name,step,head,sex,birthday,address,vip,viptime,regtime,a.*,b.uname as seller_uname,b.utel as seller_utel,b.uaddress as seller_uaddress,b.uimg as seller_uimg,b.status as seller_status')
            -> where('u.uid',$uid)
            -> find();
        return $result;
    }

    /**
     * 添加跑腿人员
     */
    public function add_cust($data){
        $result=Db::name('cust_user')->insert($data);
        return $result;
    }
  	public function save_cust($id,$data){
      $result=Db::name('cust_user')->where('cid',$id)->update($data);
      return $result;
    }
    public function TopEdit($data,$bid){
        $rs=db('Wxnav')->where(['bid'=>$bid,'navtype'=>'navigation'])->find();
        if($rs){
            db('Wxnav')->where(['bid'=>$bid,'navtype'=>'navigation'])->delete();
        }

        $save['bid']=$bid;
        $save['navtype']='navigation';
        $save['value']=json_encode($data);
        $save['createtime']=time();
        $result=db('Wxnav')->insert($save);


        return $result;

    }
    public function downNav($data,$bid){
        $rs=db('Wxnav')->where(['bid'=>$bid,'navtype'=>'navbar'])->find();
        if($rs){
            db('Wxnav')->where(['bid'=>$bid,'navtype'=>'navbar'])->delete();
        }
        if(!empty($data)){
            foreach ($data as $key=>$val){
                $save['bid']=$bid;
                $save['navtype']='navbar';
                $save['value']=json_encode($val);
                $save['createtime']=time();
                $dataAll[]=$save;
            }
            $result=db('Wxnav')->insertAll($dataAll);
        }else{
            $result=array();
        }
        return $result;
    }
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
    public function reset($bid){
        $result=db('Wxnav')->where('bid',$bid)->delete();
        return $result;
    }
    /**
     * 跑腿个中心
     */
    public static function userContent($bid){

        $url=config('uploadPath').'icon/user/';
        $data=[
            'user_center_bg'=>config('uploadPath').'/background/bg3.png',
            'menu_style'=>0,
            'top_style'=>0,
            'menu_list'=>[
                0=>[
                    'icon'=>$url.'icon1.png',
                    'id'=>'qianbao',
                    'name'=>'钱包',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/balance-management/index'
                ],
                1=>[
                    'icon'=>$url.'icon2.png',
                    'id'=>'youhui',
                    'name'=>'优惠券',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/coupon-management/index'
                ],
                2=>[
                    'icon'=>$url.'icon9.png',
                    'id'=>'dingdan',
                    'name'=>'我的订单',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/other-order-list/index'
                ],
                3=>[
                    'icon'=>$url.'icon3.png',
                    'id'=>'adds',
                    'name'=>'我的地址',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/address-list/index'
                ],
                4=>[
                    'icon'=>$url.'icon11.png',
                    'id'=>'vip',
                    'name'=>'会员中心',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/member-center/index'
                ],
                5=>[
                    'icon'=>$url.'icon10.png',
                    'id'=>'mess',
                    'name'=>'消息管理',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/message-management/index'
                ],
                6=>[
                    'icon'=>$url.'icon6.png',
                    'id'=>'pao',
                    'name'=>'加入跑腿',
                    'open_type' => 'navigator',
                    'url'=>'/service/pages/service/index/index'
                ],
                7=>[
                    'icon'=>$url.'icon12.png',
                    'id'=>'guanli',
                    'name'=>'模块管理',
                    'open_type' => 'navigator',
                    'url'=>'/service/pages/module-mananger/index/index'
                ],
                8=>[
                    'icon'=>$url.'icon7.png',
                    'id'=>'fankui',
                    'name'=>'意见反馈',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/advise/index'
                ],
                9=>[
                    'icon'=>$url.'icon8.png',
                    'id'=>'lingquan',
                    'name'=>'领券中心',
                    'open_type' => 'navigator',
                    'url'=>'/sd_liferuning/pages/constmer/lingjuan/index'
                ],
                10=>[
                    'icon'=>$url.'icon9.png',
                    'id'=>'phone',
                    'open_type' => 'tel',
                    'name'=>'联系客服',
                    'url'=>''
                ],


            ],
            'menus'=>[]
        ];
        $result=json_decode(db('Option')->where(['bid'=>$bid,'name'=>'app'])->value('value'),1);
        if(empty($result)){
            $data['menus']=$data['menu_list'];
            $result=$data;
        }else{
            $result['menu_list']=$data['menu_list'];
        }
        return $result;
    }
    /**
     * 个人中心
     */
    public static function userSave($data,$bid){
        $data['bid']=$bid;
        $data['name']='app';
        $data['time']=time();
        $rs=db('Option')->where(['bid'=>$bid,'name'=>'app'])->count();
        if($rs)db('Option')->where(['bid'=>$bid,'name'=>'app'])->delete();
        $result=db('Option')->insert($data);

        return $result;
    }
    /**
     * 标签修改添加
     */
    public static function TagSave($data,$bid){
        if(!empty($data['list'])){
            $add=array();
            foreach ($data['list'] as $key=>$val){
                if($val['id']==0){
                    array_push($add,['bid'=>$bid,
                        'tagname'=>$val['name']]);
                }
            }
            if($add!=null){
                $result=db('Usertag')->insertAll($add);
                if(empty($result))return false;
            }
        }
        if(!empty($data['del'])){
            $delSrt=implode($data['del'],',');
            $where['tagid']=['in',$delSrt];
            $rs=db('Usertag')->where($where)->delete();
            if(empty($rs))return false;
        }
        return true;

    }

    public function label ($name) {
        $data = array(
            'la_name' => $name,
            'creat_time' => time()
        );
        db('userLabel') -> insert($data);
    }
}
