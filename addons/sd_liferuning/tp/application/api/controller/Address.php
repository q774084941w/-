<?php
namespace app\api\controller;

use app\api\model\AddressModel;
use think\Controller;
use think\Request;

class Address extends Controller
{
   /**
    * 用户类  添加收货地址
    */
    public function addlist(Request $request){
        $address = $request->post();
        $data = [
            'uid' => $address['uid'],
            'address' => $address['address'],
            'bid' => $address['bid'],
            'name' => $address['name'],
            'phone' => $address['phone'],
            'province' => $address['province'],
            'city' => $address['city'],
            'area' => $address['area'],
            'default' => isset($address['default']) ? $address['default'] : 0,
            'address_type' => isset($address['type']) ? $address['type'] : 0,
            'createtime' => time()
        ];

//        $url='https://restapi.amap.com/v3/geocode/geo';
//        $input=[
//            'city'=>$data['city'],
//            'address'=>$data['address'],
//            'key'=>'d81974e1b46b1d913ead63752fe8c434',
//        ];
//        $adds=json_decode(file_get_contents($url.'?'.http_build_query($input)),1);
//        if($adds['info']=='OK')$data['location']=$adds['geocodes'][0]['location'];
        $count = AddressModel::instance()->address($data['uid']);
//        if($count >= 4) $this->outPut(null,3001);
        if(empty($count)) $data['default'] = 1;
        if($address['default'] == 1 && !empty($count)){
            $result =  AddressModel::instance()->sitelist($data);
        }else{
            $result =  AddressModel::instance()->addressAdd($data);
        }
        if($result == 1){
            $this->jsonOut(['success'=>$result]);
        }else{
            $this->outPut(null,0);
        }
    }
    /**
     * 收货地址列表
     */
    public function addressList(Request $request){
        $uid = $request->get('uid');
        $uaid = $request->get('uaid');
        $bid = $request->get('bid');
        $type = $request->get('type');
        if(empty($type)) {
            $type=0;
        }
        isset($uaid) ? $uaid = $uaid : $uaid = '';
        $field = 'id,uaid,uid,address,name,phone,province,city,area,default';
        $list = AddressModel::instance()->datalist($uid,$field,$uaid,$bid,$type);
        $this->jsonOut($list);
    }
    /**
     * 修改收货地址
     */
    public function siteupdate(Request $request){
        $info = $request->post();
        $type = $info['type'];
        if(empty($type)) {
            $type=0;
        }
        if(isset($info['address'])) $data['address'] = $info['address'];
        if(isset($info['name'])) $data['name'] = $info['name'];
        if(isset($info['phone'])) $data['phone'] = $info['phone'];
        if(isset($info['province'])) $data['province'] = $info['province'];
        if(isset($info['city'])) $data['city'] = $info['city'];
        if(isset($info['area'])) $data['area'] = $info['area'];

        $data['updatetime'] = time();
    
        $result = AddressModel::instance()->siteupdate($data,$info['uaid'],$type);
        if($result == 1){
            $this->jsonOut(['success'=>$result]);
        }else{
            $this->outPut(null,0);
        }
    }
    /**
     * 设置默认地址
     */
    public function defaultsite(Request $request){
        $uaid = $request->get('uaid');
        $type = $request->get('type');
        if(empty($type)) {
            $type=0;
        }
        $result = AddressModel::instance()->defaultsite($uaid,$this->uid,$type);
        if($result == 1){
            $this->jsonOut(['success'=>$result]);
        }else{
            $this->outPut(null,0);
        }

    }
    /**
     * 删除地址
     */
    public function delAddress(Request $request){
        $uaid = $request->get('uaid');
        $type = $request->get('type');
        if(empty($type)) {
            $type=0;
        }
        $result = AddressModel::instance()->delAddress($uaid,$type);
        if($result){
            $this->jsonOut(['success'=>$result]);
        }else{
            $this->outPut(['success'=>1]);
        }

    }
    /**
     * 获取默认收货地址
     */
    public function mrAddress(Request $request){
        $uid = $request->get('uid');
        $bid = $request->get('bid');
        $type = $request->get('type');
        if(empty($type)) {
            $type=0;
        }
        $result = AddressModel::instance()->defaultaddr($uid,$bid,$type);
        $res = [
            'uaid' => $result['uaid'],
            'adress' => $result['province'].$result['city'].$result['area'].$result['address'],
            'name' => $result['name'],
            'phone'=>$result['phone']
        ];
        if($res){
            $this->jsonOut($res);
        }else{
            $this->jsonOut(0);
        }
    }
    /**
     * 获取地址区域
     */
    public function getarea(Request $request){
        $bid = $request->post('bid');
        $type = $request->post('type');
        if(empty($type)) {
            $type=0;
        }
        $result = AddressModel::instance()->area($bid,$type);
        if($result){
            $this->jsonOut($result);
        }else{
            $this->jsonOut(0);
        }
    }


}
