<?php
namespace app\home\controller;

use app\home\model\FreightModel;
use app\home\model\GoodsModel;
use app\home\model\CommonModel;
use think\Controller;
use think\Request;
use think\Cache;


class Goods extends Controller
{


    public function _iniaialize(Request $request = null)
    {


    }
    public function ceshi(){
        $data = '<?php
//配置文件
return [
    \'APPID\'           => \'wxc3381e684ed93b64\',
    \'MCHID\'           => \'1492564812\',     //商户号（必须配置，开户邮件中可查看）
    \'KEY\'             => \'JDJNTMRTDJKKSGJGLZX1983102600000\',        //商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
    \'APPSECRET\'       => \'5eacd8beca4ea2e2d0b64ba4b083a19d\',        //公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）
    \'SSLCERT_PATH\'    => \'/cert/apiclient_cert.pem\',   //设置商户证书路径
    \'SSLKEY_PATH\'     => \'/cert/apiclient_key.pem\',   //设置商户证书路径
    \'WEIXIN_URL\'      => \'https://shop.zijunxcx.cn/api/Paynotify/wxgoodsnotify\',
    \'REPORT_LEVENL\'   => 1,    //接口调用上报等级，默认紧错误上报(0.关闭上报; 1.仅错8888
];';
        file_put_contents(ROOT_PATH."application/extra/wxpay.php", $data);
    }
    /**
     * 首页展示
     * @return \think\response\View
     *
     */
    public function index()
    {
        $bid = [
            'bid' => $this->_getBid(),
            'delete' => 0,
        ];
        $data = GoodsModel::instance()->show($bid);

        return view('goods/index',['data'=>$data]);
    }
    /**
     * 编辑
     * @return \think\response\View
     *
     */
    public function edit(Request $request)
    {
        $goodsid = $request->get('id');
        $bid = [
            'bid' => $this->_getBid(),
            'status' => 1
        ];
        $details = GoodsModel::instance()->details($goodsid);

        $details['pic'] = uploadpath('goods',$details['pic']);

        if($details['template'] != 0){
            $freigh = FreightModel::instance()->showdata($details['template']);
            $details['freid'] = $freigh['freid'];
            $details['title'] = $freigh['title'];
        }else{
            $details['freid'] = '';
            $details['title'] = '';
        }
        $menus = GoodsModel::instance()->menus($bid);
        $details['template'] == 0 ? $details['aaxa'] = 0 : $details['aaxa'] = 1;
        $details['freight_unify'] == 0 ? $details['freight_unify'] = '' : $details['freight_unify'] = $details['freight_unify'];
        return view('goods/edit',['menus'=>$menus,'data'=>$details]);

    }
    /**
     * 详情
     */
    public function show(Request $request){
        $id = $request->get('id');
        $details = GoodsModel::instance()->details($id);
        $details['pics'] ? $details['pics'] = explode(',',$details['pics']) : $details['pics'] = [];
        $details['pic'] = uploadpath('goods',$details['pic']);
        $details['freight_count'] == 0 ? $details['freight_count'] = '重量' : $details['freight_count'] = '件数';
        $details['shelves_time'] = date('Y-m-d H:i:s',$details['shelves_time']);;
        $details['createtime'] = date('Y-m-d H:i:s',$details['createtime']);;
        $details['updatetime'] == 0 ? $details['updatetime'] = '' : $details['freight_count'] = date('Y-m-d H:i:s',$details['freight_count']);
        foreach ($details['pics'] as &$val){
            $val = uploadpath('goods',$val);
        }
        return view('goods/show',['data'=>$details]);
    }


    /**
     * 商品添加
     * @return \think\response\View
     *
     */
    public function add()
    {
        $bid = [
            'bid' => $this->_getBid(),
            'status' => 1,
        ];
        $bida = [
            'bid' => $this->_getBid(),
            'status' => 1,
            'type' =>1
        ];

        $menus = GoodsModel::instance()->menus($bid);
        $freight = GoodsModel::instance()->freight($bida);
        return view('goods/add',['menus'=>$menus,'freight'=>$freight]);
    }
    /**
     * 商品添加
     * @return \think\response\View
     *
     */
    public function templateajax()
    {

        $bida = [
            'bid' => $this->_getBid(),
            'status' => 1,
            'type' =>1
        ];

        $freight = GoodsModel::instance()->freight($bida);

        return $freight;
    }


