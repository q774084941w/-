<?php
namespace app\home\controller;

use app\home\model\CommonModel;
use app\home\model\MessageModel;
use app\home\model\UserModel;
use think\Controller;
use think\Request;
use think\Db;
use PHPExcel;
use PHPExcel_IOFactory;

class User extends Controller
{
    public function _initialize(Request $request = null)
    {
        $result = $this->_getBid();
        if(!$result){
            $this->success('未登录', 'induserindex.ex/login');
        }
    }
    /**
     * 用户类
     *
     */
    public function index(Request $request){
        $bid = $this->_getBid();
        $selet=$request->get('selet');
        $search=$request->get('search');
        $tel=$request->get('tel');
        //var_dump($bid);die;
        $where =[];
        $where1='';
        if(isset($selet)){
            if($selet != 3&&$selet!='null'){
                if ($selet != '') {
                    $where=array(
//                    'step'=>$selet
                        'a.la_id' => $selet
                    );
                }
            }

        }

        if(isset($search) && $search !='null'){
            $where1['nickname']=['like',"%$search%"];
        }
        if(isset($tel) && $tel !='null'){
            $where1['tel|phone']=['like',"%$tel%"];
        }

        $result = UserModel::instance()->userlist($bid,$where,$where1);
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $list=Db::name('runorder')->where('uid',$v['uid'])->select();

            $v['regtime'] == 0 ? $data['regtime'] = '' :$data['regtime'] = date('Y-m-d H:i:s',$v['regtime']);
            $data['count']=count($list);
            $result->offsetSet($k,$data);
        }
        $label = db('UserLabel')
            -> field('la_id,la_name')
            -> where(array('delete_time'=>1))
            ->select();


