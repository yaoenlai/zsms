<?php
use think\Config;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 取得IP
 *
 * @return string 字符串类型的返回结果
 */
function getIp(){
    if (@$_SERVER['HTTP_CLIENT_IP'] && $_SERVER['HTTP_CLIENT_IP']!='unknown') {
        $ip = $_SERVER['HTTP_CLIENT_IP'];
    } elseif (@$_SERVER['HTTP_X_FORWARDED_FOR'] && $_SERVER['HTTP_X_FORWARDED_FOR']!='unknown') {
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        $ip = $_SERVER['REMOTE_ADDR'];
    }
    return preg_match('/^\d[\d.]+\d$/', $ip) ? $ip : '';
}

//极光推送个人
function pushMessages($pushId,$content){
    vendor('jpush.JPush');
    
    $jPush = Config::get('jpush');
    $app_key = $jPush['key'];
    $master_secret = $jPush['secret'];
    $client = new JPush($app_key, $master_secret);
    $result = $client->push()
        ->setPlatform('all')
        ->addRegistrationId($pushId)
        ->setNotificationAlert($content)
        ->send();
    // print_r($response);
}
//极光推送全部
function pushMessage($content){
    vendor('jpush.JPush');
    
    $jPush = Config::get('jpush');
    $app_key = $jPush['key'];
    $master_secret = $jPush['secret'];
    $client = new JPush($app_key, $master_secret);
    $result = $client->push()
        ->setPlatform('all')
        ->addAllAudience()
        ->setNotificationAlert($content)
        ->send();
    //print_r($response);
}

/**
 * @name  区县ID获取省市区名称
 * @param string $countyID 区县ID
 * @return array 省市区
 */
function getCItyName($countyID){
    $where = [
        'a1.AREA_ID'    => (int)$countyID
    ];
    $info = db('Area')->alias('a1')
                ->field('a3.AREA_NAME AS PROVINCE_NAME, a2.AREA_NAME AS CITY_NAME, a1.AREA_NAME AS COUNTY_NAME')
                ->join('sb_area a2', 'a2.AREA_ID = a1.PARENT_ID', 'LEFT')
                ->join('sb_area a3', 'a3.AREA_ID = a2.PARENT_ID', 'LEFT')
                ->where($where)
                ->find();
    if(empty($info)){
        return [
            'PROVINCE_NAME' => null
            ,'CITY_NAME'    => null
            ,'COUNTY_NAME'  => null
        ];
    } else {
        unset($info['NUMROW']);
        return $info;
    }
}

/*
 * 生成随机字符串
 * @param int $length 生成随机字符串的长度
 * @param string $char 组成随机字符串的字符串
 * @return string $string 生成的随机字符串
 */
function str_rand($length = 32, $char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ') {
    if(!is_int($length) || $length < 0) {
        return false;
    }
    
    $string = '';
    for($i = $length; $i > 0; $i--) {
        $string .= $char[mt_rand(0, strlen($char) - 1)];
    }
    
    return $string;
}

/** 
 * 获取源图片
 * @param string $code 身份证号码
 * @param string $xz_code 险种编码
 * @param string $zone_code 参保区域编码
 *  */
function get_source_img($code){
    
    $path = "./image/source_img/{$code}.jpg";
    
    $where = [
        'CODE'    => $code
    ];
    $info = db("RetireImg")->where($where)->find();
    if(empty($info)){
        return false;
    }
    $obj = stream_get_contents($info['IMG']);
    //创建文件夹
    if(!file_exists(dirname($path))){
        mkdir(dirname($path));
    }
    
    if(file_put_contents($path, $obj)){
        return trim($path,'.');
    } else {
        return false;
    }
}
/** 
 * 通知消息
 * @param string $title 标题
 * @param string $title 内容
 * @param string $type 消息类型 1、用户发送；2、系统发送
 * @param string $u_id 接受用户U_ID
 *  */
function msg_add($title='', $content='', $u_id, $type='2'){
    $insert = [
        'TITLE'     => $title
        ,'CONTENT'  => $content
        ,'TYPE'     => $type
        ,'U_ID'     => $u_id
        ,'ADDTIME'  => time()
        ,'ADDDATE'  => date("Y-m-d H:i:s")
    ];
    if(db('MsgBak')->insert($insert)){
        return true;
    } else {
        return false;
    }
}
