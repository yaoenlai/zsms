<?php
namespace app\admin\controller;

class Publics extends Common
{
    //获取文件
    public function getContent(){
        $path = input('post.path');
        if(file_exists ($path)){
            rjson(file_get_contents($path));
        } else {
            rjson_error('路径不存在【'.$path.'】');
        }
    }
    
    //生成文件
    public function putContent(){
        $path = input('post.path');
        $content = input('post.content');
        
        if(empty($path)){
            $path = './uploads/news/'.date('Ymd').'/'.md5(time().rand('1','100')).'.txt';
        }

        if(! file_exists(dirname($path)) ){
            mkdir ( dirname($path), '0777' , true);
        }
        if( file_put_contents($path, $content)){
            rjson($path);
        } else {
            rjson_error('意外错误');
        }
    }
}