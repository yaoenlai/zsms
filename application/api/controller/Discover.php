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
    
    //获取附近列表
    public function nearbyList(){
        $data = input('post.');
        $distance = $data['distance'];
        $max_X = $data['fP1Lon'] + $distance;
        $min_X = $data['fP1Lon'] - $distance;
        $max_Y = $data['fP1Lat'] + $distance;
        $min_Y = $data['fP1Lat'] - $distance;
        
        $where = [
            'X'     => ['BETWEEN',[$min_X,$max_X]]
            ,'Y'    => ['BETWEEN',[$min_Y,$max_Y]]
        ];
        $list = db('Nearby')->field("ID,X,Y,Z,TITLE,HEAD_IMG")->whereOr($where)->select();
        rjson($list);
    }
    
    //获取附近详情
    public function nearbyDetail(){
        $where = [
            'ID'    => input('post.id')
        ];
        $info = db("Nearby")->where($where)->find();
        rjson($info);
    }
}