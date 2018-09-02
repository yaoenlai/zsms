<?php
namespace app\upload\controller;

use think\Controller;
use Lib\st\StUpload;

class Index extends Controller
{
    private $upload;
    public function _initialize(){
     
        $upload = new StUpload();
        $this->upload = $upload;
    }

    /**
     *   StUpload.class.php 5个方法
     *
     *  图片上传 upload
     *  文件上传 upload_file
     *  编辑器图片上传 editor_upload
     *  图片读取 img
     *  文件下载 down
     *
     */
    
    public function _empty($func){
        $this->upload->$func();
    }
}