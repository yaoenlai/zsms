<?php
namespace app\pay\controller;

use Lib\wx\WechatAppPay;

class Wx extends Common
{
    function notify()
    {
        $xml = file_get_contents('php://input');
        $result = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);
        //存入文件
        file_put_contents('./wx.txt', json_encode($result)."\r\n", 8);
        
        $this->perpay_id = $result['out_trade_no'];
        $this->pay_money = $result['total_fee'] / 100;
        $this->pay_ment = 3;
        
        $WeChat = new WechatAppPay();
        if($WeChat->notify()){
            echo 'success';
        }
    }
}