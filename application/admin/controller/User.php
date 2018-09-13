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
}