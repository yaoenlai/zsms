<?php
namespace app\admin\controller;

class News extends Common
{
    public function list(){
        
        $where = [];
        if(!empty(input('post.class_id'))){
            $where['PID'] = array("EQ", input('post.class_id'));
        }
        
        $this->_where = $where;
        
        parent::list();
    }
}