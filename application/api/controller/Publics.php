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
    
    //张家口参保
    public function getZone(){
        $where = [];
        $list = db("zone")->where($where)->select();
        rjson($list);
    }
    
    //获取在线客服
    public function getDatum(){
        $where = [
            'IS_LOCK'   => '1'
        ];
        $list = db('Datum')->where($where)->order('SORT DESC')->select();
        rjson($list);
    }
    
    //获取民族
    public function getNation(){
        $where = [];
        $list = db("mz")->where($where)->select();
        rjson($list);
    }
    
    //获取险种
    public function getInsuranceType(){
        $where = [
            'STATUS'    => input('post.status')
        ];
        $list = db("RetireInsurance")->where($where)->select();
        rjson($list);
    }
    
    public function jsonp(){
        $url = input('post.url');
        $data = input('post.');
        unset($data['url']);

        $tmp_name = array(          
            //要上传的本地文件地址
            "image" => new \CURLFile(ROOT_PATH.'/public/'.$data['image']),
        );
        
        if(!empty($data['image_ref1'])) $tmp_name["image_ref1"] = new \CURLFile(ROOT_PATH.'/public/'.$data['image_ref1']);
        if(!empty($data['image_ref2'])) $tmp_name["image_ref2"] = new \CURLFile(ROOT_PATH.'/public/'.$data['image_ref2']);
        if(!empty($data['image_ref3'])) $tmp_name["image_ref3"] = new \CURLFile(ROOT_PATH.'/public/'.$data['image_ref3']);
        
        unset($data['image']);
        
        $post_data = array_merge($data, $tmp_name);
        
        $ch = curl_init();
        curl_setopt($ch , CURLOPT_URL , $url);
        curl_setopt($ch , CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch , CURLOPT_POST, 1);
        curl_setopt($ch , CURLOPT_POSTFIELDS, $post_data);
        curl_setopt($ch , CURLOPT_SSL_VERIFYPEER, false);
        $output= curl_exec($ch);
        if(curl_errno($ch)){
            rjson('', '400', curl_error($ch));
        }
        curl_close($ch);
        
        //删除图片
//         unlink(ROOT_PATH.'/public/'.input('post.image'));
//         if(!empty($data['image_ref1'])) unlink(ROOT_PATH.'/public/'.input('post.image_ref1'));
//         if(!empty($data['image_ref2'])) unlink(ROOT_PATH.'/public/'.input('post.image_ref2'));
//         if(!empty($data['image_ref3'])) unlink(ROOT_PATH.'/public/'.input('post.image_ref3'));
        
        echo $output;
    }
    
    public function take_cut_pic(){
        $url = input('post.url');
        $post_data = input('post.');
        unset($post_data['url']);
        
        $data = $this->http_url($url, json_encode($post_data));
        
        $image_path = './image/'.$post_data['file_name'].'.jpg';
        file_put_contents($image_path, $data);
        
        rjson(trim($image_path,'.'));
    }
       
    public function http_url($url,$data=null){
        
        $headers = array(
            'Content-Type: application/json;charset=utf-8',
            'Content-Length: ' . strlen($data),
            'y-cli:pc'
        );
        
        $curl=curl_init();
        //设置请求地址
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $output=curl_exec($curl);
        curl_close($curl);
        return $output;
    }
}