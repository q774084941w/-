<?php
namespace app\home\model;
use think\Model;
use think\Db;
class Region extends Model{
    public function _initialize(){

    }
    public function add($input,$bid){
        $data=[
            'proxy_uname'=>$input['proxyName'],
            'proxy_name'=>$input['name'],
            'proxy_tel'=>$input['phoneNumber'],
            'proxy_pass'=>md5($input['password']),
            'bid'=>$bid,
            'proxy_region'=>$input['proxyRegion'],
            'proxy_status'=>$input['enable'],
            'proxy_cretime'=>time()
        ];
      
        $region=[];

        Db::startTrans();
        $proxy_id=db('Proxy')->insertGetId($data);
        foreach ($input['serviceRegionGroup'] as $key=>$val){
            $region[$key]['int_km']=$val['weight'];
            $region[$key]['int_km_price']=$val['weightprice'];
            $region[$key]['on_km']=$val['distance'];
            $region[$key]['on_km_price']=$val['distanceprice'];
            $region[$key]['int_kg']=$val['sdistance'];
            $region[$key]['int_kg_price']=$val['sdistanceprice'];
            $region[$key]['on_kg']=$val['addstart'];
            $region[$key]['on_kg_price']=$val['addstartprice'];
            $region[$key]['location']=json_encode($val['location']);
            $region[$key]['bid']=$bid;
            $region[$key]['proxy_id']=$proxy_id;
        }

        $result=db('Region')->insertAll($region);
        if($proxy_id&&$result){
            Db::commit();
            return true;
        }else{
            Db::rollback();
            return false;
        }
    }
}