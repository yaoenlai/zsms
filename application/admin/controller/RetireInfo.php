<?php
namespace app\admin\controller;

class RetireInfo extends Common
{
    public function list(){
        $this->_order='ID DESC';
        $where = [];
        
        if(!empty(input('post.code'))){
            $where['CODE']  = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.is_pay'))){
            if( input('post.is_pay') == '00'){
                $where['IS_PAY']  = array("EQ", 0);
            } else {
                $where['IS_PAY']  = array("EQ", input('post.is_pay'));
            }
        }
        if(!empty(input('post.xz_code'))){
            $where['XZ_CODE']  = array("EQ", input('post.xz_code'));
        }
        if(!empty(input('post.zone_code'))){
            $where['ZONE_CODE']  = array("EQ", input('post.zone_code'));
        }
        
        $this->_where = $where;
        
        parent::list();
    }
    
    public function get_source_img(){
        if(empty(input('post.code'))) rjson_error('身份证号码为空');
            
        $path = get_source_img( input('post.code') );
        if(!empty($path)){
            rjson($path);
        } else {
            rjson_error('获取源照片失败');
        }
    }
}