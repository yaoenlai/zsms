<?php
namespace app\admin\controller;

class Nav extends Common
{
    public function list(){
        
        $where = [
            
        ];
        $list = db('Nav')->where($where)->select();
        rjson($list);
    }
    
    public function get_nav_list(){
        $where = [
            'PID'       => '0'
            ,'IS_LOCK'  => '1'
        ];
        if($this->group_id == '1'){
            
        } else {
            $nav_list = [];
            foreach (db("GroupNav")->field('NAV_ID')->where(['GROUP_ID'=>$this->group_id])->select() AS $value){
                $nav_list[] = $value['NAV_ID'];
            }
        }
        $list= db('Nav')->where($where)->select();
        
        foreach ($list as $key => $value){
            $where = [
                'PID'       =>$value['ID']
                ,'IS_LOCK'  => '1'
            ];
            if(!empty($nav_list)){
                $where['ID'] = array("IN", $nav_list);
            }
            $list[$key]['children'] = db('Nav')->where($where)->select();
            if(count($list[$key]['children']) <= 0){
                unset($list[$key]);
            }
        }
        rjson($list);
    }
}