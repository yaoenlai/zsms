<?php
/** 
 * 退休类
 *  */
namespace app\api\controller;

class Retires extends Common
{
    public function index(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
        ];
        $list = db('Retire')->where($where)->select();
        rjson($list);
    }
    
    public function add(){
        
    }
    
    public function order_pay(){
        
    }
    
    public function edit(){
        
    }
}