<?php
namespace app\home\model;

use think\Db;

class CommtypeModel
{
    /**
     * 单例模式
     * @return CommTypeModel
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CommtypeModel();
        }
        return $m;
    }
    /**
     * 页面展示
     *
     */
    public function select(){
        $field = 'goodsid,pic,name,unit,min_price,stock,sales,status';
        $list = Db::name('goods')->field($field)->where('status',1)->select();
        foreach ($list as $key=>&$val){
            $val['pic'] = uploadpath('goods',$val['pic']);
        }
        
        return $list;
    }

    /**
     * 添加分类展示（3级）
     */
    public function add($bid){
        $field = 'tid,bid,name,name,status';
        $list = Db::name('menus')->field($field)->where(['status'=>1,'bid'=>$bid])->select();
        foreach ($list as &$va){
            $oneype = Db::name('commType')->field('name,ptid,pid,level')->where(['status'=>1,'bid'=>$bid,'tid'=>$va['tid'],'level'=>0])->select();
            
            foreach ($oneype as &$val){
                $twoype = Db::name('commType')->field('name,ptid,pid,level')->where(['status'=>1,'bid'=>$bid,'pid'=>$val['ptid'],'level'=>1])->select();
                $val['comm'] = $twoype;
            }
            $va['comm'] = $oneype;

        }
       
        return $list;
    }
    /**
     * 添加分类展示（3级）
     */
    public function addajax($bid,$id){
        $field = 'ptid,level,name,pid,status';
        $list = Db::name('commType')->field($field)->where(['status'=>1,'level'=>0,'bid'=>$bid,'tid'=>$id])->select();
        foreach ($list as $eky=>&$val){
            $listtwo = Db::name('commType')->field($field)->where(['status'=>1,'level'=>1,'pid'=>$val['ptid'],'bid'=>$bid,'tid'=>$id])->select();
            foreach ($listtwo as $k=>&$v){
                $listthr = Db::name('commType')->field($field)->where(['status'=>1,'level'=>2,'pid'=>$v['ptid'],'bid'=>$bid,'tid'=>$id])->select();
                $listtwo[$k]['sub'] = $listthr;
            }
            $val['sub'] = $listtwo;
        }
        return $list;
    }
    /**
     * 分类展示
     */
    public function commindex($bid){
        $field = 'tid,bid,name,name,status,createtime,logo,solt,updatetime';
        $list = Db::name('menus')->field($field)->where(['bid'=>$bid])->order('solt desc')->select();
        foreach ($list as $key=>&$va){
            $va['logo'] = uploadpath('commtype',$va['logo']);
            $va['updatetime'] = date('Y-m-d H:i:s',$va['updatetime']);
            $va['rank'] = $key;
            $va['status'] == 1 ? $va['oclass'] = 'openbtn' : $va['oclass'] = 'modifybtn';

            $va['status'] == 1 ? $va['operate'] = '关闭' : $va['operate'] = '开启';
            $va['status'] == 1 ? $va['sclass'] = 'putaway' : $va['sclass'] = 'sold';
            $va['status'] == 1 ? $va['statuss'] = '开启' : $va['statuss'] = '关闭';

            $oneype = Db::name('commType')->field('name,ptid,pid,level,status,pic,solt,tid,createtime,updatetime')
                ->where(['bid'=>$bid,'tid'=>$va['tid'],'level'=>0])->order('solt desc')->select();
            foreach ($oneype as $keyy=>&$val){
                $val['status'] == 1 ? $val['oclass'] = 'openbtn' : $val['oclass'] = 'modifybtn';
                $val['updatetime'] = date('Y-m-d H:i:s',$val['updatetime']);
                $val['status'] == 1 ? $val['operate'] = '关闭' : $val['operate'] = '开启';
                $val['status'] == 1 ? $val['sclass'] = 'putaway' : $val['sclass'] = 'sold';
                $val['status'] == 1 ? $val['statuss'] = '开启' : $val['statuss'] = '关闭';

                $val['pic'] = uploadpath('commtype',$val['pic']);
                $val['rank'] = $key.'_'.$keyy;
                $twoype = Db::name('commType')->field('name,ptid,pid,level,status,pic,solt,tid,createtime,updatetime')
                    ->where(['bid'=>$bid,'pid'=>$val['ptid'],'level'=>1])->order('solt desc')->select();
                foreach ($twoype as $keyyy=>&$vvv){
                    $vvv['status'] == 1 ? $vvv['oclass'] = 'openbtn' : $vvv['oclass'] = 'modifybtn';
                    $vvv['updatetime'] = date('Y-m-d H:i:s',$vvv['updatetime']);
                    $vvv['status'] == 1 ? $vvv['operate'] = '关闭' : $vvv['operate'] = '开启';
                    $vvv['status'] == 1 ? $vvv['sclass'] = 'putaway' : $vvv['sclass'] = 'sold';
                    $vvv['status'] == 1 ? $vvv['statuss'] = '开启' : $vvv['statuss'] = '关闭';

                    $vvv['pic'] = uploadpath('commtype',$vvv['pic']);
                    $vvv['rank'] = $key.'_'.$keyy.'_'.$keyyy;
                }
                $val['sub'] = $twoype;
            }
            $va['sub'] = $oneype;

        }

        return $list;
    }

