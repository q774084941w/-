<?php
namespace app\home\controller;

use app\home\model\BannerModel;
use app\home\model\IndexModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Db;
use think\Request;
use think\Session;

class Banner extends Controller
{
    public function _iniaialize(Request $request = null)
    {
        var_dump($this->_getBid());

    }
    /**
     * banner类
     * 列表
     */
    public function index(Request $request){
      global $_W;

      $UserSession = Session::get('we7_account');
        if(empty($UserSession))
            header('Location:'.$_W['setting']['copyright']['url']);
        else
            //var_dump($UserSession['key']);die;
            $data = [
                'appid' => $UserSession['key'],
                'secret' => $UserSession['secret'],
                'uniacid' =>$UserSession['uniacid']
            ];
        //$results = IndexModel::instance()->checkLogin();
        $result = IndexModel::instance()->insertWei($data,$UserSession['uniacid']);
        Session::set('bus_bid',$result['bid']);
        //var_dump($result);die;
        //var_dump(1);die;
        //$bid = $request->get('bid');
        $bid = $this->_getBid();
//        var_dump($bid);die;
        $field = 'banid,bid,pic,url,sort,name,createtime';
        $result = BannerModel::instance()->banlist($bid,$field);
        //var_dump($result);die;
        foreach ($result as &$val){
            //var_dump( uploadpath('banner',$val['pic']));die;
            $val['pic'] = uploadpath('banner',$val['pic']);

            $val['createtime'] = date('Y-m-d H:i:s',$val['createtime']);
        }
        $blogo = db('business')->where(['bid' => $bid])->find();
        if($blogo){
            $blogo['logo'] = uploadpath('business',$blogo['logo']);
        }
        $this->assign('blogo',$blogo);
        return view('banner/index',['data'=>$result]);
    }
    /**
     *
     * 添加
     */
    public function add(){
        return view('banner/add');
    }

    /**
     * 添加数据库
     */
    public function insert(Request $request){
        $bid = $this->_getBid();
        $bid = $this->_getBid();
        $pic = CommonModel::instance()->upload('banner');
        $data = $request->post();
        $data['pic'] = $pic;
        $data['bid'] = $bid;
        $data['createtime'] = time();
        $result = BannerModel::instance()->baninster($data);
        if($result == 1){
            $this->success('添加成功', 'Banner/index');
        }else{
            $this->error('新增失败');
        }

    }
    
    /**
     * 删除
     */
    public function delete(Request $request){
        $banid = $request->get('id');
        $result = BannerModel::instance()->deleteban($banid);

        return $result;
    }
    public function banner_save(Request $request){
        $banid = input('param.banid');
        $result = BannerModel::instance()->getban($banid);
        $result['pic']=uploadpath('banner',$result['pic']);
        return view('banner/edit',['data'=>$result]);
    }
    public function saveban(Request $request){
        $data=$request->post();
        $banid=$request->post('banid');
        $data['bid']=$this->_getBid();
        $data['pic']=CommonModel::instance()->upload('banner');
        $data['createtime']=time();
        $result=BannerModel::instance()->saveban($banid,$data);

        if($result){
            $this->success('修改成功！','Banner/index');
        }else{
            $this->error('修改失败!');
        }
    }
    public function Home(){
        $bid=$this->_getBid();
        $src=db('Business')->where('bid',$bid)->value('pic');
        if(empty($src)){
            $src='https://lg-kpwvyrhk-1253649822.cos.ap-shanghai.myqcloud.com/auth-bg-img.png';
        }else{
            $src=uploadpath('home',$src);
        }
        if(\request()->isPost()){
            $src=CommonModel::instance()->upload('home');
            $result=db('Business')->where('bid',$bid)->update(['pic'=>$src]);
            if($result){
                return $this->success('更新成功');
            }else{
                return $this->error('error');
            }
        }else{
            return view('',['src'=>$src]);
        }

    }

}
