<?php
namespace app\admin\controller;

class CardMail extends Common
{
    public function list(){

        $where = [];
        if(!empty(input('post.c_name'))){
            $where['C_NAME'] = array("LIKE", '%'.input('post.c_name').'%');
        }
        if(!empty(input('post.code'))){
            $where['C_CODE'] = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.express_num'))){
            $where['EXPRESS_NUM'] = array("LIKE", '%'.input('post.express_num').'%');
        }
        if(!empty(input('post.express_name'))){
            $where['NAME'] = array("LIKE", '%'.input('post.express_name').'%');
        }
        if(!empty(input('post.is_pay'))){
            $where['IS_PAY'] = array("EQ", input('post.is_pay'));
        }
        if(!empty(input('post.step_status'))){
            $where['STEP_STSTUS'] = array("EQ", input('post.step_status'));
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['ADDTIME'] = array(array('EGT', input('post.date_value_0')), array('ELT',input('post.date_value_1')) );
        } elseif ( !empty(input('post.date_value_0')) ){
            $where['ADDTIME'] = array('EGT', input('post.date_value_0'));
        } elseif ( !empty(input('post.date_value_1')) ){
            $where['ADDTIME'] = array('ELT', input('post.date_value_1'));
        }
        
        $this->_model = "Mail";
        $this->_where = $where;
        
        parent::list();
        
    }
    
    public function getExpress(){
        $where = [];
        $list = db('Express')->where($where)->select();
        rjson($list);
    }
}