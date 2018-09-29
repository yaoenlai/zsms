<?php
/** 
 * 会员类
 *  */
namespace app\admin\controller;

class User extends Common
{
    public function list(){
        $this->_order = 'REG_TIME desc';
        
        parent::list();
    }
    
    public function detail(){
        $id = input('post.id');
        $where = [
            "ID"    => $id,
        ];
        $info = db("User")->where($where)->find();
        rjson($info);
    }
    
    public function shebao(){
        $where = [
            'ID'    => input('post.id')
        ];
        
        $save = [
            'USER_TYPE' => '1'
        ];
        
        if( db('User')->where($where)->update($save) ){
            rjson('设置社保人员成功');
        } else {
            rjson_error('设置失败');
        }
    }
    
    public function puton(){
        $where = [
            'ID'    => input('post.id')
        ];
        
        $save = [
            'USER_TYPE' => '0'
        ];
        
        if( db('User')->where($where)->update($save) ){
            rjson('设置普通人员成功');
        } else {
            rjson_error('设置失败');
        }
    }
}