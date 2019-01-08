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
    
    //获取SEO
    public function getSeo(){
        $list = [];
        
        //当日0点的时间
        $dateStr = date('Y-m-d', time());
        $timestamp0 = strtotime($dateStr);
        //当日24点的时间
        $timestamp24 = strtotime($dateStr) + 86400; 
        
        $user_where = [
            'REG_TIME'  => array('BETWEEN',array($timestamp0 , $timestamp24))
        ];     
        $list['USER']['COUNT'] = db('User')->count();
        $list['USER']['COUNT_DAY'] = db('User')->where($user_where)->count();
        
        $card_where = [
            'IS_LOCK'       => '1'
            ,'C_ADD_TIME'   => array(array('EGT', $timestamp0), array('ELT', $timestamp24))
        ];
        $list['CARD']['COUNT_DAY'] = db('Card')->where($card_where)->count();
        $card_where = [
            'IS_LOCK'       => '1'
            ,'EXAM_TIME'    => array(array('EGT', $timestamp0), array('ELT', $timestamp24))
        ];
        $list['CARD']['COUNT_DAY_EXAM'] = db('Card')->where($card_where)->count();
        
        $retire_where = [
            'IS_LOCK'       => '1'
            ,'LIVE_STATUS'  => '1'
            ,'PERIOD_TIME'  => array(array('EGT', $timestamp0), array('ELT', $timestamp24))
        ];
        $list['RETIRE']['COUNT_DAY_PERIOD'] = db('Retire')->where($retire_where)->count();
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
        
        echo curl_post($url, $post_data);
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
        
        echo curl_post($url, $post_data);
    }
    
    public function jsonp2(){
        $url = input('post.url');
        $data = input('post.');
        unset($data['url']);
        
        $tmp_name = [];
        if(!empty($data['image_ref1'])) $tmp_name["image_ref1"] = new \CURLFile(config('file_path.retire_path').str_replace('/retire_img?path=', '', $data['image_ref1']));
        if(!empty($data['image_ref2'])) $tmp_name["image_ref2"] = new \CURLFile(config('file_path.retire_path').str_replace('/retire_img?path=', '', $data['image_ref2']));
        $post_data = array_merge($data, $tmp_name);    
        
        echo curl_post($url, $post_data);
    }
    
    public function gen_sign(){
        $apiKey = 'Ahqtp60UiHyQOfhbq5tVC3kf29NmZNTd';
        $apiSecret = 'KySRW9tHShPUhZAf3bkxb8yKR_jINZ9C';
        $expired = 100;
        
        $rdm = rand();
        $current_time = time();
        $expired_time = $current_time + $expired;
        $srcStr = "a=%s&b=%d&c=%d&d=%d";
        $srcStr = sprintf($srcStr, $apiKey, $expired_time, $current_time, $rdm);
        $sign = base64_encode(hash_hmac('SHA1', $srcStr, $apiSecret, true).$srcStr);
        rjson($sign);
    }
}