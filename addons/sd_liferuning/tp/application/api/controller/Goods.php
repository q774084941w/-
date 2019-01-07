<?php
namespace app\api\controller;

use app\api\model\GoodsModel;
use think\Controller;
use think\Request;
use think\Url;


class Goods extends Controller
{

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
                $result = GoodsModel::instance()->goodslist(['menu'=>$goods['tid'],'status'=>1,'bid'=>$goods['bid']],$fieid);
            }
       }else{
           $result = GoodsModel::instance()->goodslistlimt(['recom'=>1,'status'=>1,'bid'=>$goods['bid']],$fieid);
            $offer = GoodsModel::instance()->specialOffer(['status'=>1,'bid'=>$goods['bid'],'special_offer'=>1],$fieid);
	foreach ($offer as &$va){
                $va['pic'] = uploadpath('goods',$va['pic']);
            }
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
/**
     * 搜索
     */
    public function search(Request $request){
        $title = $request->post();
        $fieid = 'goodsid,bid,pic,name,unit,max_price,min_price,favournum,stock,sales,explain,freight_unify';
        $result = GoodsModel::instance()->search($title,$fieid);
        foreach ($result as &$val){
            $val['pic'] = uploadpath('goods',$val['pic']);
        }
        $this->jsonOut($result);
    }
   /**
     * 热销 推荐 积分 商品
     * 0:积分商品  1：特价   2：热销
     */
    public function goodsclassify(Request $request){
        $goods = $request->post();
        if(empty($goods['bid'])) $this->outPut('', 1001, "缺少商家id");
        //if($goods['type']) $this->outPut('', 1001, "type");
        $fieid = 'goodsid,bid,pic,name,unit,max_price,min_price,favournum,stock,sales,explain,freight_unify,need_integral';
        $result = GoodsModel::instance()->goodsclassify($goods,$fieid);
        foreach ($result as &$val){
            $val['pic'] = uploadpath('goods',$val['pic']);
        }
        $this->jsonOut($result);
    }
/**
     * 新闻列表
     */
    public function newslist(Request $request){
        $bid = $request->get('bid');
        $result = GoodsModel::instance()->newslist($bid);
        $this->jsonOut($result);
    }
    /**
     * 新闻详情
     */
    public function newsdetails(Request $request){
        $neid = $request->get('neid');
        $result = GoodsModel::instance()->newsdetails($neid);
        $this->jsonOut($result);
    }
    /**
     *服务时间
     */
    public function times(Request $request){
        $neid = $request->get('bid');
        $result = GoodsModel::instance()->times($neid);
        $this->jsonOut($result);
    }
    /**
     *服务时间
     */
    public function price(Request $request){
        $neid = $request->param();
        //var_dump($neid);die;
        $result = GoodsModel::instance()->price($neid);
        if(empty($result)){
            $result = 1;
            return json_encode(['data' => 0]);
        }
        $this->jsonOut($result);
    }
    /*ceshi*/
    public function test(){
        $freight = GoodsModel::instance()->getFreight('52');
        $add =$freight['freight1']/$freight['next'];
        $ll = $freight['freight'] + $add;
        $result = 218.4;
        if($result < $freight['first']){
            $results = $freight['freight'];
        }else{
//            $dis = bcsub($result,$freight['first']);
//            $price = bcdiv($freight['freight1'],$freight['next']);
//            $results = bcmul($dis,$price);
            $add = ($result - $freight['first'])*($freight['freight1']/$freight['next']);
            $results = $freight['freight'] + $add;
        };
        var_dump($results);
    }

    public function takeTheUrl($data) {

        $url = "https://apis.map.qq.com/ws/direction/v1/driving/?from=".$data['myaddswd'].",".$data['myaddsjd']."&to=".$data['mudaddswd'].",".$data['mudaddsjd']."&output=json&callback=cb&key=EKJBZ-72L3P-FHXDL-VSLEP-JEAGJ-JTFSD";
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true) ; // 获取数据返回
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true) ; // 在启用 CURLOPT_RETURNTRANSFER 时候将获取数据返回
        $datas = curl_exec($ch);
        curl_close($ch);
        return json_decode($datas,true);
    }

    /**
     *距离计算金额
     */
   public function addprice(Request $request){
        $adds = $request->param();
        $bid = $adds['bid'];
        $rs=GoodsModel::instance()->region($adds,$bid);//区域代理
        if($rs){
            $freight = $rs;
            $results['proxy_id']=$rs['proxy_id'];
            $results['region_id']=$rs['region_id'];
        }else{
            $freight = GoodsModel::instance()->getFreight($bid);
        }
        $data = $this -> takeTheUrl($adds);
        if (isset($data['result'])) {
            $results['more'] = $data['result'];
            $results['add']  = $adds;
            $result = round($data['result']['routes'][0]['distance']/1000,2);
            $results['distance'] = $result;
            //$result = GoodsModel::instance()->addprice($adds);
            $results['freight'] = $freight;
            if($result < $freight['first']){
                $results['price'] = $freight['freight'];
            }else{
                $add =  empty($freight['first'])? 0 : ($result-$freight['first'])/$freight['next'] * $freight['freight1'];
                $results['price']  = empty($freight['freight'])? 10 :$freight['freight'] + $add;
                //var_dump(['res' =>$add,'ress' => $result,'results'=> $results]);die;
            };

            $this->jsonOut($results);
        } else {
          echo json_encode(['code'=>0,'msg'=>$data['message']]);
        }
    }
    /**
     *重量计算金额
     */
    public function WeightPrice(Request $request){
        $bid = $request->get('bid');
        $weight = $request->get('weight');
        $region_id=$request->get('region_id');
        $result = GoodsModel::instance()->WeightPrice($bid,$weight,$region_id);
        $this->jsonOut($result);
    }
    /**
     *保单
     */
    public function Insprice(Request $request){
        $bid = $request->get('bid');
        $result = GoodsModel::instance()->Insprice($bid);
        $this->jsonOut($result);
    }
    /**
     * 悬赏
     */
    public function reward(Request $request){
        $bid = $request->get('bid');
        $result = GoodsModel::instance()->reward($bid);
        $this->jsonOut($result);
    }
    /**
     *议价
     */
    public function distype(Request $request){
        $bid = $request->get('bid');
        $result = GoodsModel::instance()->distype($bid);
        $this->jsonOut($result);
    }
}
