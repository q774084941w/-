<?php
namespace app\api\controller;

use think\Cache;
use app\api\model\UserModel;
use think\Controller;
use think\Request;
use app\conmon\WXBizDataCrypt;
use think\Db;
use app\home\model\CommonModel;
use app\home\model\UserModel as HomeUserModel;
require_once('SmsTools.php');

class User extends Controller
{
    /**
     * 用户类
     *
     * 我的订单  type = 0:全部  -1:关闭  1:待付款  2:待收货  3:已完成
     */
    public function login(Request $request){
        $goodsid = $request->post();
        UserModel::instance()->login_data($goodsid);
    }
    public function userlogin($bid,$uid){
        $result=db('User')->where(['bid'=>$bid,'uid'=>$uid])->find();
        if($result){
            $cash=db('CustUser')->alias('a')->join('User u','u.uid=a.uid')->field('u.uid,u.phone,a.status,a.cashstatus,a.cid')->where(['u.bid'=>$bid,'u.uid'=>$uid])->find();

            if($cash){
                exit(json_encode(['code'=>2,'phone'=>$result['phone'],'cash'=>['phone'=>$result['phone'],'status'=>$cash['status'],'cashstatus'=>$cash['cashstatus'],'cid'=>$cash['cid']],'msg'=>'获取cash信息']));
            }else{
                $data=[
                    'uid'=>$result['uid'],
                    'tel'=>$result['phone'],
                    'createtime'=>time(),
                    'cashstatus'=>0,
                    'status'=>1
                ];
                $rs=db('CustUser')->insert($data);
                if($rs){
                    $this->userlogin($bid,$uid);
                }
            }
        }else{
            exit(json_encode(['code'=>0,'msg'=>'没有数据']));
        }

    }
    public static function removeEmoji($text) {
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }
    public function logins(){
        $content = file_get_contents ( 'php://input' );
        $content=json_decode($content,true);
//         $utoken=$content["utoken"];
        // echo $utoken;die;
        if(!empty($utoken)&&Cache($utoken)){
            $result["success"]=11;
            $result['utoken']=$utoken;
            echo json_encode($result);
            return;
        }
        $code=$content["code"];
        $bid = $content["bid"];
        $tel = $content["tel"];

        $encryptedData=$content["encryptedData"];
        $iv = $content['iv'];
        /*获取session_key*/
        $s_result=$this->getSession($code,$bid);
        $WxData = new WXBizDataCrypt($s_result['appid'],$s_result['session_key']);
        /*解密用户数据*/
        $errCode = $WxData->decryptData($encryptedData, $iv, $user_data);
        // $wxap_key = md5(uniqid(md5(microtime(true)),true));
        $result=array();
        if($errCode==0){
            $user_data=json_decode($user_data,true);
            $result["success"]=10;
            //  $result['utoken']=$wxap_key;
            $result['openid'] = $s_result['openid'];
            $user_id = $this->wxUserAdd($user_data,$bid,$tel);
//            $result['uid'] = $user_id['uid'];
            if($user_id < 1 || empty($user_id)){
                $result["success"]=-1;
                $result['errCode']=0;
                $result['msg']="获取用户信息出错！";
                echo json_encode($result);
                return;
            }
            $user_data['uid']=$user_id;
            //var_dump($user_data['uid']);
            //Cache::set($wxap_key,$user_data,7200);
            echo  json_encode($result);
            return;
        }else{
            $result["success"]=-1;
            $result['errCode']=$errCode;
            $result['msg']="获取用户信息出错！！";
            echo json_encode($result);
            return;
        }
    }

    /*code 换取 session_keysession_key*/
    public function getSession($code,$bid) {
        $loginId  = Db::name('business')->where(['bid'=>$bid])->field('appid,secret')->find();

        $s_data['appid'] = $loginId['appid'];
        $s_data['secret'] = $loginId['secret'];
        $s_data['js_code'] = $code;
        $s_data['grant_type']="authorization_code";
        $session_url = 'https://api.weixin.qq.com/sns/jscode2session?'.http_build_query ( $s_data );
        // echo $session_url.'<br/>';
        $content = file_get_contents ( $session_url );
        // echo $content;
        $content = json_decode ( $content, true );
        $content['appid']=$s_data['appid'];
        return $content;
    }
    public function upload(){

        if($src=CommonModel::instance()->upload('user')){
            exit(json_encode(['msg'=>'图片上传成功','code'=>1,'src'=>uploadpath('user',$src)]));
        }else{
            exit(json_encode(['msg'=>'图片上传失败','code'=>0]));
        }

    }

