<?php
namespace app\api\model;

use think\Db;

class AddressModel
{
    /**
     * 单例模式
     * @return UserModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new AddressModel();
        }
        return $m;
    }
    /**
     * 查询用户收货地址数量
     */
    public function address($uid,$address_type=0){
        $count = Db::name('address')  ->where('address_type',$address_type)->where('uid',$uid)->count();
        return $count;
    }
    /**
     * 添加用户收货地址
     */
    public function addressAdd($data){
        $result = Db::name('address')->insert($data);
        return $result;
    }
    /**
     * 收货地址列表
     */
    public function datalist($uid,$field,$uaid,$bid,$address_type=0){
        if(!empty($uaid)){
            $result = Db::name('address')
                ->where(['uid'=>$uid,'id'=>$uaid,'bid'=>$bid])
                ->where('address_type',$address_type)
                ->field($field)
                ->select();
        }else{
            $result = Db::name('address')
                ->where('uid',$uid)
                ->where('address_type',$address_type)
                ->field($field)
                ->select();
        }

        return $result;
    }
    /**
     * 默认地址逻辑处理
     */
    public function sitelist($data,$address_type=0){
        Db::name('address')
            ->where('address_type',$address_type)
            ->where('uid',$data['uid'])
            ->setField('default',0);
        $result = $this->addressAdd($data,$address_type);
        return $result;
    }
    /**
     * 修改收货地址
     */
    public function siteupdate($data,$uaid,$address_type=0){
        $result = Db::name('address')
            ->where('address_type',$address_type)
            ->where('id',$uaid)->update($data);
        return $result;
    }
    /**
     * 设置默认地址
     */
    public function defaultsite($uaid,$uid,$address_type=0){
        Db::name('address')
            ->where('uid',$uid)
            ->where('address_type',$address_type)
            ->setField('default',0);
        $result = Db::name('address')
            ->where('id',$uaid)
            ->where('address_type',$address_type)
            ->setField('default',1);
        return $result;

    }
    /**
     * 默认收货地址
     */
    public function defaultaddr($uid,$bid,$address_type=0){
        $result = Db::name('address')
            ->field('uaid,address,name,phone,province,city,area')
            ->where('address_type',$address_type)
            ->where(['uid'=>$uid,'bid'=>$bid,'default'=>1])
            ->find();
        return $result;
    }
    /**
     *
     * 删除当前地址
     */
    public function delAddress($uaid){
        $result = db('address')->where('id',$uaid)->delete();
        return $result;
    }
    /**
     *
     * 获取地址区域
     */
    public function area($bid){
        $result = db('run_area')
            ->where('bid',$bid)->select();
        $province = [];
        $city = [];
        $area = [];
        $p = 0;
        $c = 0;
        $a = 0;
        foreach($result as $k => $v){
            if(!in_array($v['province'],$province)) {
                $province[$p] = $v['province'];
                $city[$p] = [];
                $area[$p] = [];
                $c = 0;
                $p++;
            }
            $p1 = $p-1;
            if(!in_array($v['city'],$city[$p1])) {
                $city[$p1][$c] = $v['city'];
                $area[$p1][$c] = [];
                $a = 0;
                $c++;
            }
            $c1 = $c-1;
            if(!in_array($v['area'],$area[$p1][$c1])) {
                $area[$p1][$c1][$a] = $v['area'];
                $a++;
            }
        };
        $result = [$province,$city,$area];
        return $result;
    }

}
