<?php
namespace app\api\controller;

use app\api\model\Card;

class Mail extends Common
{
    //获取邮寄列表
    public function mailList(){
        $data = $this->_postData;
        $where = [
            'U_ID'         => $this->_loginInfo['U_ID']
        ];
        
        if(!empty($this->_postData['is_pay'])) $where['IS_PAY'] = $this->_postData['is_pay'];
        if(!empty($this->_postData['step_status'])) $where['STEP_STSTUS'] = $this->_postData['step_status'];
        
        $page_index = empty($this->_postData['page_index']) ? '1' : $this->_postData['page_index'];
        $page_size  = empty($this->_postData['page_size']) ? '10' : $this->_postData['page_size'];
        
        $list['data'] = db('Mail')->where($where)->limit($page_size)->page($page_index)->order('ADDTIME DESC')->select();
        $list['total'] = db('Mail')->where($where)->count();
        rjson($list);
    }
    
    //社保邮寄
    public function card_mail(){
        
        $data = input('post.');
        $where = [
            'ID'            => $data['card_id'],
//             'EXAM_STATUS'   => 3,
        ];
        
        $cardInfo = db("Card")->where($where)->find();
        if( !empty($cardInfo) ){
            (new Card())->addMailOrder($this->_loginInfo['U_ID'], $cardInfo);
        } else {
            rjson('', '400', '社保号有问题');
        }
    }
    
    //确认收货
    public function sure_mail(){
        $data = input('post.');
        $where = [
            'ID'    => $data['mail_id']
        ];
        $info = db('CardMail')->where($where)->find();
        if( !empty($info) ){
            (new Card())->sure_mail($info['ID'], $info['CARD_ID']);
        } else {
            rjson('', '400', '邮寄号有问题');
        }
    }
}