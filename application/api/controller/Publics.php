<?php
namespace app\api\controller; 

use think\Controller;

class  Publics extends Controller
{
    //获取最新版本号
    public function getVersion(){
        $where = [
            'TYPE'  => input('post.type')
        ];
        $info = db("Version")->where($where)->order('CREATE_TIME DESC')->find();
        rjson($info);
    }
    
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
        $parent_id = empty(input('post.parent_id')) ? 100000 : input('post.parent_id');
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
    
    //获取设置费用
    public function get_set_price(){
        $list = get_price();
        rjson($list);
    }
    
    //获取社保卡进度
    public function getSchedule(){
        if (empty(input('post.code'))) rjson('', '400', '请输出身份证号');
        
        $where = [
            'c.IS_LOCK'   => '1'
            ,'c.C_CODE'   => input('post.code') 
        ];
        $list = db('Card')->alias('c')
            ->field("
                c.C_NAME,c.C_CODE,c.EXAM_STATUS,c.IS_IMPORT,c.IS_EXPRESS
                ,CASE 
                	WHEN c.IS_IMPORT=1 THEN c.PULL_ADDRESS 
                	WHEN c.IS_IMPORT=0 THEN 
                		CASE 
                			WHEN c.IS_EXPRESS=3 THEN d.ADDRESS 
                			ELSE '自提/未选择'
                		END
                END AS ADDRESS
            ")
            ->join('sb_dot d', 'd.ID=c.D_ID', 'LEFT')
            ->where($where)->select();
        rjson($list);
    }
    
    public function jsonp(){
        $url = input('post.url');
        $data = input('post.');
        unset($data['url']);

        $tmp_name = array(          
            //要上传的本地文件地址
            "image" => new \CURLFile(config('file_path.retire_path').str_replace('/retire_img?path=', '', $data['image'])),
        );
        
        if(!empty($data['image_ref1'])) $tmp_name["image_ref1"] = new \CURLFile(config('file_path.retire_path').str_replace('/retire_img?path=', '', $data['image_ref1']));
        if(!empty($data['image_ref2'])) $tmp_name["image_ref2"] = new \CURLFile(config('file_path.retire_path').str_replace('/retire_img?path=', '', $data['image_ref2']));
        if(!empty($data['image_ref3'])) $tmp_name["image_ref3"] = new \CURLFile(config('file_path.retire_path').str_replace('/retire_img?path=', '', $data['image_ref3']));
        
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
     
        echo $output;
    }
    
    public function card_jsonp(){
        $url = input('post.url');
        $data = input('post.');
        unset($data['url']);
        
        $tmp_name = array(
            //要上传的本地文件地址
            "image" => new \CURLFile(config('file_path.card_path').str_replace('/card_img?path=', '', $data['image'])),
        );
        
        if(!empty($data['image_ref1'])) $tmp_name["image_ref1"] = new \CURLFile(config('file_path.card_path').str_replace('/card_img?path=', '', $data['image_ref1']));
        if(!empty($data['image_ref2'])) $tmp_name["image_ref2"] = new \CURLFile(config('file_path.card_path').str_replace('/card_img?path=', '', $data['image_ref2']));
        if(!empty($data['image_ref3'])) $tmp_name["image_ref3"] = new \CURLFile(config('file_path.card_path').str_replace('/card_img?path=', '', $data['image_ref3']));
        
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
        
        echo $output;
    }
    
}