<?php
namespace app\index\controller\home;

use app\index\model\BannerModel;
use phpDocumentor\Reflection\Types\Null_;
use think\console\Output;
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

}
