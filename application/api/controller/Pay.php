<?php
namespace app\api\controller;

use Lib\aop\AopClient;
use Lib\aop\request\AlipayTradeAppPayRequest;
use Lib\wx\WechatAppPay;

class Pay
{
 
    public $return_data = [];
    
    //暂时废弃
    public function pay_type(){
        $data = input('post.');
        
        switch ($data['type'])
        {
            case '1':
                break;
            case '2':
                $this->aliapi($data);
                break;
            case '3':
                break;
        }
    }
    
    public function aliapi(){
        $data = input('post.');
        
        $aop = new AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = config('alipay.app_id');
        $aop->rsaPrivateKey = config("alipay.merchant_private_key");
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = "RSA2";
        $aop->alipayrsaPublicKey = config('alipay.ali_private_key');
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new AlipayTradeAppPayRequest(); 
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        switch ($data['total_amount_type']){
            case '1':$data['total_amount'] = get_price('1');break;
            case '2':$data['total_amount'] = get_price('2');break;
            case '3':$data['total_amount'] = get_price('3');break;
            default:$data['total_amount'] = "0.01";
        }
        $bizcontent = "
                        {\"body\":\"掌上名生支付\","
                        . "\"subject\": \"{$data['subject']}\","
                        . "\"out_trade_no\": \"{$data['out_trade_no']}\","
                        . "\"timeout_express\": \"30m\","
                        . "\"total_amount\": \"{$data['total_amount']}\","
                        . "\"product_code\":\"QUICK_MSECURITY_PAY\""
                        . "}
                        ";
        $request->setNotifyUrl(config('alipay.notify_url'));
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        
        $this->return_data = $response;
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
//         echo htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
    }
    
    
    public function wxpay(){
        $data = input('post.');
        
        
        $WeChat = new WechatAppPay();
        
        switch ($data['total_fee_type']){
            case '1':$data['total_fee'] = get_price('1');break;
            case '2':$data['total_fee'] = get_price('2');break;
            case '3':$data['total_fee'] = get_price('3');break;
            default:$data['total_fee'] = "0.01";
        }
        $param['total_fee'] = $data['total_fee']*100;
        $arr = $WeChat->wechat_pay($data['body'], $data['out_trade_no'], $param['total_fee']);
        $this->return_data = $arr;
    }
    
    public function __destruct()
    {
        $perpay_id = input('post.out_trade_no');
        $where = [
            'PREPAY_ID' => $perpay_id
        ];
        $info = db("Order")->where($where)->find();
        
        $pid = [];
        switch ($info['TYPE']){
            case '1':
                $pid = db('Card')->where($where)->value("ID");
                break;
            case '2':
                $pid = db('CardMail')->where($where)->value("ID");
                break;
            case '3':
                $pid = db("Retire")->where($where)->value("ID");
                break;
        }
        $data = [
            'pay'   => $this->return_data
            ,'id'   => $pid
        ];
        rjson($data);
    }
}