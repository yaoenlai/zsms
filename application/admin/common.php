<?php
use think\Hook;

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
    
    echo json_encode(array('code'=>$code,'msg'=>$msg,'data'=>$data));die;
}

function rjson_error($msg){
    rjson('', '400', $msg);
}

/** 
 * 行为方法
 * @param string $name 行为名称
 * @param string $admin_id 操作人ID
 * @param string $table 操作表
 * @param string $type 操作类型 1、新增；2、编辑；3、删除；31、软删除；32、软还原；4、查询
 * @param array $where 条件
 * @param array $data 请求参数
 *  */
function behavior($name, $admin_id, $table, $type, $where=[], $data=[]){
    $params = [
        'admin_id'  => $admin_id
        ,'table'    => $table
        ,'type'     => $type
        ,'where'    => $where
        ,'data'     => $data
    ];
    Hook::listen($name, $params);
}
