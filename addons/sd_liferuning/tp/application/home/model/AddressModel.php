<?php
namespace app\home\model;

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
    public function address($uid){
        $count = Db::name('address')->where('uid',$uid)->count();
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
    public function datalist($uid,$field,$uaid){
        if(empty($uaid)){
            $result = Db::name('address')->where(['uid'=>$uid,'uaid'=>$uaid])->field($field)->select();
        }else{
            $result = Db::name('address')->where('uid',$uid)->field($field)->select();
        }

        return $result;
    }
    /**
     * 默认地址逻辑处理
     */
    public function sitelist($data){
        Db::name('address')->where('uid',$data['uid'])->setField('default',0);
        $result = $this->addressAdd($data);
        return $result;
    }
    /**
     * 修改收货地址
     */
    public function siteupdate($data,$uaid){
        $result = Db::name('address')->where('uaid',$uaid)->update($data);
        return $result;
    }
    /**
     * 设置默认地址
     */
    public function defaultsite($uaid,$uid){
        Db::name('address')->where('uid',$uid)->setField('default',0);
        $result = Db::name('address')->where('uaid',$uaid)->setField('default',1);
        return $result;

    }

}
