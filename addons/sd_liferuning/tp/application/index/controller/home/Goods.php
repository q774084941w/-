<?php
namespace app\index\controller\home;

use app\index\model\CommonModel;
use app\index\model\GoodsModel;
use think\Controller;
use think\Request;


class Goods extends Controller
{
    /**
     * 首页展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $data = GoodsModel::instance()->show();
        
        return view('home/goods/index',['data'=>$data]);
    }
    /**
     * 首页展示
     * @return \think\response\View
     *
     */
    public function edit(Request $request)
    {
        $goodsid = $request->post('goodsid');
        $menus = GoodsModel::instance()->menus();
        $freight = GoodsModel::instance()->freight();
        $details = GoodsModel::instance()->details($goodsid);

        return view('home/goods/edit',['menus'=>$menus,'freight'=>$freight,'data'=>$details]);
    }


    /**
     * 商品添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        $menus = GoodsModel::instance()->menus();
        $freight = GoodsModel::instance()->freight();

        return view('home/goods/add',['menus'=>$menus,'freight'=>$freight]);
    }

    /**
     * 添加数据库
     * @param Request $request
     */
    public function insert(Request $request){
        $data = $request->post();
        if($data['caidan']){
            $maxmenu = explode(',',$data['caidan']);
            foreach ($maxmenu as $key=>&$val){
                $minmunu = explode('/*max*/',$val);
                foreach ($minmunu as $k=>&$v){
                    $list[$key][] = $v;
                }
            }
            $data['caidan'] = $list;
        }
        GoodsModel::instance()->addgoods($data);
        echo '添加成功';

        
    }

    /**
     * 商品列表
     * @return \think\response\View
     *
     */
    public function lists(Request $request)
    {
        $goods = $request->post();
        if(empty($goods['bid'])) $this->outPut('', 1001, "缺少商家id");
        $fieid = 'goodsid,bid,pic,name,unit,max_price,min_price,favournum,stock,sales,explain,freight_unify';
        $bus = GoodsModel::instance()->buslist(['bid'=>$goods['bid'],'status1'=>1],'name,address');
        if(!empty($goods['tid'])){
            if(!empty($goods['ptid'])){
                $result = GoodsModel::instance()->goodslist(['menu'=>$goods['tid'],'type'=>$goods['ptid'],'status'=>1,'bid'=>$goods['bid']],$fieid);
            }else{
                $result = GoodsModel::instance()->goodslist(['menu'=>$goods['tid'],'status'=>1,'bid'=>$goods['bid'],'recom'=>2,],$fieid);
            }
        }else{
            $result = GoodsModel::instance()->goodslist(['recom'=>1,'status'=>1,'bid'=>$goods['bid']],$fieid);
            $offer = GoodsModel::instance()->specialOffer(['status'=>1,'bid'=>$goods['bid'],'special_offer'=>1],$fieid);
$bus['offer'] = $offer;
        }
        foreach ($result as $key=>&$val){
            $val['pic'] = uploadpath('goods',$val['pic']);
        }
        $bus['goods'] = $result;
        

        $this->jsonOut($bus);

    }
    /**
     * 商品详情
     */
    public function detail(Request $request){
        $goodsid = $request->get('goodsid');
        if(empty($goodsid)) $this->outPut(null,1001,"缺少商品id");
        
        $where['goodsid'] = $goodsid;
        //$where['status'] = 1;
        $field = 'goodsid,bid,pic,name,unit,max_price,min_price,favournum,stock,sales,explain,content,status,pics,weight,freight_unify,open_rule';
        $result = GoodsModel::instance()->getDetail($where,$field);

        $result['pic'] = uploadpath('goods',$result['pic']);

        $this->jsonOut($result);
        
    }

        
}
