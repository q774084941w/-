<?php
namespace app\home\controller;


use think\Controller;
use think\Request;
use think\Db;

class Setsms extends Controller{
    public function _iniaialize(Request $request = null)
    {


    }
    public function index()
    {
        $bid = $this->_getBid();
        $result = db('alysend')->where('bid', $bid)->select();
        if ($result){
                return view('setsms/index', ['data' => $result, 'bid' => $result[0]['bid']]);
        }else {
                return view('setsms/index',['data' => $result, 'bid'=>$bid]);
        }
    }


    public function edit(){
        $id = $this->_getBid();
        $result = Db::name('alysend')->where('bid',$id)->find();
//        var_dump($result);exit;
        return view('setsms/edit',['data'=>$result,'bid' =>$id]);
    }
    public function yard(){
        $id = $this->_getBid();
        $result = Db::name('run_yards')->where('bid',$id)->find();
        return view('setsms/yard',['data'=>$result,'bid' =>$id]);
    }
    public function editer(Request $request){
        $data = $request->post();
        $bid =  $data['bid'];
        $dis = db('alysend')->where('bid',$bid)->find();
        if($dis){
            $vi = [
                'keyid'=>$data['keyid'],
                'keysecret'=>$data['keysecret'],
                'signname'=>$data['signname'],
                'templatecode'=>$data['templatecode'],
                'time'=>time()
            ];
            $result = Db::name('alysend')->where('bid',$bid)->update($vi);
            if($result || $result == 0){
                $this->success('操作成功', 'setsms/edit');
            }else{
                $this->error('操作失败');
            }
        }else{
            $data['time'] = time();
            $result = Db::name('alysend')->insert($data);
            if($result){
                $this->success('操作成功', 'setsms/edit');
            }else{
                $this->error('操作失败');
            }
        }
    }
    /**收货码**/
    public function editers(Request $request){
        $data = $request->post();
        $bid =  $data['bid'];
        $dis = db('run_yards')->where('bid',$bid)->find();
        if($dis){
            $vi = [
                'keyid'=>$data['keyid'],
                'keysecret'=>$data['keysecret'],
                'signname'=>$data['signname'],
                'templatecode'=>$data['templatecode'],
            ];
            $result = Db::name('run_yards')->where('bid',$bid)->update($vi);
            if($result || $result == 0){
                $this->success('操作成功', 'setsms/yard');
            }else{
                $this->error('操作失败');
            }
        }else{
//            $data['time'] = time();
            $result = Db::name('run_yards')->insert($data);
            if($result){
                $this->success('操作成功', 'setsms/yard');
            }else{
                $this->error('操作失败');
            }
        }
    }
}
