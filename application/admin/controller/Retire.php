<?php
namespace app\admin\controller;

class Retire extends Common 
{
    public function list(){
        $this->_order = 'CREATE_DATE DESC';
        parent::list();
    }
      
    //获取险种
    public function getInsuranceList(){
        
        $list = db("RetireInsurance")->select();
        rjson($list);
    }
}