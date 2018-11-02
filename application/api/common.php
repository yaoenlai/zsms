<?php
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
function rjson($data, $code='200', $msg='success'){
    
    echo json_encode(array('status'=>$code,'msg'=>$msg,'data'=>$data));die;
}
/**
 * 获取用户注册错误信息
 * @param  integer $code 错误编码
 * @return string        错误信息
 */
function showRegError($code = 0){
    switch ($code) {
        case -1:  $error = '此手机号已经被注册,请更换'; break;
        case -2:  $error = '手机号不可为空!'; break;
        case -3:  $error = '验证码发送成功,请查收!'; break;
        case -4:  $error = '验证码发送失败,请重试!'; break;
        case -5:  $error = '您刚刚已经获取了,请等待查收!'; break;
        case -6:  $error = '当前输入手机号未注册,确认前去注册'; break;
        case -7:  $error = '验证码不正确或已过期,请重试'; break;
        case -8:  $error = '注册成功,前去登录'; break;
        case -9:  $error = '用户名或者密码错误'; break;
        case -10:  $error = '登陆成功'; break;
        case -11:  $error = '账户被冻结,暂时无法登陆'; break;
        case -12:  $error = '登录状态失效请重新登录'; break;
        case -13:  $error = '您录入的身份信息已经被注册,请更换或联系APP客服'; break;
        case -14:  $error = '您的实名认证已经成功'; break;
        case -15:  $error = '获取成功'; break;
        case -16:  $error = '数据库操作失败'; break;
        case -17:  $error = '传入参数不全，请检查'; break;
        case -88:  $error = '网络请求失败,请重试'; break;
        default:  $error = '未知错误';
    }
    return $error;
}

/*
 更新社保卡状态
 */
function card_status(string $cid, string $status, string $info){
    $status = array(
        'C_ID'          => $cid,
        'EXAM_STATUS'   => $status,
        'EXAM_INFO'     => $info,
        'IS_PUSH'       => 1,
        'ADDTIME'       => time(),
    );
    return $status;
}

/*
 给用户发消息
 */
function user_msg($uid,$title,$content,$type){
    $adds =array(
        'U_ID'      => $uid,
        'TITLE'     => $title,
        'CONTENT'   => $content,
        'TYPE'      => $type,
        'STATUS'    => 1,
        'ADDTIME'   => time(),
    );
    return $adds;
}
/* 
 * 获取价格
 *  */
function get_price($type=''){
    $where = [];
    $list = db('SetPrice')->where($where)->select();
    if(!empty($type)){
        foreach ($list as $key=>$value){
            if($value['TYPE'] == $type){
                return $value['PRICE'];
            }
        }
    } else {
        return $list;
    }
}

/*
 * 获取文章详情
 *   */
function getContent($path){
    if(! file_exists(dirname($path)) ){
        return $path;
    } else {
        return file_get_contents($path);
    }
}