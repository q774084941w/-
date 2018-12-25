<?php
namespace app\home\controller;

use app\home\model\ServiceModel;
use think\Controller;
use think\Request;
use app\home\model\CommonModel;
use think\Db;
class Service extends Controller
{
    public function _iniaialize(Request $request = null)
    {


    }
    /**
     * 用户类
     *
     */
    public function index(){
        $bid = $this->_getBid();

        $result = ServiceModel::instance()->servicelist($bid);
        foreach($result as $k=>$v){
            $data = [];
            $data = $v;
            $data['pic'] = uploadpath('service',$v['pic']);
            $result->offsetSet($k,$data);
        }
        return view('service/index',['data'=>$result]);
    }
    /*
     * 添加服务
     */
    public function add()
    {
        $cid = input('id');
        return view('service/add',['cid'=>$cid]);
    }
    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){
        $pic = CommonModel::instance()->upload('service');
        $data = $request->post();
     // var_dump($_FILES['image']);die;
        if($data['name'] == '' || $_FILES['image'] ==''){
            return $this->success('内容不完整','service/add');
        }
        if($pic){
            $data['pic'] = $pic;
            $data['bid']  = $this->_getBid();
            ServiceModel::instance()->insert($data);
            return $this->success('添加成功','service/classlist');
        }else{
            return $this->success('添加失败','service/classlist');
        }
    }
    /**
     * 详情
     */
    public function show(Request $request){
        $id = $request->get('id');
        $details = ServiceModel::instance()->details($id);
        $details['pic'] = uploadpath('service',$details['pic']);
        return view('service/show',['data'=>$details]);
    }
    /**
     * 上下架
     */
    public function soldOut(Request $request){
        $type = $request->post();
        $result = ServiceModel::instance()->soldOut($type);
        return $result;
    }
    /**
     * 编辑服务
     */
    public function edit(Request $request)
    {
        $id = $request->get('id');
        $bid = [
            'bid' => $this->_getBid(),
            'status' => 1
        ];
        $details = ServiceModel::instance()->detail($id);
        $details['pics'] = $details['pic'];
        $details['pic'] = uploadpath('service',$details['pic']);

        return view('service/edit',['data'=>$details]);
    }
    /**
     * 编辑
     */
    public function editinsert(Request $request){
        header('Content-type:text/html;charset=utf-8');
        $data = $request->post();
        $id = $request->get('id');

        $pic = CommonModel::instance()->upload('service');
        if($pic != 0) $data['pic'] = $pic;
        $data['bid'] = $this->_getBid();
        $result = ServiceModel::instance()->editinsert($data,$id);

        if($result > 0){
            $this->success('修改成功', 'service/classlist');
        }else{
            $this->error('修改失败','service/classlist');
        }
    }
     /**
     * 删除
     */
        public function delete(Request $request){

            $id = $request->get('id');
            $result = ServiceModel::instance()->deleteban($id);

            return $this->success('删除成功','service/classlist');
        }
        /**
         * 服务分类列表
         */
        public function classlist(Request $request){
            //$bid = $request->get('bid');
            $bid = $this->_getBid();

            $list = db('class')->where('bid',$bid)
                ->order('paixu asc')
                ->select();
            foreach ($list as $key=>&$va){
                $oneype = db('service')->where(['cid'=>$va['cid'],'bid'=>$bid])->select();
                foreach ($oneype as $k =>&$v){
                    $oneype[$k]['pic'] = uploadpath('service',$v['pic']);
                    $threeClass = db('three_class')->where(['bid'=>$bid,'pid'=>$v['id']])->select();
                    $v['three'] = $threeClass;
                }
                $va['vdo'] = $oneype;
            }
            return view('service/classlist',['data'=>$list]);
        }
        /**
         * 添加服务分类
         */
        public function addclass(){
            $bid = $this->_getBid();

            return view('service/addclass');
        }
        /**
         * 插入分类数据
         */
        public function insertclass(Request $request){
            $data = $request->post();
            $data['bid']  = $this->_getBid();
            $data['status'] = 1 ;
            $result = db('class')->insert($data);
            if ($result){
                return $this->success('添加成功','service/classlist');
            }else{
                return $this->success('添加失败','service/classlist');
            }
        }
        /**
         * 删除分类
         */
        public function delclass(Request $request){
            $bid = $this->_getBid();

            $id = $request->get('id');

            db('class')->where(['bid'=>$bid,'cid'=>$id])->delete();

            return $this->success('删除成功','service/classlist');
        }
        /**
         * 编辑分类
         */
        public function editclass(Request $request){

            $bid = $this->_getBid();

            $id = $request->get('id');

            $result = db('class')->where(['bid'=>$bid,'cid'=>$id])->find();

            return view('service/addclass',['data'=>$result]);
        }
        /**
         * 修改分类
         */
        public function updateclass(Request $request){
            $data = $request->post();
            $bid = $this->_getBid();
            $cid = $data['cid'];
            $vn = [
                'name'=>$data['name'],
                'moban'=>$data['moban'],
                'paixu'=>$data['paixu']
                ];
            $result = db('class')->where(['bid'=>$bid,'cid'=>$cid])->update($vn);
            if ($result){
                return $this->success('修改成功','service/classlist');
            }else{
                return $this->success('修改失败','service/classlist');
            }
        }
        /**
         * 添加子分类，即三级分类
         */
        public function threeadd(Request $request){
            $pid = $request->get('id');

            return view('service/threeadd',['pid'=>$pid]);
        }
        /**
         * 插入子分类的数据
         */
        public function threeinsert(Request $request){
            $data = $request->post();
            $bid = $this->_getBid();
            $data['bid'] = $bid;
            $data['status'] = 1;
            $result = db('three_class')->insert($data);
            if ($result){
                return $this->success('添加成功','service/classlist');
            }else{
                return $this->success('添加失败','service/classlist');
            }
        }
    /**
     * 编辑三级分类
     */
    public function editthree(Request $request){

        $bid = $this->_getBid();

        $id = $request->get('id');
        $result = db('three_class')->where(['bid'=>$bid,'tid'=>$id])->find();

        return view('service/threeadd',['data'=>$result]);
    }
    /**
     * 修改分类
     */
    public function updatethree(Request $request){
        $data = $request->post();
        $bid = $this->_getBid();
        $tid = $data['tid'];
        $vn = [
            'sname'=>$data['sname'],
            'my_status'=>$data['my_status']
        ];
        $result = db('three_class')->where(['bid'=>$bid,'tid'=>$tid])->update($vn);
        if ($result){
            return $this->success('修改成功','service/classlist');
        }else{
            return $this->success('修改失败','service/classlist');
        }
    }
    /**
     * 删除三级分类
     */
    public function delthree(Request $request){
        $bid = $this->_getBid();

        $id = $request->get('id');

        db('three_class')->where(['bid'=>$bid,'tid'=>$id])->delete();

        return $this->success('删除成功','service/classlist');
    }
    
    
    
    /*
       悬赏金额
     * */
	public function reward(){
        $bid = $this->_getBid();
        $res = db::name('membermoney')->field('xsmoney')->where(['bid'=>$bid])->find();
		return view('service/reward',['data'=>$res]);
	}
    /*
    * 设置悬赏金额
    */
    public function rewardMoney(Request $request){
        $bid = $this->_getBid();
        $money = $request->post('money');
//        var_dump($money);die;
        $res = db::name('membermoney')->where(['bid'=>$bid])->find();
        if($res){
            $res = db::name('membermoney')->where(['bid'=>$bid])->update(['xsmoney'=>$money]);
            $this->success('修改成功!','service/reward',['data'=>$money]);
        }else{
            $res = db::name('membermoney')->insert(['xsmoney'=>$money,'bid'=>$bid]);
            $this->success('添加成功!','service/reward');
        }

    }














}
