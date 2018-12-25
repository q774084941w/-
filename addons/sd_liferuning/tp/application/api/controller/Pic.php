<?php

namespace app\api\Controller;

use think\Controller;
use think\image;
use app\api\controller\Imges;
class Pic extends Controller{

		public function pic(){
         
            // if($_FILES['file']['error']==0){
            //  $file =request()->file('file');
            //  //echo json_encode(var_dump($file));die;
            // // 移动到框架应用根目录/public/uploads/ 目录下
            // $info = $file->move(ROOT_PATH.'public'.DS.'upload1');
            // echo $info->getExtension();
            // // 输出 20160820/42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getSaveName();
            // // 输出 42a79759f284b767dfcb2a0197904287.jpg
            // echo $info->getFilename(); 
           // var_dump($info);
        //     if($info){
        //     // 成功上传后 获取上传信息
        //     $input['file'] = '/uploads/'.date('Ymd').'/'.$info->getFilename();
        //     //var_dump($input['file'])
        //     }else{
        // // 上传失败获取错误信息
      
        //      echo $file->getError();
        // }
           
        }
}




