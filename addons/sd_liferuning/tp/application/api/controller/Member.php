<?php
namespace app\api\controller;

use app\api\model\MemberModel;
use app\api\model\OrderModel;
use think\Controller;
use think\Request;
use think\Db;

class Member extends Controller
{
    /**
     *会员首页显示
     */
    public function show(Request $request)
    {
        $uid = $request->post('uid');
        $res = Db::name('user')->alias('u')->join('member m', 'u.member_grade = m.member_grade')->field('u.step,u.member_grade,u.integral,u.hyintegral,m.pic')->where(['uid' => $uid])->find();
        if(!$res){
            $res['step'] = 0;
            $res['hyintegral'] = 0;
            $res['integral'] = 0;
            $this->jsonOut($res);
        }
        if ($res['step'] == 0) {
            if ($res['member_grade'] == 1) {
                $res['pic'] = 'https://xam.chaojiyuanma.com/addons/sd_liferuning/tp/public/uploads/member/level1.png';
            } else if ($res['member_grade'] == 2) {
                $res['pic'] = 'https://xam.chaojiyuanma.com/addons/sd_liferuning/tp/public/uploads/member/level2.png';
            } else if ($res['member_grade'] == 3) {
                $res['pic'] = 'https://xam.chaojiyuanma.com/addons/sd_liferuning/tp/public/uploads/member/level3.png';
            } else if ($res['member_grade'] == 4) {
                $res['pic'] = 'https://xam.chaojiyuanma.com/addons/sd_liferuning/tp/public/uploads/member/level4.png';
            } else if ($res['member_grade'] == 5) {
                $res['pic'] = 'https://xam.chaojiyuanma.com/addons/sd_liferuning/tp/public/uploads/member/level5.png';
            }
        }
        $this->jsonOut($res);
    }
    /**
     * 积分折扣
     */
    public function get_integral(Request $request)
    {
        $uid = $request->post('uid');
        $bid = $request->post('bid');
        $res = Db::name('user')->field('integral')->where(['uid' => $uid])->find();
        $ress = Db::name('run_rules')->field('jifen')->where('bid', $bid)->find();
        $resu = $res['integral'] / $ress['jifen'];
        $this->jsonOut(['integral' => $resu]);
    }
    /**
     *获取用户会员等级信息，单天是否签到
     */
    public function memberinfo(Request $request){
        $uid = $request->post('uid');
        $res = Db::name('user')->field('member_grade,lianxuday,integral,sign,signtime,hyintegral,step')->where(['uid' => $uid])->find();
        if ($res['step'] == '0') {
            $this->outPut('1001', null);
        }
        $reaa = $this->integral($res['lianxuday'], 100);
        $res['jifen'] = $reaa['jifen'];
        $ressign = date("Y-m-d", $res['signtime']);
        $today = strtotime(date("Y-m-d"), time());//获得当日凌晨的时间戳
        $end = $today + 60 * 60 * 24;//获得当日24点的时间戳
        $signs = strtotime($ressign) + 60 * 60 * 24;//获得某天24点的时间戳
        if ($res['signtime'] > $today && $res['signtime'] <= $end) {
            $this->jsonOut($res);
        } else {
            $res['msg'] = '今天未签到';
            $this->jsonOut($res);
        }
    }
    /**
     *获取会员规则明细
     */
    public function memberinfos(Request $request){
        $bid = $request->post('bid');
        $res = Db::name('member')->where(['bid'=>$bid])->field('member_grade,grade_name,quanyi,pic')->select();
        $this->jsonOut($res);
    }
    /**
     *获取会员续费价格
     */
    public function memberin(Request $request){
        $bid = $request->get('bid');
        $ress = Db::name('membermoney')->field('hymoney')->where(['bid'=>$bid])->find();
        $this->jsonOut($ress['hymoney']);
    }
    /**
     *会员签到
     */
    public function sign(Request $request){
        $uid = $request->post('uid');
        $bid = $request->post('bid');
        $res = Db::name('user')->field('signtime,sign,lianxuday,hyintegral')->where(['uid' => $uid])->find();
        $ressign = date("Y-m-d", $res['signtime']);
        $today = strtotime(date("Y-m-d"), time());//获得当日凌晨的时间戳
        $end = $today + 60 * 60 * 24;//获得当日24点的时间戳
        $signs = strtotime($ressign) + 60 * 60 * 24;//获得某天24点的时间戳
        if ($today - $signs >= 24 * 60 * 60) {// 判断时间是否大于24小时
            $ress['lianxuday'] = 1;
        } else {
            $ress['lianxuday'] = ++$res['lianxuday'];
        }
        $result['lianxuday'] = $ress['lianxuday'];
        $result['sign'] = ++$res['sign'];
        $result['signtime'] = time();
        $reaa = $this->integral($ress['lianxuday'], $res['hyintegral']);
        $result['hyintegral'] = $reaa['hyintegral'];
        $res = $this->addIntegral($result['hyintegral'], $bid);
        $result['member_grade'] = $res;
        $ress = Db::name('user')->where(['uid' => $uid])->update($result);
        $result['jifen'] = $reaa['jifen'];
        $this->jsonOut($result);
    }
    /**
     *增加积分和等级
     */
    public function addIntegral($hyintegral, $bid){
        $results = Db::name('member')->field('member_grade,growth')->where('bid', $bid)->select();
        foreach ($results as $k => $v) {
            $data = $v;
            $growth = $data['growth'];
            $member_grade = $data['member_grade'];
            if ($hyintegral >= $growth) {
                $result['member_grade'] = $member_grade;
            }
        }
        return $result['member_grade'];
    }
    /**
     *成长值的等级的变化
     */
    public function integral($lianxuday, $hyintegral){
        if ($lianxuday <= 3) {
            $reaa['jifen'] = 6;
        } elseif ($lianxuday > 3 && $lianxuday <= 8) {
            $reaa['jifen'] = 8;
        } elseif ($lianxuday > 8 && $lianxuday <= 15) {
            $reaa['jifen'] = 10;
        } elseif ($lianxuday > 15 && $lianxuday <= 24) {
            $reaa['jifen'] = 12;
        } elseif ($lianxuday > 25 && $lianxuday <= 31) {
            $reaa['jifen'] = 14;
        }
        $reaa['hyintegral'] = $hyintegral + $reaa['jifen'];
        return $reaa;
    }
    /**
     * 支付(购买，续费)会员
     */
    public function pay($money, $uid){
        if (empty($money)) $this->outPut(null, 1001, "money");
        $order = trade_no();
        $res_parms = OrderModel::instance()->rechargePay($money, $uid, $order);
        if (!$res_parms) {
            $this->outPut(null, 1001);
        }
        $result['order'] = $order;
        $ress = Db::name('user')->where(['uid' => $uid])->update($result);
        if ($ress) {
            $this->jsonOut($res_parms);
        }
    }
    /**
     * 支付成功回调修改数据库
     */
    public function study($uid){
        if (empty($uid)) $this->outPut(null, 1001, "uid");
        $result['step'] = 1;
        $result['viptime'] = time();
        $ress = Db::name('user')->field('vip,member_grade')->where(['uid' => $uid])->find();
        if ($ress['vip']) {
            $result['vip'] = strtotime('+1 months', $ress['vip']);
        } else {
            $result['vip'] = strtotime('+1 months', time());
        }
        if ($ress['member_grade']) {
            $result['member_grade'] = $ress['member_grade'];
        } else {
            $result['member_grade'] = 1;
        }
        $res = Db::name('user')->where(['uid' => $uid])->update($result);
        $this->jsonOut($res);
    }
    /**
     * 判断会员是否到期
     */
    public function viptime($uid, $bid){
        if (empty($uid)) $this->outPut(null, 1001, "uid");
        if (empty($bid)) $this->outPut(null, 1002, "bid");
        $ress = Db::name('user')->field('vip,hyintegral,updateviptime')->where(['uid' => $uid])->find();
        $vip = date("Y-m-d", $ress['vip']);//获得会员到期时间的0点日期
        $signss = strtotime($vip) + 60 * 60 * 24;//获得会员到期时间的24点的时间戳
        $today = strtotime(date("Y-m-d"), time());//获得当日凌晨的时间戳
        $end = $today + 60 * 60 * 24;//获得当日24点的时间戳
        if ($ress['updateviptime']) {
            $viptime = $ress['updateviptime'];
            $updateviptime = date("Y-m-d", $viptime);//获得某天0点的日期
            $signs = strtotime($updateviptime) + 60 * 60 * 24;//获得某天24点的时间戳
            $num = (($end - $signs) / 60 * 60 * 24) * 5;
        }
        $nums = (($end - $signss) / (60 * 60 * 24)) * 5;

        if (!$ress['vip']) {
            $result = '非会员用户';
            $this->jsonOut($result);
        } else if ($ress['vip'] < time()) {//会员到期
            if ($ress['hyintegral'] == 0) {
                $result = 2002;
                $this->jsonOut($result);
            } else if (5 >= $ress['hyintegral'] && $ress['hyintegral'] > 0) {
                if ($ress['updateviptime']) {
                    if ($num == 0) {
                        $result = '今天积分已扣除';
                        $this->jsonOut($result);
                    } else {
                        $res['hyintegral'] = 0;
                        $res['step'] = 0;
                        $res['updateviptime'] = time();
                    }
                } else {//相当于if($signss)
                    if ($nums == 0) {
                        $result = '今天积分已扣除';
                        $this->jsonOut($result);
                    } else {
                        $res['hyintegral'] = 0;
                        $res['step'] = 0;
                        $res['updateviptime'] = time();
                    }
                }
            } else if ($ress['hyintegral'] > 5) {
                if ($ress['updateviptime']) {
                    if ($num == 0) {
                        $result = '今天积分已扣除';
                        $this->jsonOut($result);
                    } else {
                        $res['hyintegral'] = $ress['hyintegral'] - $num;
                        $res['step'] = 0;
                        $res['updateviptime'] = time();
                    }
                } else {//相当于if($signss)
                    if ($nums == 0) {
                        $result = '今天积分已扣除';
                        $this->jsonOut($result);
                    } else {
                        $res['hyintegral'] = $ress['hyintegral'] - $nums;
                        $res['step'] = 0;
                        $res['updateviptime'] = time();
                    }
                }
            }
            $res = $this->addIntegral($res['hyintegral'], $bid);
            $res['member_grade'] = $res;
            $res = Db::name('user')->where(['uid' => $uid])->update($res);
            $this->jsonOut($res);
        } else {
            $result = '会员用户';
            $this->jsonOut($result);
        }
    }
}