    /**
     * @param $bid
     * @return bool
     * 获取GetToken
     */
    public function GetToken($bid){

        $loginId  = Db::name('business')->where(['bid'=>$bid])->field('appid,secret')->find();
        $data['grant_type']='client_credential';
        $data['appid']=$loginId['appid'];
        $data['secret'] = $loginId['secret'];
        $url='https://api.weixin.qq.com/cgi-bin/token?'.http_build_query ( $data );

        $ch  = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $data = curl_exec($ch);
        $obj=json_decode($data);
        if(empty($obj->errcode)){
            $save['token']=$obj->access_token;
            $save['tokentime']=time();
            db('Mess')->where('bid',$bid)->update($save);
            return $obj->access_token;
        }else{
            return false;
        }




    }

    /**
     * @param $bid
     * @param $openid
     * @param $order_no
     * @param $type
     * 模板消息
     */
    function mess($bid,$openid='',$order_no,$type){
        $mess=Db::name('mess')->where(['bid'=>$bid])->find();
        $accessToken=$mess['token'];
        if(empty($accessToken)){
            $accessToken=$this->GetToken($bid);
        }
        if(($mess['tokentime']+6000)<time()){
            $accessToken=$this->GetToken($bid);
        }
        $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$accessToken}";
        if(json_decode(file_get_contents($url))->errcode==40001){
            $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$this->GetToken($bid)}";
        }

        $order=db('Runorder')->where('order_no',$order_no)->find();
        if(empty($openid)){
            $openid=db('User')->where('uid',$order['uid'])->value('openid');
        }
        if($type=='apply'){
            $data=array(
                'keyword1'  => array('value'=>'跑腿支付订单'),
                'keyword2'  => array('value'=>'等待接单'),
                'keyword3'  => array('value'=>$order['order_no']),
                'keyword4'  => array('value'=>$order['price'].'元'),
            );
        }
        if($type=='cancel'){
            $data=array(
                'keyword1'  => array('value'=>'跑腿支付订单'),
                'keyword2'  => array('value'=>$order['order_no']),
                'keyword3'  => array('value'=>$order['price'].'元'),
                'keyword4'  => array('value'=>date('Y-m-d H:i:s',$order['outtime'])),
            );
        }
        if($type=='order'){
            $phone=db('User')->where('uid',$order['rid'])->value('phone');
            $data=array(
                'keyword1'  => array('value'=>$order['order_no']),
                'keyword2'  => array('value'=>date('Y-m-d H:i:s',time())),
                'keyword3'  => array('value'=>$order['myadds']),
                'keyword4'  => array('value'=>$phone),
            );
        }
        $postData = array(

            "touser"        =>$openid,      //用户openid
            "template_id"   =>$mess[$type],  //模板消息ID
            "page"          =>'sd_liferuning/pages/constmer/order-info/index?orderid='.$order['id'],
            "form_id"       =>$order['prepay_id'],      //表单提交场景下，事件带上的 formId；支付场景下，为本次支付的 prepay_id
            "data"          =>$data,
            'emphasis_keyword'=>''
        );



        $postData =  json_encode($postData,JSON_UNESCAPED_UNICODE);



        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POST, 1);//post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
        $data = curl_exec($ch);
        $obj=json_decode($data);

        if(empty($obj->errcode)){
            exit(json_encode(['code'=>1,'msg'=>'执行成功']));
        }else{

            exit(json_encode(['code'=>0,'msg'=>$obj->errmsg]));

        }

    }

    /**
     * 管理员审核
     */
    public function attestation(){
        if(request()->isPost()){
            $data=input('post.');
            $save=[
                'uname'=>$data['uname'],
                'card'=>$data['card'],
                'cardimg'=>$data['cardimg'],
                'cardimgf'=>$data['cardimgf'],
                'carcardimg'=>$data['carcardimgs'],
                'carcodes'=>$data['carcodes'],
                'status'=>2,   //审核中
                'updatetime'=>time(),
                'is_status' => $data['usertype'] ? $data['usertype']: 1,
                'license' => $data['carcardImg'] ? $data['carcardImg']: '',
            ];
            if(db('CustUser')->where(['cid'=>$data['cid']])->update($save)){
                exit(json_encode(['code'=>1,'msg'=>'提交成功,请耐心等待管理员审核']));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'提交失败,未知错误']));
            }
        }
    }
    //商家审核
    public function sellerSave(){
        if(request()->isPost()){
            $data=input('post.');
            $save=[
                'uname'=>$data['uname'],
                'utel'=>$data['utel'],
                'uimg'=>$data['uimg'],
                'uid'=>$data['uid'],
                'uaddress'=>$data['uaddress'],
                'carname'=>$data['carname'],
                'addtime'=>time(),
                'status' => 0
            ];
            switch ($data['thisType']) {
                case 1:
                    if(db('CustSeller')->insert($save)){
                        exit(json_encode(['code'=>1,'msg'=>'提交成功,请耐心等待管理员审核']));
                    }else{
                        exit(json_encode(['code'=>0,'msg'=>'提交失败,未知错误']));
                    }
                    break;
                case 3:
                    if (empty($data['id'])) {
                        exit(json_encode(['code'=>0,'msg'=>'缺少重要参数']));
                    }
                    if(db('CustSeller') -> where(['id'=>$data['id']])->update($save)){
                        exit(json_encode(['code'=>1,'msg'=>'修改成功,请耐心等待管理员审核']));
                    }else{
                        exit(json_encode(['code'=>0,'msg'=>'修改失败,未知错误']));
                    }
                    break;
                default:
                    exit(json_encode(['code'=>0,'msg'=>'失败,未知错误']));
            }

        }
    }
    public function wxUserAdd($data,$bid,$tel=0){

        $t_user = db('user');
        $data1['openid'] = $data['openId'];
        //var_dump($data);
        //echo 1;
        //var_dump($user_info['user_id']);
        if($user_info = $t_user->where('openid',$data1['openid'])->find()){
            $data2['nickname'] = self::removeEmoji($data['nickName']);
            $data2['head'] = $data['avatarUrl'];
            if (!empty($tel))
            {
                $data2['tel'] = $tel;
            }
            $insert = $t_user->where('openid',$data1['openid'])->update($data2);
            return $user_info['uid'];
        }
        // if($user_info = $t_user->where('openid',$data1['openid'])->find())
        //echo 1;
        $data1['bid'] = $bid;
        $data1['nickname'] = self::removeEmoji($data['nickName']);
        $data1['sex'] = $data['gender'];
//        $data1['language'] = $data['language'];
        $data1['address'] = $data['city'];
//        $data1['province'] = $data['province'];
        $data1['head'] = $data['avatarUrl'];
        $data1['regtime'] = time();
        $data1['status'] = 1;
        if (!empty($tel))
        {
            $data1['tel'] = $tel;
        }
        //$insert_id =
        if($insert_id=$t_user ->insert($data1)){
            return  $insert_id;
        }else{
            return 0;
        }

    }
    //获取商家注册状态
   public function getSeller(){
        $uid = input('uid');
        if(empty(input('uid'))){
            echo json_encode(['data'=>'没有获取权限']);
        }else{
            $where = array(
                'uid' => $uid
            );
            $loginId  = Db::name('CustSeller')
                -> where($where)
                -> field('id,uname,utel,uimg,uaddress,status,carname')
                -> find();

            if(empty($loginId)){
                $loginId['id']=0;
            } else {
                $result =    Db::name('runorder')
                    -> where($where)
                    -> whereTime('oktime','m')
                    -> field('count(*) as number,sum(price) as price')
                    -> find();
                $loginId['number'] = $result['number'];
                  $loginId['price'] = round($result['price'],2);
            }
            echo json_encode($loginId);

        }
    }
  
  
    public function getUid() {
        $openid = input('param.openid');
//        var_dump($openid);
        if(empty(input('param.openid'))){
            echo json_encode(['data'=>'没有获取权限']);
        }else{
            $loginId  = Db::name('user')->where('openid',$openid)->field('uid,phone,head,nickname,sellerid')->find();

            echo json_encode(['data'=>$loginId['uid'],'phone'=>$loginId['phone'],'head'=>$loginId['head'],'nickname'=>$loginId['nickname'],'sellerid'=>$loginId['sellerid']]);
        }
    }
    public function reg($uid,$phone,$bid)
    {
        //var_dump($uid);die;
        if(db('User')->where(['phone'=>$phone,'bid'=>$bid])->find()){
            exit(json_encode(['code'=>0,'msg'=>'该手机号已绑定']));
        }
        $result=db('User')->where('uid',$uid)->update(['phone'=>$phone]);
        //var_dump($result);die;
        if($result){
            $rs=db('CustUser')->where('uid',$uid)->update(['tel'=>$phone]);
            if($rs){
                exit(json_encode(['code'=>1,'msg'=>'绑定成功','phone'=>$phone]));
            }
        }else{
            exit(json_encode(['code'=>0,'msg'=>'绑定失败']));
        }
    }
    //重置密码
    public function reset($phone,$password,$type,$bid){
        if($type==1){
            if(request()->isPost()){
                if(!$row=db('User')->where(['phone'=>$phone,'bid'=>$bid])->find()){
                    exit(json_encode(['msg'=>'用户不存在！','code'=>0]));
                }
                if(db('User')->where('uid',$row['uid'])->update(['password'=>sha1($password)])){
                    exit(json_encode(['msg'=>'恭喜你修改成功！','code'=>1]));
                }else{
                    exit(json_encode(['msg'=>'不能和旧密码相同','code'=>0]));
                }
            }
        }else{
            if(request()->isPost()){
                if(!$row=db('CustUser')
                    ->alias('c')
                    ->join('User u','c.uid=u.uid')

                    ->where(['tel'=>$phone,'bid'=>$bid])
                    ->field('c.*')
                    ->find()){
                    exit(json_encode(['msg'=>'用户不存在！','code'=>0]));
                }
                if(db('CustUser')->where(['cid'=>$row['cid']])->update(['pwd'=>sha1($password)])){
                    exit(json_encode(['msg'=>'恭喜你修改成功！','code'=>1]));
                }else{
                    exit(json_encode(['msg'=>'不能和旧密码相同','code'=>0]));
                }
            }
        }
    }
    public function checkcode (){
        //引入短信验证码
        echo 6666;
        exit();
        require_once "SmsSender.php";
        $phone = input('param.phone');
        $phones = db('user')->field('phone')->select();
        $test = 1;
        foreach ($phones as $k=>$v) {
            if( $phone === $v['phone']){
                $test=0;
            }
        }
        if($test == 0){
            echo json_encode(['res'=>0]);
        }
        //实例化
        if($test != 0){
            $sms = controller('Regist', 'controller');
            header("Content-type:text/html;charset=utf-8");
            $code = rand(1000, 9999);

            $str = "您好，你的验证码为code1，请于2分钟内填写。如非本人操作，请忽略本短信。";
            $strs = str_replace('code1', $code, $str);
            //发送
            $sms->reg($phone,$strs);

            if(!empty($phone)){
                echo  $code ;
            }
        }
    }
    /**
     * 用户基本信息
     */
    public function userlist(Request $request){
        $uid = $request->get('uid');
        $list = UserModel::instance()->userlist($uid);
        $this->jsonOut($list);
    }
    /**
     * 跑腿用户信息
     */
    public function userinfo($uid){

        $result=db('CustUser')->where('uid',$uid)->find();
        exit(json_encode(['code'=>1,'data'=>['phone'=>$result['tel'],'status'=>$result['status'],'cashstatus'=>$result['cashstatus'],'cid'=>$result['cid']]]));
    }

    public function userinfos($uid){
        $field='a.*,u.head,u.nickname,u.sex';
        $result=db('CustUser')
            ->alias('a')
            ->join('User u','u.uid=a.uid')
            ->field($field)
            ->where('a.uid',$uid)
            ->find();

        $result['sex']=$result['sex']==1?'男':'女';

        exit(json_encode(['code'=>1,'data'=>$result]));
    }
    /***
     * 跑腿人员简介修改
     */
    public function content($cid,$content=''){
        if(\request()->isPost()){
            if(db('CustUser')->where('cid',$cid)->update(['content'=>$content])){
                exit(json_encode(['code'=>1,'msg'=>'更新成功']));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'修改失败']));
            }

        }else{

            $content=db('CustUser')->where('cid',$cid)->value('content');
            exit(json_encode(['code'=>0,'content'=>$content]));


        }
    }
    /**
     * 保证金金额
     */
    public function balance($cid='',$bid=''){
        if($cid==''){
            $money=db('Goods')->where('bid',$bid)->value('min_price');
            exit(json_encode(['code'=>1,'money'=>$money]));
        }else{
            $money=db('CustUser')->where('cid',$cid)->value('promisemoney');
            exit(json_encode(['code'=>1,'money'=>$money]));
        }
    }
    /**
     * 用户悬赏已接单消息提示
     *
     */
    public function userXuanShang(Request $request){
        $bid = $request->post('bid');
        $uid = $request->post('uid');
        $data = db('runorder')->where(['uid'=>$uid,'bid'=>$bid,'status'=>2])->select();
        foreach ($data as $k => $v ){
            if($v['status']==2){
                $data[$k]['status'] = '你的订单'.$v['goodsname'].'已接单';
                $data[$k]['givetime'] = date('Y-m-d H:i:s',$v['givetime']);
            }
        }
        $this->jsonOut($data,'查询成功');
    }
    /**
     * 快捷标签 和 服务内容
     */
    public function ClassBiaoQian(Request $request){
        $bid = $request->get('bid');
        $id = $request->get('id');
        $data = db('service')->where(['id'=>$id,'bid'=>$bid])->find();
        $Arr = [];
        if($data['biaoqian']){
            $Arr = explode("，",$data['biaoqian']);
        }
        $data['biaoqian'] = $Arr;
        $data['three'] = db('three_class')->where(['bid'=>$bid,'pid'=>$id])->select();
        if($data){
            $this->jsonOut($data,'查询成功');
        }
    }
    /***
     * 跑腿银行卡提交
     */
    public function card_sub($cid){
        if(\request()->isPost()){
            $input=input('post.');
            $data=[
                'bank'=>$input['bank'],
                'bankname'=>$input['bankname'],
                'bankaccount'=>$input['bankaccount'],

            ];
            if(!$row=db('CustUser')->where('cid',$cid)->find()){
                exit(json_encode(['code'=>0,'msg'=>'用户不存在']));
            }
            if(db('CustUser')->where('cid',$cid)->update($data)){
                exit(json_encode(['code'=>1,'msg'=>'提交成功']));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'修改失败']));
            }
        }else{
            exit();
        }
    }
    /**
     * 资金管理
     */
    public function capital(Request $request){
        $uid = input('uid');
        $bid = input('bid');
        //用户余额
        $money = db('cust_user')->where(['uid'=>$uid])->value('money');
        if(!$money){
            $money = 0;
        }
        //今日收入
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));//当天开始的时间戳
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;//当天结束的时间戳、
        $time['givetime'] = array(array('>',$beginToday),array('<',$endToday));
        $daySum = db('runorder')
            ->where(['rid'=>$uid,'bid'=>$bid,'status'=>3])
            ->where($time)
            ->sum('f_price');
        if(!$daySum){
            $daySum = 0;
        }else {
            $daySum = round($daySum,2);
        }
        //完成订单数 和 金额
        $orderCompleteNum = db('runorder')->where(['rid'=>$uid,'bid'=>$bid,'status'=>3])->count();
        $orderCompletePrice = db('runorder')->where(['rid'=>$uid,'bid'=>$bid,'status'=>3])->sum('f_price');
        if(!$orderCompletePrice){
            $orderCompletePrice = 0;
        }
        //待收款订单数 和 金额
        $NoorderCompleteNum = db('runorder')->where(['rid'=>$uid,'bid'=>$bid,'status'=>2])->count();
        $NoorderCompletePrice = db('runorder')->where(['rid'=>$uid,'bid'=>$bid,'status'=>2])->sum('price');
        $choucheng=db('percent')->field('percent')->where(['bid'=>$bid])->find();
        if(empty($NoorderCompletePrice)){
            $NoorderCompletePrice = 0;
        }else{
            $NoorderCompletePrice=number_format($NoorderCompletePrice-$NoorderCompletePrice*($choucheng['percent']/100),2);
        }
        echo json_encode(['money'=>$money,'daySum'=>$daySum,'orderCompleteNum'=>$orderCompleteNum,'orderCompletePrice'=>$orderCompletePrice,'NoorderCompleteNum'=>$NoorderCompleteNum,'NoorderCompletePrice'=>$NoorderCompletePrice]);
    }
    /**
     * 银行卡调取
     */

    public function bank(Request $request){
        $cid = $request->post('cid');
        $field = '135k_card_form.cardnumber,135k_card_form.name,135k_card_form.cid,135k_cust_user.money';
        if(!empty($cid)){
            $data = db('CardForm')->join('135k_cust_user','135k_card_form.uid=135k_cust_user.uid')->where('135k_cust_user.cid',$cid)->field($field)->find();
            //var_dump($data);die;
            if($data['cardnumber'] && $data['name']){
                echo json_encode(['code'=>1,'data'=>$data]);
            }else{
                echo json_encode(['code'=>0,'msg'=>'银行卡信息不全']);
            }
        }else{
            echo json_encode(['code'=>1008,'msg'=>'网络错误，不存在']);
        }
    }
    /**
     * 可提现的金额
     */
    public function money(Request $request){
       $uid = $request-> param('uid');
        $bid = $request-> param('bid');
        $money = db('user')->where(['uid'=>$uid,'bid'=>$bid])->value('money');
        if($money){
            echo json_encode(['money'=>$money]);
        }
    }
    /**
     * 统计报表
     */
       public function statistics()
    {
        $uid=input("uid");
        $num=[];
        $arr7=[];
        $num7=[];
        $arr30=[];
        $num30=[];
        $price7=[];
        $price30=[];
        for($i = 6; $i >=0; $i--){
            $stringtime=date('Y-m-d', strtotime('-'.$i.' day'));
            $begintime=strtotime($stringtime);
            $arr7[].=substr($stringtime,5);
            $endtime=$begintime+86400;
            $sql="select count(id) num_day from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$begintime." and ".$endtime;
            $list=Db::query($sql);
            foreach ($list as $key=>$val){
                $num7[].=$val["num_day"];
            }
            $sqlp="select f_price from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$begintime." and ".$endtime;
            $listp=Db::query($sqlp);
            $nump=0;
            foreach ($listp as $key=>$val){
                $nump+=$val["f_price"];
            }
            $price7[].=$nump;
        }
        for($i = 29; $i >=0; $i--){
            $stringtime=date('Y-m-d', strtotime('-'.$i.' day'));
            $begintime=strtotime($stringtime);
            $arr30[].=substr($stringtime,5);
            $endtime=$begintime+86400;
            $sql="select count(id) num_day from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$begintime." and ".$endtime;
            $list=Db::query($sql);
            foreach ($list as $key=>$val){
                $num30[].=$val["num_day"];
            }
            $sqlp="select f_price from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$begintime." and ".$endtime;
            $listp=Db::query($sqlp);
            $nump=0;
            foreach ($listp as $key=>$val){
                $nump+=$val["f_price"];
            }
            $price30[].=$nump;
        }
        $beginToday=mktime(0,0,0,date('m'),date('d'),date('Y'));//今日开始
        $endToday=mktime(0,0,0,date('m'),date('d')+1,date('Y'))-1;//今日结束
        $start_week=mktime(0, 0, 0, date("m"), date("d") - date("w") + 1, date("Y"));
        $time=time();
        $sql="select count(id) num_day from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$beginToday." and ".$time;
        $list=Db::query($sql);
        $num["num_day"]=($list[0]["num_day"]);
        if($num["num_day"]==0){
            $num["price_day"]=0;
        }else{
            $sqlp="select f_price from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$beginToday." and ".$time;
            $listp=Db::query($sqlp);
            $nump=0;
            foreach ($listp as $key=>$val){
                $nump+=$val["f_price"];
            }
            $num["price_day"]=$nump;
        }
        //周
        $beginWeek = mktime(0,0,0,date('m'),date('d')-date('w')+1,date('Y'));
        $endWeek = mktime(23,59,59,date('m'),date('d')-date('w')+7,date('Y'));
        $time=time();
        $sql1="select count(id) num_week,sum(f_price) price_week from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$beginWeek." and ".$time;
        $list1=Db::query($sql1);
        $num["num_week"]=($list1[0]["num_week"]);
        if($num["num_week"]==0){
            $num["price_week"]=0;
        }else{
            $sqlp1="select f_price from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$beginWeek." and ".$time;
            $listp1=Db::query($sqlp1);
            $nump1=0;
            foreach ($listp1 as $key=>$val){
                $nump1+=$val["f_price"];
            }
            $num["price_week"]=$nump1;
        }
        //月
        $beginMonth=mktime(0,0,0,date('m'),1,date('Y'));
        $endMonth=mktime(23,59,59,date('m'),date('t'),date('Y'));
        $time=time();
        $sql2="select count(id) num_month,sum(f_price) price_month from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$beginMonth." and ".$time;
        $list2=Db::query($sql2);
        $num["num_month"]=($list2[0]["num_month"]);
        if($num["num_month"]==0){
            $num["price_month"]=0;
        }else{
            $sqlp2="select f_price from 135k_runorder where rid=".$uid." and oktime BETWEEN ".$beginMonth." and ".$time;
            $listp2=Db::query($sqlp2);
            $nump2=0;
            foreach ($listp2 as $key=>$val){
                $nump2+=$val["f_price"];
            }
            $num["price_month"]=$nump2;
        }
         
        //累计
        $time=time();
        $sql3="select count(id) num_total,sum(f_price) price_total from 135k_runorder where rid=".$uid;
        $list3=Db::query($sql3);
        $num["num_total"]=($list3[0]["num_total"]);
        if($num["num_total"]==0){
            $num["price_total"]=0;
        }else{
            $sqlp3="select f_price from 135k_runorder where rid=".$uid ;
            $listp3=Db::query($sqlp3);
            $nump3=0;
            foreach ($listp3 as $key=>$val){
                $nump3+=$val["f_price"];
            }
            $num["price_total"]=$nump3;
        }
        exit(json_encode(array("num"=>$num,"arr7"=>$arr7,"num7"=>$num7,"arr30"=>$arr30,"num30"=>$num30,"prcie7"=>$price7,"prcie30"=>$price30)));

    }
    /**
     * 导航
     */
    public function navlist($bid){
        $result=UserModel::instance()->navList($bid);
        exit(json_encode(['code'=>1,'data'=>$result]));
    }
    /**
     * 个人中心
     */
    public function UserMember($bid){
        $result=HomeUserModel::userContent($bid);
        unset($result['menu_list']);
        exit(json_encode(['code'=>1,'data'=>$result]));
    }
    /**
     * 标签管理
     */
    public function usertag($uid,$bid){
        $field='tagid as id,tagname as name';
        $data['tag_list']=db('Usertag')->field($field)->where('bid',$bid)->select();
        $data['my_tag']=db('User')->where('uid',$uid)->value('like');
        if($data['my_tag']!=null){
            $data['my_tag']=explode(',',$data['my_tag']);
        }else{
            $data['my_tag']=[];
        }
//        var_dump($data);
        exit(json_encode(['code'=>1,'data'=>$data]));

    }
    /**
     * 标签添加
     */
    public function tagadd($id,$uid){
        $result=UserModel::tagadd($id,$uid);
        if($result==1)exit(json_encode(['code'=>1,'msg'=>'新增成功']));
    }
    /**
     * 标签更改
     */
    public function tagdel($id,$uid){
        $result=UserModel::tagdel($id,$uid);
        if($result==1)exit(json_encode(['code'=>1,'msg'=>'删除成功']));
    }
    /**
     *个人标签
     */
    public function membertag($uid,$bid){

        $mytag=db('User')->where('uid',$uid)->value('like');
        $where['tagid']=['in',$mytag];
        $where['bid']=$bid;
        $tag=db('Usertag')->where($where)->column('tagname');
        $tagid=db('Usertag')->where($where)->column('tagid');
        if($mytag!=implode(',',$tagid)){
            db('User')->where('uid',$uid)->update(['like'=>implode(',',$tagid)]);
        }
        if($tag)exit(json_encode(['code'=>1,'data'=>$tag]));
    }
    /*
     * 会员折扣
     */
    public function get_vip(Request $request){

        $uid=$request->get('uid');
        $bid=$request->get('bid');
        if(empty($uid)){
            $this->outPut('','1002','缺少参数uid');
        }
        if(empty($bid)){
            $this->outPut('','1002','缺少参数bid');
        }
        $list=UserModel::instance()->get_vip($bid,$uid);
        $this->jsonOut($list);
    }
    /**
     * 获取二维码
     */
    /**
     * 生成二维码
     */
    public function qrcode($uid,$bid){
        $mess=Db::name('mess')->where(['bid'=>$bid])->find();
        $accessToken=$mess['token'];
        if(empty($accessToken)){
            $accessToken=$this->GetToken($bid);
        }
        if(($mess['tokentime']+2000)<time()){
            $accessToken=$this->GetToken($bid);
        }
        $data=json_encode([
            'scene'=>$uid,
            'width'=>450,
            'page'=>'sd_liferuning/pages/constmer/index/index'
        ]);
        $url='https://api.weixin.qq.com/wxa/getwxacodeunlimit?access_token='.$accessToken;
        $aurl = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$accessToken}";
        if(json_decode(file_get_contents($aurl))->errcode==40001){
            $url = "https://api.weixin.qq.com/cgi-bin/message/wxopen/template/send?access_token={$this->GetToken($bid)}";
        }
        $ch  = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $img = curl_exec($ch);
        $path=ROOT_PATH.'/public/uploads/qrcode/';
        if(!is_dir($path)){
            mkdir($path,0777);
        }
        $filename=$path.time().rand().'.jpg';
        $result=file_put_contents($filename,$img);
        if($result){
            $pic=db('RunRules')->where('bid',$bid)->value('poster');
            $pic==''?$imgbgsrc=ROOT_PATH.'/public/uploads/qrbg.jpg':$imgbgsrc=ROOT_PATH.'/public/uploads/poster/'.$pic;
            $rs=$this->qrimg($filename,$imgbgsrc);
            if($rs){

                exit(json_encode(['code'=>1,'src'=>$rs]));
            }else{
                exit(json_encode(['code'=>0,'msg'=>'error']));
            }
        }
    }

    /**
     * @param $filename
     * @return bool
     * GD库拼接二维码
     */
    public function qrimg($filename,$imgbgsrc){
        header('content-type:image/jpeg');
        $image=imagecreatefromjpeg($imgbgsrc);
        $qr=imagecreatefromjpeg($filename);
        $size=getimagesize($imgbgsrc);
        $width=$size[0];
        $height=$size[1];
        imagecopymerge($image, $qr, $width-450, $height-450, 0, 0, 450, 450,100);
        imagedestroy($qr);
        $name=time().mt_rand(1,1000);
        $qrname=ROOT_PATH.'/public/uploads/qrcode/'.$name.'.jpg';
        $rs=imagejpeg ($image,$qrname);
        imagedestroy($image);
        unlink($filename);

        if($rs){
            return config('uploadPath').'qrcode/'.$name.'.jpg';
        }else{
            return false;
        }
    }
    /**
     *添加留言
     */
    public function message(){
        $data = [];
        $data['bid'] = input('bid');
        $data['uid'] = input('uid');
        $data['content'] = input('message');
        $data['createtime'] = time();
        $res = Db::name('message')->insert($data);
        if ($res){
            return 1;
        }else{
            return 0;
        }
    }
    /**
     *获取悬赏金额
     */
    public function rewardmoney(Request $request){
        $ress = Db::name('membermoney')->field('money')->where(['id'=>2])->find();
        $this->jsonOut($ress['money']);
    }
    /***
     * 获取跑腿人员位置
     */
    public function getmap($longitude,$latitude,$bid){
        $field='a.longitude,a.latitude';
        $result=db('CustUser')->alias('a')->join('User u','u.uid=a.uid')->where(['a.status'=>3,'a.cashstatus'=>1,'a.longitude'=>['neq','','u.bid'=>$bid]])->field($field)->select();
        $data=[];
        foreach ($result as $key=>$val){
//            $km=calcDistance($latitude,$longitude,$val['latitude'],$val['longitude']);
//            if($km<10){
            $val['iconPath']='/sd_liferuning/resource/common/image/pao.png';
            $val['width']=30;
            $val['height']=30;
            $data[]=$val;


//            }
        }
        exit(json_encode(['code'=>1,'data'=>$data]));


    }
    public function insertOpenIdFormId (Request $request) {
        $data = $request->param();
        if($data){
           if ($data['formId']=="the formId is a mock one"){
                echo json_encode(['code'=>2,'msg'=>'请用真机测试']);
                exit;
            }
          
            if ($data['uid']) {
                $result=db('User')
                    ->where(['uid'=>$data['uid']])
                    ->field('openid')
                    ->find();
                $where = [
                    'uid'=>$data['uid'],
                ];
                $in = [
                   'open_id'  => $result['openid'],
                    'formId'  => $data['formId'],
                    'is_form' => 1
                ];
           
                $result = db('CustUser')->where($where)->update($in);
                // Order::sendMsg();    
                if($result){
                    exit(json_encode(['code'=>1,'msg'=>'搶單提醒開啟']));
                }else{
                    exit(json_encode(['code'=>-2,'msg'=>'失敗']));
                }
            } else {
                exit(json_encode(['code'=>0,'msg'=>'失敗']));
            }
        }

    }
 	   public function getOneFormId (Request $request) {
        $data = $request->param();
        if ($data) {

            if ($data['formId']=="the formId is a mock one"){
                echo json_encode(['code'=>2,'msg'=>'请用真机测试']);
                exit;
            }
                     $type = empty($data['type'])?'0':$data['type'];
            switch ($type) {
                case 2:
                    $result = db('user')
                        -> where(['uid'=>$data['uid']])
                        -> update(['formId2'=>$data['formId']]);
                    break;
                default:
                    $result = db('user')
                        -> where(['uid'=>$data['uid']])
                        -> update(['formId'=>$data['formId']]);
            }
            if ($result) {
                $this -> jsonOut($result);
            } else {
                exit(json_encode(['code'=>-2,'msg'=>'失敗']));
            }

        }
        exit(json_encode(['code'=>0,'msg'=>'失敗']));
    }
  
  /**
     * 账单提醒
     * @param Request $request
     */
    public function enough(Request $request){
        $uid          = $request-> param('uid');
        $bid          = $request-> param('bid');
        $pre_price    = $request-> param('pre_price');
        $theLastPrice = $request-> param('thisLastPrice');
        $money = db('user')->where(['uid'=>$uid,'bid'=>$bid])->value('money');
         if(!empty($money)){
            $type = 1;
            $msg = '';
            if ($money<$pre_price+$theLastPrice) {
                $type = 2;
                $msg  = '(餘額不足)';
            }
            $pre_price = $pre_price==''?'0.00':$pre_price;
            $data = array(
                "預計金額：{$pre_price}",
                "車手費用：{$theLastPrice}",
                "當前餘額：{$money}{$msg}",
            );
            echo json_encode(['code'=>1,'data'=>$data,'title'=>'支付詳情','type'=>$type]);
        } else {
            if ($money==0) {
                echo json_encode(['code'=>2,'msg'=>'您的餘額為0']);
            } else {
                echo json_encode(['code'=>0,'msg'=>'沒有查到數據']);
            }
         
        }
    }

}













