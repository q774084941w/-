<?php
namespace app\index\model;

use think\Controller;
use think\Request;

class CommonModel
{
    /**
     * 单例模式（控制器调用 类名::instance()->方法名）
     * @return Common|null
     *
     */
    public static function instance(){
        static $m = null;
        if(!$m){
            $m = new CommonModel();
        }
        return $m;
    }
    /**
     * 多图上传
     * @return Common|null
     *
     */
    public function upload($filename=''){
        // 获取表单上传文件
        $files = request()->file('image');
        foreach($files as $file){
            // 移动到框架应用根目录/public/uploads/ 目录下
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads'. DS . $filename ,randCode(6));
            if($info){
                // 成功上传后 获取上传信息
                // 输出 42a79759f284b767dfcb2a0197904287.jpg
                return $info->getFilename();
            }else{
                // 上传失败获取错误信息
                return $file->getError();
            }
        }
    }

}
