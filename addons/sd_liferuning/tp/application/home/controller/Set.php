<?php
namespace app\home\controller;


use think\Controller;
use think\Request;
use think\Db;
use app\home\model\SetModel;
use app\home\model\CommonModel;

class Set extends Controller{
    public function _iniaialize(Request $request = null){

    }

    public function index()
    {
        $bid = $this->_getBid();
        $result = SetModel::instance()->SetList($bid);
        if(empty($result)){
            return view('set/index',['data' => $result]);
        }else{
            return view('set/index',['data' => $result]);
        }
    }
    /**
     * 添加
     */
    public function add()
    {
        $homepage = request()->file('homepage');
        $poster = request()->file('poster');
        $bid = $this->_getBid();
        $data = input('param.');
        if ($homepage != NULL){
            $data['homepage'] = $this->uploadh($homepage,'homepage');
        }
        if ($poster != NULL){
            $data['poster'] = $this->uploadh($poster,'poster');
        }
        //var_dump($data);die;
        $list = Db::name('run_rules')->where('bid',$bid)->find();
        if(empty($list)){
            $data['bid'] = $bid;
            $lists = Db::name('run_rules')->insert($data);
        }else{
            $lists = Db::name('run_rules')->where('bid',$bid)->update($data);
            //var_dump($lists);die;
        }
        if($lists == 1){
            $this->success('配置成功', 'set/index');
        }else{
            $this->success('配置失败，请重新配置', 'set/index');
        }
    }
	//选择是否开启区域限制
    public function changeLimit(){
        $bid = $this->_getBid();
        $area_limit = input('limit');
        $res = Db::name('business')->where('bid',$bid)->update(['area_limit'=>$area_limit]);
        if ($res){
            return '修改成功';
        }else{
            return '修改失败';
        }
    }
	//购买区域限制
	public function citylimit(){
     	return view('set/citylimit');
    }
    //修改区域
	public function addlimit(){
        $bid = $this->_getBid();
        $data = SetModel::instance()->areaList($bid);
        $data = json_encode($data);
        $area_limit = Db::name('business')->field('area_limit')->where('bid',$bid)->find()['area_limit'];
      	
        $this->assign(['data'=>$data,'area_limit'=>$area_limit]);
     	return view('set/addlimit');
    }
    //修改区域保存
    public function addLimitAct(){
        $bid  = $this->_getBid();
        $data = json_decode(input('list'));
        $del = json_decode(input('del'));
        foreach ($data as $k => $v){
            $data[$k] = explode(",",$v->city);
        }
        $result = SetModel::instance()->areaAdd($data,$del,$bid);
        return $result;
    }
    /**
     * 图片上传
     * @return Common|null
     *
     */
    public function uploadh($file,$path){
        // 获取表单上传文件 例如上传了001.jpg
        if($file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'. DS . $path ,randCode(6));
            if($info){
                // 成功上传后 获取上传信息
                // 输出 jpg
                return $info->getSaveName();
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }else{
            return 0;
        }

    }

}