    /**
     * 添加数据库
     * @    param Request $request
     */
    public function insert(Request $request){
        header('Content-type:text/html;charset=utf-8');
        $data = $request->post();
        var_dump($data);exit();

        $pic = CommonModel::instance()->upload('goods');
        $data['pic'] = $pic;
        $data['bid'] = $this->_getBid();
        $imgs = [];

        foreach (explode('_)(_',$data['imgurl']) as $base64_image_content){
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)){
                $type = $result[2];
                $new_file = "uploads/goods/".date('Ym',time())."/";
                if(!file_exists($new_file))
                {
                    mkdir($new_file, 0700);
                }
                $savename = date('Ymd') . randCode(6) . str_replace('.','',microtime(true));
                $new_file = $new_file.$savename.".{$type}";
                if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))){
                    $imgs[] = $savename.".$type";
                }else{
                    $imgs[] = '';
                }
            }

        }
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
        $imgs = implode(',',$imgs);

        $result = GoodsModel::instance()->addgoods($data,$imgs);
        if($result > 0){
            $this->success('添加成功', 'goods/index');
        }else{
            $this->error('新增失败');
        }

    }
    /**
     * 添加数据库
     * @    param Request $request
     */
    public function editinsert(Request $request){
        header('Content-type:text/html;charset=utf-8');
        $data = $request->post();

        $pic = CommonModel::instance()->upload('goods');
        if($pic != 0) $data['pic'] = $pic;

        $data['bid'] = $this->_getBid();
        $imgs = [];
        foreach (explode('_-_',$data['imgurl']) as $base64_image_content){
            if(substr($base64_image_content,0,4) == 'http'){
                $imgs[] = substr(strstr($base64_image_content,'//20'),9);
                unset($base64_image_content);
            }else {
                if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
                    $type = $result[2];
                    $new_file = "uploads/goods/" . date('Ym', time()) . "/";
                    if (!file_exists($new_file)) {
                        mkdir($new_file, 0700);
                    }
                    $savename = date('Ymd') . randCode(6) . str_replace('.', '', microtime(true));
                    $new_file = $new_file . $savename . ".{$type}";
                    if (file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)))) {
                        $imgs[] = $savename . ".$type";
                    } else {
                        $imgs[] = '';
                    }
                }
            }
        }


        $imgs = implode(',',$imgs);
        $result = GoodsModel::instance()->editinsert($data,$imgs);
        if($result > 0){
            $this->success('添加成功', 'goods/index');
        }else{
            $this->error('新增失败');
        }

    }
    /**
     *
     * 上下架
     */
    public function soldOut(Request $request){
        $type = $request->post();
        $result = GoodsModel::instance()->soldOut($type);
        return $result;
    }
    /**
     * 编辑图片
     */
    public function editajax(Request $request){
        $id = $request->get('id');

        $result = GoodsModel::instance()->editajax($id);
        return $result;
    }
    /**
     * 改库存
     */
    public function inventory(Request $request){
        $data = $request->get();
        $result = GoodsModel::instance()->inventory($data);
        return $result;
    }
    /**
     * 搜索
     */
    public function goodssearch(Request $request){
        $data = $request->get();
        $bid = $this->_getBid();
        $result = GoodsModel::instance()->goodssearch($data,$bid);
        return view('goods/index',['data'=>$result]);
    }
    /**
     * 删除
     */
    public function delete(Request $request){
        $banid = $request->get('id');
        $result = GoodsModel::instance()->deleteban($banid);
        return $result;
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
        $mudadds = explode(',',$adds['mudadds']);
        $myadds  = explode(',',$adds['myadds']);
        $adds['myaddswd'] = $myadds[0];
        $adds['myaddsjd'] = $myadds[1];
        $adds['mudaddswd'] = $mudadds[0];
        $adds['mudaddsjd'] = $mudadds[1];
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
            $results['more'] = $data['result']['routes'][0]['distance']/1000;
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



}
