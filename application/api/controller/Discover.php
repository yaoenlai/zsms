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
        foreach ($list AS $key=>$value){
            $list[$key]['CONTENT'] = getContent($value['CONTENT']);
        }
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
        
        $page_index = empty(input('post.page_index')) ? "1" : input("post.page_index");
        $page_size = empty(input('post.page_size')) ? "100" : input("post.page_size");
        
        $list = db('Nearby')->field("ID,X,Y,Z,TITLE,HEAD_IMG")->limit($page_size)->page($page_index)->whereOr($where)->select();
        rjson($list);
    }
    
    //获取附近详情
    public function nearbyDetail(){
        $where = [
            'ID'    => input('post.id')
        ];
        $info = db("Nearby")->where($where)->find();
        $info["CONTENT"] = getContent($info['CONTENT']);
        rjson($info);
    }
}