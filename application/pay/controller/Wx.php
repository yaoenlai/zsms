<?php
namespace app\pay\controller;

use Lib\wx\WechatAppPay;

class Wx
{
    function notify()
    {
        //存入文件
        file_put_contents('./wx.txt', json_encode($_POST).'\r\n', 8);
        $WeChat = new WechatAppPay();
        if($WeChat->notify()){
            echo 'success';
        }
    }
}