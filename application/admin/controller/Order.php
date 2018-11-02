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
        
        if( !empty(input('post.min_price')) && !empty(input('post.max_price')) ){
            $where['PRICE'] = array('BETWEEN',array(input('post.min_price'),input('post.max_price')));
        } elseif (!empty(input('post.min_price'))){
            $where['PRICE'] = array("EGT", input('post.min_price'));
        } elseif (!empty(input('post.max_price'))){
            $where['PRICE'] = array("ELT", input('post.max_price'));
        }
        
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['finish_time'] = array('BETWEEN',array(input('post.date_value_0'),input('post.date_value_1')));
        }
        
        $page_index = empty(input('post.page_index')) ? "1" : input("post.page_index");
        $page_size = empty(input('post.page_size')) ? "20" : input("post.page_size");
        
        $data = [];
        $data['list'] = db("Order2")->where($where)->limit($page_size)->page($page_index)->order('CREATE_TIME DESC')->select();
        $data['total'] = db('Order2')->where($where)->count();
        
        rjson($data);
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