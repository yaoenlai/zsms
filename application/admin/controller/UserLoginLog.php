<?php
namespace app\admin\controller;

class UserLoginLog extends Common
{
    
    public function list()
    {
        $this->_order = 'CREATE_TIME DESC';
        $where = [];
        if(!empty(input('post.u_id'))){
            $where['PID'] = array('EQ', input('post.u_id'));
        }
        $this->_where = $where;
        
        parent::list();
    }
}