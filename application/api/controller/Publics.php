<?php
namespace app\api\controller;

use think\Controller;

class  Publics extends Controller
{
    //获取协议信息
    public function get_card_protocol(){
        
        $data = input('post.');
        $where = [
            'pid'       => $data['pid'],
            'is_lock'   => 1,
        ];
        $info = db("protocol_list")->where($where)->order("id desc")->find();
        if(!empty($info)){
            rjson($info);
        } else {
            rjson('', '400', '获取失败');
        }
    }
    //获取监护人类别列表
    public function get_relation_list(){
        $where = [];
        $list = db("relation")->where($where)->select();
        rjson($list);
    }
    //获取字典内容信息
    public function get_set_list(){
        $data = input('post.');
        if(empty( $data['key'] ))
            rjson('', '400', '参数不能为空');
            
        $where = [
            'key'   => $data['key'],
        ];
        $list = db("set_info")->where($where)->select();
        if(!empty($list)){
            rjson($list);
        } else {
            rjson('', '400', '获取失败');
        }
    }
    //获取快递列表
    public function getExpress(){
        $where = [
            'IS_LOCK'   => '1'
        ];
        $list = db('Express')->where($where)->select();
        rjson($list);
    }
    //获取省市区
    public function getArea(){
        $parent_id = empty(input('post.parent_id')) ? 0 : input('post.parent_id');
        $where = [
            'PARENT_ID' => $parent_id,
        ];
        $list = db('Area')->where($where)->select();
        rjson($list);
    }
    
    public function jsonp(){
        $url = input('post.url');
        $data = input('post.');
        unset($data['url']);
        if(empty($_FILES['image']['tmp_name'])){
            rjson('', '400', '图片错误');
        }
        copy($_FILES['image']['tmp_name'], './image.jpg');
        
        $tmp_name = array(          
            //要上传的本地文件地址
            "image" => new \CURLFile('./image.jpg'),
        );
        
        $post_data = array_merge($data, $tmp_name);
        
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_POST, 1);
        curl_setopt($ch , CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch , CURLOPT_SSL_VERIFYPEER, false);
        $output= curl_exec($ch);
        
        curl_close($ch);
        //删除图片
        unlink('./image.jpg');
        
        echo $output;
    }
}