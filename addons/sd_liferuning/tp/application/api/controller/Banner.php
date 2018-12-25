<?php
namespace app\api\controller;

use app\api\model\BannerModel;
use think\Controller;
use think\Request;

class Banner extends Controller
{
    /**
     * banner 显示
     */
    public function banlist(Request $request){
        $bid = $request->get('bid');
        $field = 'banid,bid,pic,url,sort';
        $result = BannerModel::instance()->banlist($bid,$field,'sort desc');
        foreach ($result as &$value){
            $value['pic'] = uploadpath('banner',$value['pic']);
        }
        $this->jsonOut($result);
    }
    public function homeimg($bid){
        $src=db('Business')->where('bid',$bid)->value('pic');
        if(empty($src)){
            $src='https://lg-kpwvyrhk-1253649822.cos.ap-shanghai.myqcloud.com/auth-bg-img.png';
        }else{
            $src=uploadpath('home',$src);
        }
        exit(json_encode(['code'=>1,'src'=>$src]));
    }

}
