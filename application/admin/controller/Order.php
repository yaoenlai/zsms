<?php
namespace app\admin\controller;

class Order extends Common
{
    public function list(){
        $where = [];
        if(!empty(input('post.code'))){
            $where['USER_CODE'] = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.code2'))){
            $where['C_CODE'] = array("LIKE", '%'.input('post.code2').'%');
        }
        if(!empty(input('post.pay_type'))){
            $where['TYPE'] = array("EQ", input('post.pay_type'));
        }
        if(!empty(input('post.payment'))){
            $where['PAYMENT'] = array("EQ", input('post.payment'));
        }
        if(!empty(input('post.pay_status'))){
            $where['STATUS'] = array("EQ", input('post.pay_status'));
        }
        
        if( !empty(input('post.min_price')) && !empty(input('post.max_price')) ){
            $where['PRICE'] = array('BETWEEN',array(input('post.min_price'),input('post.max_price')));
        } elseif (!empty(input('post.min_price'))){
            $where['PRICE'] = array("EGT", input('post.min_price'));
        } elseif (!empty(input('post.max_price'))){
            $where['PRICE'] = array("ELT", input('post.max_price'));
        }        
        
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['FINISH_TIME'] = array(array('EGT', input('post.date_value_0')), array('ELT',input('post.date_value_1')) );
        } elseif ( !empty(input('post.date_value_0')) ){
            $where['FINISH_TIME'] = array('EGT', input('post.date_value_0'));
        } elseif ( !empty(input('post.date_value_1')) ){
            $where['FINISH_TIME'] = array('ELT', input('post.date_value_1'));
        }
        
        $this->_order = "FINISH_TIME DESC";
        $this->_where = $where;
        $this->_model = "Order2";
        
        parent::list();
    }
    
    //统计订单
    public function count_order(){
        $where = [];
        if(!empty(input('post.pay_type'))){
            $where['TYPE'] = array("EQ", input('post.pay_type'));
        }
        if(!empty(input('post.payment'))){
            $where['PAYMENT'] = array("EQ", input('post.payment'));
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['finish_time'] = array('BETWEEN',array(input('post.date_value_0'),input('post.date_value_1')));
        } else {
            rjson_error("必须传时间");
        }
        
        $list = db('Order')->field("
            SUM(CASE WHEN TYPE=1 THEN PRICE END) AS CARD_PRICE
            ,SUM(CASE WHEN TYPE=2 THEN PRICE END) AS MAIL_PRICE
            ,SUM(CASE WHEN TYPE=3 THEN PRICE END) AS RETIRE_PRICE
            ,CASE 
            	WHEN PAYMENT=1 THEN '云支付'
            	WHEN PAYMENT=2 THEN '支付宝'
            	WHEN PAYMENT=3 THEN '微信'
            	ELSE '未支付'
            END AS PAYMENT_NAME
            ")->where($where)->group('PAYMENT')->select();
        rjson($list);
    }
}