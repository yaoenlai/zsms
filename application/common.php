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
