<?php
namespace app\api\controller; 

use think\Controller;

class Update extends Controller
{
    public function update_file()
    {
        file_put_contents('./file.txt', json_encode($_FILES));
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(ROOT_PATH . 'public' . DS . 'uploads');
            if($info){
                // 成功上传后 获取上传信息
                $return = array(
                    'ext'       => $info->getExtension(),
                    'path'      => '/uploads/'.$info->getSaveName(),
                    'file_name' => $info->getFilename()
                );;
                rjson($return);
            }else{
                // 上传失败获取错误信息
                rjson('', '400', $file->getError());
            }
        }
    }
}