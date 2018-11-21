<?php
/** 
 * 通知消息类
 *  */
namespace app\admin\controller;

class Msg extends Common
{
    public function list(){
        
        $where = [];
        
        if(!empty(input('post.code'))){
            $where['CODE'] = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.name'))){
            $where['USERNAME'] = array("LIKE", '%'.input('post.name').'%');
        }
        if(!empty(input('post.phone'))){
            $where['PHONE'] = array("LIKE", '%'.input('post.phone').'%');
        }
        
        $this->_where = $where;
        
        parent::list();
        
    }
}