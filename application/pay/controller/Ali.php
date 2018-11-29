<?php
namespace app\pay\controller;

use Lib\aop\AopClient;

class Ali extends Common
{
    function notify()
    {
        $result = input('post.');
        //存入文件
        file_put_contents('./ali.txt', json_encode($result)."\r\n", 8);
        
        $this->perpay_id = $result['out_trade_no'];
        $this->pay_money = $result['total_amount'] / 100;
        $this->pay_ment = 2;
        
        $aop = new AopClient();
        
        $aop->alipayrsaPublicKey = config('alipay_.alipay_public_key');
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, "RSA2");
        //验签通过后再实现业务逻辑，比如修改订单表中的支付状态。
        /**
         *  ①验签通过后核实如下参数out_trade_no、total_amount、seller_id
         *  ②修改订单表
         **/
        //打印success，应答支付宝。必须保证本界面无错误。只打印了success，否则支付宝将重复请求回调地址。
        echo 'success';
    }
}