<?php
namespace app\home\controller;

use app\home\model\FreightModel;
use phpDocumentor\Reflection\DocBlock\Tags\Return_;
use think\Controller;
use think\Request;

class Freight extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 运费模板展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $bid = $this->_getBid();
        $data = FreightModel::instance()->show($bid);
        foreach ($data as &$val){
            if($val['status'] == 1){
                $val['status'] = '正常';
            }elseif ($val['status'] == 0){
                $val['status'] = '关闭';
            };
            $val['createtime'] == 0 ? $val['createtime'] = '' :$val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $val['handletime'] == 0 ? $val['handletime'] = '' :$val['handletime'] = date('Y-m-d H:i:s',$val['handletime']);
        }
        return view('freight/index',['data'=>$data]);
    }
    /**
     * 运费模板删除
     */
    public function delFreight(Request $request){
        $type = $request->post();
        $result = db('freight')->where('freid',$type['goodsid'])->delete();
        echo 1;
    }
    /**
     * 距离模板展示
     * @return \think\response\View
     *
     */
    public function distance()
    {
        $bid = $this->_getBid();
        $data = FreightModel::instance()->distance($bid);
        foreach ($data as &$val){
            if($val['status'] == 1){
                $val['status'] = '正常';
            }elseif ($val['status'] == 0){
                $val['status'] = '关闭';
            };

            $val['createtime'] == 0 ? $val['createtime'] = '' :$val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
            $val['handletime'] == 0 ? $val['handletime'] = '' :$val['handletime'] = date('Y-m-d H:i:s',$val['handletime']);
        }
        return view('freight/distance',['data'=>$data]);
    }
    /**
     * 编辑
     */
    public function edit(Request $request){
        $id = $request->get('id');
        $data = FreightModel::instance()->showdata($id);
        return view('freight/edit',['data'=>$data]);
    }

    /**
     * 运费模板添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        return view('freight/add');
    }
    /**
     * 距离运费模板添加
     * @return \think\response\View
     *
     */
    public function dadd()
    {
        return view('freight/dadd');
    }
    /**
     * 详情
     * @return \think\response\View
     *
     */
    public function show(Request $request)
    {
        $id = $request->get('id');
        $data = FreightModel::instance()->showdata($id);
        $data['unit'] == 0 ? $data['unit'] = '重量' : $data['unit'] = '件数';
        $data['createtime'] = date('Y-m-d H:is',$data['createtime']);
        return view('freight/show',['data'=>$data]);
    }
    /**
     * 距离模板详情
     * @return \think\response\View
     *
     */
    public function dshow(Request $request)
    {
        $id = $request->get('id');
        $data = FreightModel::instance()->dshowdata($id);
        $data['unit'] == 0 ? $data['unit'] = '重量' : $data['unit'] = '件数';
        $data['createtime'] = date('Y-m-d H:is',$data['createtime']);
        return view('freight/show',['data'=>$data]);
    }
    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){
        $bid = $this->_getBid();
        $data = $request->post();
        if($data['city']){
            $data['createtime'] = time();
            $data['bid'] = $bid;
            $res = FreightModel::instance()->insert($data);
            if($res == 1){
                $this->error('只能添加一条数据','freight/index');
            }
            $this->success('添加成功', 'freight/index');
        }else{
            $this->error('未选择配送地址');
        }


    }
    /**
     * 距离模板添加数据库
     * @param Request $request
     */
    public function dinsert(Request $request){
        $bid = $this->_getBid();
        $data = $request->post();
        if($data['city']){
            $data['createtime'] = time();
            $data['bid'] = $bid;
            $res = FreightModel::instance()->dinsert($data);
            if($res == 1){
                $this->error('只能添加一条数据','freight/distance');
            }
            $this->success('添加成功', 'freight/distance');
        }else{
            $this->error('未选择配送地址');
        }


    }
    /**
     * 开启关闭
     */
    public function soldOut(Request $request){
        $type = $request->post();
        $result = FreightModel::instance()->soldOut($type);
        return $result;
    }
    /**
     * 距离模板开启关闭
     */
    public function dsoldOut(Request $request){
        $type = $request->post();
        $result = FreightModel::instance()->dsoldOut($type);
        return $result;
    }
    /**
     * 距离模板删除
     */
    public function delOut(Request $request){
        $type = $request->post();
        $result = db('run_dis_freight')->where('freid',$type['goodsid'])->delete();
        echo 1;
    }
    /**
     * 修改数据库
     */
    public function editinsert(Request $request){
        $data = $request->post();
        $data['handletime'] = time();
        $result = FreightModel::instance()->editinsert($data);
        if($result == 1){
            $this->success('添加成功', 'freight/index');
        }else{
            $this->error('修改失败');
        }
    }
    /***
     * @时间设置
     */
    public function date()
    {
        return view('freight/date');
    }
      /**
     * @param Request $request
     * @return mixed
     * @页面初始化时间设置
     */
    public function GetDate(Request $request)
    {
        $bid = $this->_getBid();
        $result = FreightModel::instance()->GetDate($bid);
        return $result;
    }
    /***
     * @循环时间配置
     */
    public function SetDate(Request $request)
    {
        date_default_timezone_set("PRC");
        $Jtime = $request->post();
        //var_dump($Jtime);die;
        $start = strtotime(date("Y-m-d 00:00:00"));
        $end   = strtotime(date("Y-m-d 00:00:00",strtotime('+1 day')));
        $arr['setdate'] = [];
        $res['res'] = [];
        for($time = $start; $time <= $end-$Jtime['data']* 60; $time = $time + $Jtime['data']* 60){
            $y = array(
                'status'=>true,
                'time'=>date("H:i", $time),
                'endtime' => date("H:i", $time + $Jtime['data']* 60),
                'fee'=>0,
                'wb'=>1
            );
            array_push($arr['setdate'],$y);
        }
        return json_encode($arr);
    }

    /**
     * @param Request $request
     * @return mixed
     * @添加时间设置
     */
    public function InsertDate(Request $request)
    {
        $date = $request->post();
        $bid = $this->_getBid();
       // var_dump($request->post());die;
        $data = [
            'name' => $date['name'],
            'storeid' => $bid,
            'date' => $date['setdate'],
        ];
        $result = FreightModel::instance()->InsertDate($data);
        return $result;
    }
    /**
     * 代驾时间计费模板
     */
    public function license()
    {
        return view('freight/DrivingData');
    }
}
