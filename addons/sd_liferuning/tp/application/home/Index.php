<?php
namespace app\home\controller;

use app\home\model\IndexModel;
use think\Controller;
use think\Request;
use think\Session;
use think\Db;


class Index extends Controller
{
    /**
     * 登录
     */
//    public function login(){
//        return view('index/login');
//    }
    /**
     * 获取token
     */
    public function stati(){
        if (session('access_token') && session('expire_time') > time()) {

            return session('access_token');
        } else {
            $appid = "wxa834360f88210bea";
            $appsecret = "28bfcfcdb0bd24cb49d936d9402dc750";
            $session_url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=" . $appid . "&secret=" . $appsecret;
            // echo $session_url.'<br/>';
            $res = file_get_contents ( $session_url );
            $ress = json_decode($res);
            $access_token = $ress->access_token;
            //将重新获取到的access_token存到session里
            session('access_token',$access_token);
            echo 1;
            session('expire_time',time() + 3600);
            //echo session('access_token);exit();
            return $access_token;
        }
    }
    public function WeChatprogram($url,$data){
        //$token = $this->stati();
        //$url = 'https://api.weixin.qq.com/datacube/getweanalysisappiddailysummarytrend?access_token='.$token;
       // $data =  [  'begin_date' =>date("Y-m-d",strtotime("-1 day")),'end_date' =>date("Y-m-d",strtotime("-1 day"))];
        $datas = json_encode($data);
//        dump($datas);exit();
        $options = array(
            'http' => array(
                'method' => 'post',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' =>$datas,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        //return $result;
        $res = json_decode($result);
        return $res;
    }

    /**
     * 登录检测
     */
    public function checkLogin(Request $request){
        global $_W;
        $UserSession = Session::get('we7_account');
        if(empty($UserSession))
            header('Location:'.$_W['setting']['copyright']['url']);
        else
            //var_dump($UserSession['key']);die;
            $data = [
                'appid' => $UserSession['key'],
                'secret' => $UserSession['secret']
            ];
        $result = IndexModel::instance()->insertWei($data,$UserSession['uniacid']);
        Session::set('bus_bid',$result);
        return $this->fetch('index/index');
//        $map = $request->post();
//        if(!$map['name'] && !$map['password']) return 0;
//        $data['account'] = $map['name'];
//        $data['password'] = $map['password'];
//        $result = IndexModel::instance()->checkLogin($data);
//        if($result){
//            Session::set('bus_bid',$result['bid']);
//            Session::set('bus_name',$map['name']);
//            $this->success('登录成功', 'index/index');
//        }else{
//            $this->error('账号密码错误');
//        }
    }
    /**
     * 退出登录
     */
    public function quitLogin(){
        Session::delete('bus_bid');
        Session::delete('name');
        $this->success('退出成功', 'index/login');
    }
    /**
     * 概况分析
     * @param
     */
    public function index(){
        $result = $this->_getBid();
        if(!$result){
            $this->success('未登录', 'index/login');
        }
        $token = $this->stati();
        $url = 'https://api.weixin.qq.com/datacube/getweanalysisappiddailysummarytrend?access_token='.$token;
        $data =  [  'begin_date' =>date("Y-m-d",strtotime("-1 day")),'end_date' =>date("Y-m-d",strtotime("-1 day"))];
        $datas = json_encode($data);
        $options = array(
            'http' => array(
                'method' => 'post',
                'header' => 'Content-type:application/x-www-form-urlencoded',
                'content' =>$datas,
                'timeout' => 15 * 60 // 超时时间（单位:s）
            )
        );
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);
        //return $result;
        $res = json_decode($result);
            if($ress = Db::name('stitc')->where('times', $res->list[0]->ref_date)->find()){
               // $this->assign('stitc',$res->list[0]);
               // dump($res) ;
                $this->assign('stitc',$res->list[0]);
                $this->fetch('index/index');

            }else{
                Db::name('stitc')->insert([
                    'times'=>$res->list[0]->ref_date,
                    'open'=>$res->list[0]->visit_total,
                    'leijip'=>$res->list[0]->share_pv,
                    'leijiu'=>$res->list[0]->share_uv,
                ]);
            }
        $this->assign('stitc',$res->list[0]);
        $this->fetch('index/index');
        return view('index/index');
    }

    /**
     * 用户分析
     * @param
     */
//    public function getSearchDate(){
//        $date=date('Y-m-d');  //当前日期
//        $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
//        $w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
//        $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
//        $now_end=date('Y-m-d',strtotime("$now_start +6 days"));  //本周结束日期
//        $last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期
//        $last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期
//        //获取上周起始日期
//        $this->start_date = $last_start;
//        //获取本周起始日期
//        $this->end_date = $now_start;
//        $this->week_end_date = $last_end;
//        var_dump($this->start_date);
//    }
    public function user(){
        $result = $this->_getBid();
        if(!$result){
            $this->success('未登录', 'index/login');
        }
        //昨日
        $token = $this->stati();
        $url = 'https://api.weixin.qq.com/datacube/getweanalysisappiddailyvisittrend?access_token='.$token;
        $data = [  'begin_date' =>date("Y-m-d",strtotime("-1 day")),'end_date' =>date("Y-m-d",strtotime("-1 day"))];
        $res = $this->WeChatprogram($url,$data);
        //上周
//        $token = $this->stati();
//        $url = 'https://api.weixin.qq.com/datacube/getweanalysisappidweeklyvisittrend?access_token='.$token;
//        $date=date('Y-m-d');  //当前日期
//        $first=1; //$first =1 表示每周星期一为开始日期 0表示每周日为开始日期
//        $w=date('w',strtotime($date));  //获取当前周的第几天 周日是 0 周一到周六是 1 - 6
//        $now_start=date('Y-m-d',strtotime("$date -".($w ? $w - $first : 6).' days')); //获取本周开始日期，如果$w是0，则表示周日，减去 6 天
//        $now_end=date('Y-m-d',strtotime("$now_start +6 days"));  //本周结束日期
//        $last_start=date('Y-m-d',strtotime("$now_start - 7 days"));  //上周开始日期
//        $last_end=date('Y-m-d',strtotime("$now_start - 1 days"));  //上周结束日期
//        //获取上周起始日期
//        $this->start_date = $last_start;
//        //获取本周起始日期
//        $this->end_date = $now_start;
//        $this->week_end_date = $last_end;
////      var_dump(  $now_end);exit();
//        $data = ['begin_date' =>$last_start,'end_date' =>$last_end];
//        $ress = $this->WeChatprogram($url,$data);
//        var_dump($ress);
//         exit();
        if($ress = Db::name('yestoday')->where('times', $res->list[0]->ref_date)->find()){
            // $this->assign('stitc',$res->list[0]);
            // dump($res) ;
            $this->assign('stitc',$res->list[0]);
            $this->fetch('index/user');

        }else{
            Db::name('yestoday')->insert([
                'times'=>$res->list[0]->ref_date,          //时间
                'yestody'=>$res->list[0]->visit_uv_new,  //新用户
                'opens'=>$res->list[0]->session_cnt,     //打开次数
                'visit_uv'=>$res->list[0]->visit_uv,       //访问人数
                'stay_time_uv'=>$res->list[0]->stay_time_uv,         //停留时间
                'visit_pv'=>$res->list[0]->visit_pv,   //访问次数
            ]);
        }
        $this->assign('stitc',$res->list[0]);
       // $this->assign('week',$res->list[0]);
//        $this->fetch('index/user');
        return view('index/user');
    }

    /**
     * 访问分析
     * @param
     */
    public function visit(){
        return view('home/statistics/visit');
    }

    public function stics(){

       $data = Db::name('stitc')->limit(5)->field('times,open')->order('id desc')->select();

        return $data;
    }
    public function userStics(){
        $data = Db::name('yestoday')->limit(5)->field('times,visit_uv')->order('id desc')->select();
        return $data;
    }



}
