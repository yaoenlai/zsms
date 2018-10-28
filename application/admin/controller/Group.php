<?php
namespace app\admin\controller;

use think\Db;

class Group extends Common
{
    public function getNavList(){
        //获取全部权限
        $where = [
            'PID'       => '0'
            ,'IS_LOCK'  => '1'
        ];
        $list['list']= db('Nav')->where($where)->select();
        foreach ($list['list'] as $key => $value){
            $list['list'][$key]['children'] = db('Nav')->where(['PID'=>$value['ID'],'IS_LOCK'  => '1'])->select();;
        }
        //获取已有权限
        $where = [
            'GROUP_ID'  => input('post.ID')
        ];
        $data = db('GroupNav')->where($where)->select();
        foreach ($data AS $value){
            $list['detail'][] = $value['NAV_ID'];
        }
        
        rjson($list);
    }   
    
    //修改权限
    public function editNav(){
        $data = input('post.');
        $group_id = array_pop($data);

        Db::startTrans();
        try {
            $where = [
                'GROUP_ID'  => $group_id
            ];
            db('GroupNav')-> where($where)->delete();
            
            foreach ($data AS $value){
                $insert = [
                    'GROUP_ID'  => $group_id
                    ,'NAV_ID'   => $value
                ];
                
                if(db("GroupNav")->insert($insert)){
                    
                } else {
                    exception("出现错误");
                }
            }
            Db::commit();
            rjson('修改权限成功');
        } catch (\Exception $e){
            Db::rollback();
            rjson_error($e->getMessage());
        }
    }
}