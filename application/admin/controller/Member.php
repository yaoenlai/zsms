<?php

namespace app\admin\controller;

use think\Controller;

class Member extends Controller
{

    
    public function login(){
        
        $where = [
            'USERNAME'  => input('post.username'),
            'PWD'       => md5(input('post.password')),
        ];
        $admin_id = db('admin')->where($where)->value("ADMIN_ID");
        if(!empty($admin_id)){
            $insert = array(
                'ADMIN_ID'      => $admin_id,
                'ADMIN_S_TIMIE' => time(),
                'ADMIN_S_IP'    => getIp(),
            );
            $where = [
                'ADMIN_ID'=>$admin_id
            ];
            //删除历史登录session
            db("AdminSession")->where($where)->delete();
            //添加最新登录session
            if( db("AdminSession")->insert($insert) ){
                $login_info = db("AdminSession")->where($where)->find();
                rjson($login_info);
            } else {
                rjson('', '400', '登录失败,未知错误');
            }
        } else {
            rjson('', '400', '登录失败,账号密码错误');
        }
    }
    
    public function logout(){
        
        $Auth = !empty($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
        if(!empty($Auth)){
            $where = [
                'ADMIN_S_ID' => $Auth,
            ];
            if( db("AdminSession")->where($where)->delete() ){
                rjson('登出成功');
            } else {
                rjson('', '400', '登出失败，未知错误');
            }
        } else {
            rjson('', '400','登录信息不存在');
        }
    }
}