    /**
     * 添加数据库
     */
    public function upload($info){
        $data = [
            'pid' => $info['Ptid'],
            'name' => $info['namm'],
            'solt' => $info['solt'],
            'level' => $info['Level'],
            'createtime' => time(),
        ];
        $list = Db::name('commType')->insert($data);
        return $list;
    }

    /**
     * 添加数据库
     */
    public function insert($data){
      
        $list = Db::name('commType')->insert($data);
        return $list;
    }
    /**
     * 开启/关闭
     */
    public function soldOut($data){
        if($data['status'] == 0){
            $status = 1;
        }elseif ($data['status'] == 1){
            $status = 0;
        }
        $off = Db::name('commType')->field('pid,ptid,level')->where(['level'=>1,'ptid'=>$data['ptid'],'status'=>0])->find();
        if($off){
            return 0;
        }
        Db::startTrans();

        $one = Db::name('commType')->where('ptid',$data['ptid'])->update(['status'=>$status,'updatetime'=>time()]);

        $two = Db::name('commType')->field('pid,ptid,level')->where(['level'=>1,'pid'=>$data['ptid']])->select();

        if($two){
            $comm = Db::name('commType')->where('pid',$data['ptid'])->update(['status'=>$status,'updatetime'=>time()]);
        }else{
            $comm = 1;
        }
        if (!$one || !$comm) {
            Db::rollback(); //回滚事务
            return 0;
        }
        //提交事务
        Db::commit();
        return 1;
    }
    /**
     * 添加分类菜单
     */
    public function addcommtype($data){
        if(!isset($data['pid'])){
            $result = Db::name('commType')->insert($data);
        }else{
            $data['level'] = 1;
            $result = Db::name('commType')->insert($data);
        }
        return $result;
    }
    /**
     * 开启关闭菜单
     */
    public function handle($data,$bid){
        if($data['status'] == 0){
            $status = 1;
        }elseif ($data['status'] == 1){
            $status = 0;
        }
        $result = Db::name('menus')->where(['tid'=>$data['tid']])->update(['status'=>$status]);
        Db::name('commType')->where(['tid'=>$data['tid'],'bid'=>$bid])->update(['status'=>$status]);
        if($result>0){
            return 1;
        }else{
            return 0;
        }
        
    }
    /**
     * 编辑
     */
    public function edit($id){
        $field = 'name,ptid,pid,level,status,pic,solt,tid,createtime';
        $list = Db::name('commType')->field($field)->where('ptid',$id['id'])->find();
        $list['pic'] = uploadpath('commtype',$list['pic']);
        return $list;
    }
    /**
     * 添加数据库  编辑
     */
    public function editinse($data){
        $result = Db::name('commType')->update($data);
        return $result;
    }
    /**
     * 编辑排序
     */
    public function editsolt($data){
        if($data['v'] == 0){
            $result = Db::name('menus')->where('tid',$data['id'])->update(['solt'=>$data['val']]);
        }else{
            $result = Db::name('commType')->where('ptid',$data['id'])->update(['solt'=>$data['val']]);
        }
        return $result;
    }


}
