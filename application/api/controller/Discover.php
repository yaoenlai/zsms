<?php
/* *
 * 发现类
 *  */
namespace app\api\controller;

use think\Controller;

class Discover extends Controller
{
    //获取类型列表
    public function getClass(){
        $list = db("DiscoverClass")->where(['IS_LOCK'=>'1'])->select();
        rjson($list);
    }
    
    //获取内容列表
    public function getList(){
        $data = input('post.');
        $where = [
            'IS_LOCK'   => '1',
            'PID'       => $data['pid'],
        ];
        $list = db('Discover')->where($where)->select();
        rjson($list);
    }
}