        return view('user/index',['data'=>$result,'label'=>$label]);

    }

    /**
     * 修改用户类型
     */
    public function changeLabel(Request $request) {
        $uid = $request->post('uid');
        $value = $request->post('value');
        if (!empty($uid) && !empty($value)) {
            $result = Db::name('user')
                -> where(['uid'=>$uid])
                -> update(['la_id'=>$value]);
            if ($result) {
                exit(json_encode(['code'=>1]));
            } else {
                exit(json_encode(['code'=>2]));
            }
        } else {
            exit(json_encode(['code'=>0]));
        }
    }

    /**
     * 详情
     */
    public function show(Request $request,$type=''){
        $id = $request->get('id');

        switch ($type) {
            case 2:
                $details = UserModel::instance()->defaults($id);
                //评论
                $details['comment'] = UserModel::instance() -> hisComment($id);
                break;
            case 12:
                $details = UserModel::instance()->details12($id);
                return view('user/show12',['data'=>$details]);exit;

                break;
            case 13:
                $details = UserModel::instance()->details13($id);
                return view('user/show13',['data'=>$details]);exit;

                break;
            default:
                $details = UserModel::instance()->details($id);
        }

//        print_r($details);
        $details['login_status'] = 0 ? $details['login_status'] = '未登录' : $details['login_status'] = '登录';
        $details['sex'] == 1 ? $details['sex'] = '男' : $details['sex'] = '女';
        $details['regtime'] == 0 ? $details['regtime'] = '' : $details['regtime'] = date('Y-m-d H:i:s',$details['regtime']);
        $details['vip'] == 0 ? $details['vip'] = '' : $details['vip'] = date('Y-m-d H:i:s',$details['vip']);
        $details['viptime'] == 0 ? $details['viptime'] = '' : $details['viptime'] = date('Y-m-d H:i:s',$details['viptime']);
        $details['step'] == 0 ? $details['step'] = '非会员' : $details['step'] = '会员';

        return view('user/show',['data'=>$details]);
    }

    /**
     * 充值
     */
    public function recharge(Request $request) {
        if ($request -> isPost()) {
            $uid = $request -> post('id');
            $number = $request -> post('number');
            $result = UserModel::instance()->recharge($uid,$number);
            if ($result) {
                MessageModel::index($uid,'转账充值',$number);
              echo json_encode(array('code'=>1));
            } else {
                echo json_encode(array('code'=>2));
            }
        } else {
            echo json_encode(array('code'=>0));
        }
    }

    /**
     * 扣费
     */
    public function bond(Request $request)
    {
        if ($request -> isPost()) {
            $uid    = $request -> post('id');
            $number = $request -> post('number');
            $type   = $request -> post('type');
            switch ($type)
            {
                case 2:
                    $msg  = "保证金奖励";
                    $paytype = 'outpay';
                    break;
                default:
                    $msg  = "保证金扣除";
                    $paytype = 'pay';
            }
            $result = UserModel::instance()->bond($uid,$number,$type);
            if ($result) {
                MessageModel::index($uid,$msg,$number,null,$paytype);
                  echo json_encode(array('code'=>1));
            } else {
                echo json_encode(array('code'=>2));
            }
        } else {
            echo json_encode(array('code'=>0));
        }
    }

    /**
     * 会员详情
     */
    public function shows(Request $request,$type=''){
        $id = $request->get('id');
        $details = UserModel::instance()->detailss($id);
//        print_r($details);
        $details['createtime'] == 0 ? $details['createtime'] = '' : $details['createtime'] = date('Y-m-d H:i:s',$details['createtime']);
        $details['stute'] == 1 ? $details['stute'] = '开启' : $details['stute'] = '关闭';
        return view('user/shows',['data'=>$details]);
    }

    /**
     * 商户用户审核
     */
    public function seller(){


        $result= Db('CustSeller')
            -> alias('c')
            -> join('User u','u.uid=c.uid')
            -> field('c.*,u.nickname')
            -> where(['c.status'=>0])
            -> order('addtime desc')
            -> paginate(10);


        return $this->fetch('',['data'=>$result]);
    }

    /**
     * 跑腿用户审核
     */
    public function user(){

        $bid = $this->_getBid();

        $result=Db('CustUser')
            -> alias('c')
            -> join('User u','u.uid=c.uid')
            -> where(['u.bid'=>$bid,'c.status'=>2])
            -> order('createtime desc')
            -> paginate(10);


        foreach($result as $k=>$v){
            $data = $v;
            $data['createtime']=date('Y-m-d H:i:s',$v['createtime']);
            $data['sex']=$v['sex']==1?'男':'女';
            $result->offsetSet($k,$data);
        }

        return $this->fetch('',['data'=>$result]);
    }
    /**
     * 跑腿用户
     */
    public function userindex(){
        $bid = $this->_getBid();
        $field='u.nickname,u.sex,c.*';
        //var_dump($bid);die;
        $result=Db('CustUser')->alias('c')->join('User u','u.uid=c.uid')->where(['u.bid'=>$bid,'c.status'=>['in','-1,3']])->field($field)->order('createtime desc')->paginate(10);

//        print_r($result);
        foreach($result as $k=>$v){
            $data = $v;
            $data['createtime']=date('Y-m-d H:i:s',$v['createtime']);
            $data['sex']=$v['sex']==1?'男':'女';
            $result->offsetSet($k,$data);
        }

        return $this->fetch('',['data'=>$result]);
    }
    /**
     * 审核状态
     */
    public function status($id,$status){

        $array = array(
            'status'=>$status,
            'updatetime' => time()
        );
        if(db('CustUser')->where('cid',$id)->update($array)){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 商家审核状态
     */
    public function seller_status($id,$status){

        $array = array(
            'status'=>$status,
            'updatetime' => time()
        );
        if(db('CustSeller')->where('id',$id)->update($array)){
            echo 1;
        }else{
            echo 0;
        }
    }
    /**
     * 保证金设置
     */
    public function money(){
        $bid=$this->_getBid();
        $goods=db('Goods')->where('bid',$bid)->count();
        if(empty($goods)){
            db('Goods')->insert(['bid'=>$bid,'name'=>'保证金']);
        };
        $money=db('Goods')->where('bid',$bid)->value('min_price');
        if(\request()->isPost()){
            $money=input('money');
            if($money<=0){
                return $this->error('保证金不能小于等于0');
            }
            if(db('Goods')->where('bid',$bid)->update(['min_price'=>$money])){
                return $this->success('更新成功');
            }else{
                return $this->success('error');
            }
        }else{
            return $this->fetch('',['money'=>$money]);
        }

    }
    /**
     * 图片上传
     */
    public function uploads(){
        $file=request()->file('file');
        $result=$file->move(ROOT_PATH.'/public/uploads/nav',randCode(6));
        if($result){
            $src=uploadpath('nav',$result->getFilename());
            return $src;
        }
    }
    /**
     * 添加会员等级
     */
    public function addmember(Request $request){
        $bid = $this->_getBid();
        $data = input('param.');
        $pic = $this->uploads();
        $data['pic'] = $pic;
        $data['bid'] = $bid;
        $data['createtime'] = time();
        $res = Db::name('member')->field('member_grade')->where(['member_grade'=>$data['member_grade']])->where(['bid'=>$bid])->find();
        if($res){
            $this->error('该会员已经存在，请去会员列表编辑', 'User/member');
        }else{
            $result = Db::name('member')->insert($data);
//        var_dump($result);die;
            if($result == 1){
                $this->success('添加成功', 'User/member');
            }else{
                $this->error('新增失败');
            }
        }
    }
    /**
     * 修改会员等级
     */
    public function editmember(Request $request){
        $data = input('param.');
        $pic = $this->uploads();
        $data['pic'] = $pic;
        $data['edittime'] = time();
        $result = Db::name('member')->where(['id'=>$data['id']])->update($data);
//        var_dump($result);die;
        if($result == 1){
            $this->success('修改成功', 'User/member');
        }else{
            $this->error('修改失败');
        }
    }
    //5.20新增	---小程序底部导航
    public function lists(){
        $bid = $this->_getBid();
        if(\request()->isPost()){
            $data=input('post.');
            $result=UserModel::instance()->TopEdit($data['navigation_bar_color'],$bid);
            if($result){
                if(!empty($data['navbar']['navs'])){
                    UserModel::instance()->downNav($data['navbar']['navs'],$bid);
                }else{
                    UserModel::instance()->downNav('',$bid);
                }
                exit(json_encode(['code'=>0,'msg'=>'更新成功']));
            }else{
                exit(json_encode(['code'=>1,'msg'=>'更新失敗']));
            }

        }else{


            return view('user/list');
        }

    }
    public function navlist(){
        $bid = $this->_getBid();
        $data=UserModel::instance()->navList($bid);
        exit(json_encode(['code'=>0,'data'=>$data]));
    }
    public function reset(){
        $bid = $this->_getBid();
        UserModel::instance()->reset($bid);
        return $this->success('更新成功');
    }

    //小程序--个人中心
    public function personal(){
        $bid = $this->_getBid();
        if(\request()->isPost()){
            $data=input('post.');
            $result=UserModel::userSave($data,$bid);
            if($result)exit(json_encode(['code'=>1,'msg'=>'更新成功']));
        }else return view('user/personal');

    }
    public function userAjax(){
        $bid = $this->_getBid();
        $result=UserModel::userContent($bid);
        exit(json_encode(['code'=>1,'data'=>$result]));
    }
    public function UserReset(){
        $bid = $this->_getBid();
        $result=db('Option')->where(['bid'=>$bid,'name'=>'app'])->delete();
        if($result){
            return $this->success('修改成功');
        }else{
            return $this->error('修改失败');
        }
    }
    /**
     * 图片上传
     */
    public function upload(){
        $file=request()->file('file');
        $result=$file->move(ROOT_PATH.'/public/uploads/nav',randCode(6));
        if($result){
            $src=uploadpath('nav',$result->getFilename());
            exit(json_encode(['code'=>0,'msg'=>'success','data'=>['url'=>$src]]));
        }
    }
    /*
     * 电话下单页面
     */
    public function telorder(Request $request){
        $uid=input('uid');
        $tel=input('tel');
        $nickname=input('nickname');
        return view('user/telorder',['uid'=>$uid,'tel'=>$tel,'nickname'=>$nickname]);
    }


    /*
     * 添加跑腿人员页面
     */
    public function cust(Request $request){
        $uid=input('uid');
        return view('user/cust',['uid'=>$uid]);
    }


    /*
     * 添加跑腿人员
     */
    public function add_cust(Request $request){
        $data=$request->post();
        $data['status']=3;
        $data['cardimg']=uploadpath('user',CommonModel::instance()->upload('user'));
        $data['createtime']=time();
        $data['cashstatus']=1;
      	$data['inside']=1;
        if($result=Db::name('cust_user')->where('uid',$data['uid'])->find()){
          
            if($result['status']==1||$result['status']== 1){
                $list=UserModel::instance()->save_cust($result['cid'],$data);
            }elseif ($result['status']==2){
                $list=UserModel::instance()->save_cust($result['cid'],$data);
            }elseif($result['status']==3){
                $this->error('该用户已经是跑腿人员！','user/index');
            }
        }else{
            $list=UserModel::instance()->add_cust($data);
        }
        if($list){
            $this->success('添加成功！','user/index');
        }else{
            $this->error('添加失败！');
        }
    }
    /*
     * 会员列表
     */
    public function memberList(){
        $bid = $this->_getBid();
//        var_dump($bid);die;
        $result = UserModel::instance()->memberlist($bid);
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $v['viptime'] == 0 ? $data['viptime'] = '' :$data['viptime'] = date('Y-m-d H:i:s',$v['viptime']);
            $v['vip'] == 0 ? $data['vip'] = '' :$data['vip'] = date('Y-m-d H:i:s',$v['vip']);
            $result->offsetSet($k,$data);
        }
        return view('user/memberList',['data'=>$result]);
    }
    /*
    * 会员金额页面显示
    */
    public function purchase(){
        $bid = $this->_getBid();
        $res = db::name('membermoney')->field('hymoney')->where(['bid'=>$bid])->find();
        return view('user/purchase',['data'=>$res]);
    }
    /*
    * 设置会员金额
    */
    public function memberMoney(Request $request){
        $bid = $this->_getBid();
        $money = $request->post('money');
//        var_dump($money);die;
        $res = db::name('membermoney')->where(['bid'=>$bid])->find();
        if($res){
            $res = db::name('membermoney')->where(['bid'=>$bid])->update(['hymoney'=>$money]);
            $this->success('修改成功!','user/purchase',['data'=>$money]);
        }else{
            $res = db::name('membermoney')->insert(['hymoney'=>$money,'bid'=>$bid]);
            $this->success('添加成功!','user/purchase');
        }

    }
    /*
     * 会员等级页面
     */
    public function member(){
        $bid = $this->_getBid();
        //var_dump($bid);die;
        $result = UserModel::instance()->memberlists($bid);
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $v['createtime'] == 0 ? $data['createtime'] = '' :$data['createtime'] = date('Y-m-d H:i:s',$v['createtime']);
            $v['stute'] == 0 ? $data['stute'] = '未开启' : $data['stute'] = '已开启';
            $result->offsetSet($k,$data);
        }
        return view('user/member',['data'=>$result]);
    }

    /**
     * 添加会员商品
     */
    public function memberset(){
        return view('user/memberset');
    }
    /**
     * 改变会员状态
     */
    public function states(Request $request){
        $data = $request->get();
        $res = db::name('member')->where(['id'=>$data['id']])->update(['stute'=>$data['stute']]);
        echo json_decode($res);
    }
    /**
     * 删除会员信息
     */
    public function del(Request $request){
        $data = $request->get();
        $res = db::name('member')->where(['id'=>$data['id']])->delete();
        echo json_decode($res);
    }
    /**
     * 编辑会员商品
     */
    public function updates(Request $request){
        $id = $request->get('id');
        $res = db::name('member')->where(['id'=>$id])->find();
//        var_dump($id);die;
        $res['id'] = $id;
        $res['stute'] == 0 ? $res['stute'] = '未开启' : $res['stute'] = '已开启';
        $res['createtime'] = date('Y-m-d H:i:s',$res['createtime']);
        return view('user/membersets',['data'=>$res]);
    }
    public function userTag(){
        if(\request()->isPost()){
            $bid=$this->_getBid();
            $field='tagname as name,tagid as id';
            $result=db('Usertag')->field($field)->where('bid',$bid)->select();
//            var_dump($result);
            exit(json_encode(['code'=>1,'data'=>$result]));
        }
        return view('');
    }
    public function TagSave(){
        if(\request()->isPost()){
            $bid=$this->_getBid();
            $data=input('post.');
            $result=UserModel::TagSave($data,$bid);
            if($result)exit(json_encode(['code'=>1,'msg'=>'更新成功']));
            exit(json_encode(['code'=>0,'msg'=>'更新失败']));
        }
    }

    /**
     * 会员信息导出
     */
    public function excels(){
        $bid = $this->_getBid();
        $result = Db::name('user')->field('uid,phone,address,nickname,sex,birthday,regtime,integral,step,member_grade,money')->where('bid',$bid)->select();

        foreach($result as $k=>$v){

            $list=Db::name('runorder')->where('uid',$v['uid'])->select();
            $result[$k]['regtime'] == 0 ? $result[$k]['regtime'] = '' :$result[$k]['regtime'] = date('Y-m-d H:i:s',$v['regtime']);
            $result[$k]['sex']=$v['sex']==1?"男":'女';
            $result[$k]['count']=count($list);
            $result[$k]['step']=$v['step']==0?'普通会员':'VIP'.$v['member_grade'].'会员';
        }
        $path = dirname(__FILE__); //找到当前脚本所在路径

        $PHPExcel = new PHPExcel();
        //        $PHPExcel_IOFactory = new PHPExcel_IOFactory();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("demo"); //给当前活动sheet设置名称
        $PHPSheet->setCellValue("A1", "ID")
            ->setCellValue("B1", "用户名")
            ->setCellValue("C1", "性别")
            ->setCellValue("D1", "手机")

            //->setCellValue("E1", "身份证号")
            ->setCellValue("E1", "地址")
            ->setCellValue("F1", "加入时间")
            ->setCellValue("G1", "身份")
            ->setCellValue("H1", "当前积分")
            ->setCellValue("I1", "当前余额")
            ->setCellValue("J1", "下单数");
        //       var_dump(count($data));
        //       exit;

        $d=2;

        foreach($result as $key=>$vo){

            $PHPSheet->setCellValue("A".$d,$vo['uid'])
                ->setCellValue("B".$d,$vo['nickname'])
                ->setCellValue("C".$d,$vo['sex'])
                ->setCellValue("D".$d,$vo['phone'])
                // ->setCellValue("E".$d,$vo['id_number'].' ')
                ->setCellValue("E".$d,$vo['address'])
                ->setCellValue("F".$d,$vo['regtime'])
                ->setCellValue("G".$d,$vo['step'])
                ->setCellValue("H".$d,$vo['integral'])
                ->setCellValue("I".$d,$vo['money'])
                ->setCellValue("J".$d,$vo['count']);

            $d++;
        }
        //        exit;
        //        $PHPSheet->setCellValue("A2","张三")->setCellValue("B2","2121");//表格数据
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007");
        ob_end_clean(); // Added by me
        ob_start(); // Added by me
        header('Content-Disposition: attachment;filename="会员信息'.date('Y-m-d',time()).'.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output",$path); //表示在$path路径下面生成demo.xlsx文件
    }
    /**
     * 跑腿会员信息导出
     */
    public function excel(){
        $bid = $this->_getBid();
        $field='u.nickname,u.sex,c.*';
        //var_dump($bid);die;
        $result=Db('CustUser')->alias('c')->join('User u','u.uid=c.uid')->where(['u.bid'=>$bid,'c.status'=>['in','-1,3']])->field($field)->order('createtime desc')->select();

        foreach($result as $k=>$v){

            $result[$k]['createtime']=date('Y-m-d H:i:s',$v['createtime']);
            $result[$k]['sex']=$v['sex']==1?'男':'女';
        }
        $path = dirname(__FILE__); //找到当前脚本所在路径

        $PHPExcel = new PHPExcel();
        //        $PHPExcel_IOFactory = new PHPExcel_IOFactory();
        $PHPSheet = $PHPExcel->getActiveSheet();
        $PHPSheet->setTitle("demo"); //给当前活动sheet设置名称
        $PHPSheet->setCellValue("A1", "ID")
            ->setCellValue("B1", "用户名")
            ->setCellValue("C1", "姓名")
            ->setCellValue("D1", "性别")

            //->setCellValue("E1", "身份证号")
            ->setCellValue("E1", "身份证号")
            ->setCellValue("F1", "身份证正面")
            ->setCellValue("G1", "身份证反面")
            ->setCellValue("H1", "缴纳保证金")
            ->setCellValue("I1", "账户余额")
            ->setCellValue("J1", "加入时间");
        //       var_dump(count($data));
        //       exit;

        $d=2;

        foreach($result as $key=>$vo){

            $PHPSheet->setCellValue("A".$d,$vo['uid'])
                ->setCellValue("B".$d,$vo['nickname'])
                ->setCellValue("C".$d,$vo['uname'])
                ->setCellValue("D".$d,$vo['sex'])
                // ->setCellValue("E".$d,$vo['id_number'].' ')
                ->setCellValue("E".$d,$vo['card'])
                ->setCellValue("F".$d,$vo['cardimg'])
                ->setCellValue("G".$d,$vo['cardimgf'])
                ->setCellValue("H".$d,$vo['promisemoney'])
                ->setCellValue("I".$d,$vo['money'])
                ->setCellValue("J".$d,$vo['createtime']);
            $d++;
        }
        //        exit;
        //        $PHPSheet->setCellValue("A2","张三")->setCellValue("B2","2121");//表格数据
        $PHPWriter = PHPExcel_IOFactory::createWriter($PHPExcel, "Excel2007");
        ob_end_clean(); // Added by me
        ob_start(); // Added by me
        header('Content-Disposition: attachment;filename="跑腿用户'.date('Y-m-d',time()).'.xlsx"');
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $PHPWriter->save("php://output",$path); //表示在$path路径下面生成demo.xlsx文件
    }
    /**
     *意见反馈
     */
    public function opinion(){
        $bid = $this->_getBid();
        $result=Db('message')->alias('m')->join('user u','u.uid = m.uid')->field('m.*,u.nickname')->where(['m.bid'=>$bid])->select();
        foreach($result as $k=>$v) {
            $result[$k]['createtime'] = date('Y-m-d H:i:m', $result[$k]['createtime']);
        }
        return view('user/opinion',['data'=>$result]);
    }
    /**
     *删除意见反馈
     */
    public function deletes($msid){
        $result=Db('message')->where(['msid'=>$msid])->delete();
        if($result){
            echo 1;
        }else{
            echo 0;
        }
//        return view('user/opinion',['data'=>$result]);
    }


    public function Label (Request $request) {
        if ($request -> isPost()) {
            $data = $request -> param();
            if (!empty($data['type'])) {
                switch ($data['type']) {
                    case 1:
                        $result =  UserModel::instance() -> label ($data['number']);
                        break;
                    case 2:
                        $result =  UserModel::instance() -> updateLabe ($data['id'],$data['name']);
                        break;
                    default:

                }
                if ($result) {
                    exit(json_encode(array('code'=>1)));
                } else {
                    exit(json_encode(array('code'=>0)));
                }
            }


        exit;
        }

        $data = Db('userLabel') -> select();
        $this -> assign('data',$data);
        return view();
    }
    
}
