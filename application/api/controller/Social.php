<?php
/**
 * 社保 
 *  */
namespace app\api\controller;

use think\Controller;
use app\api\model\Card;
use think\Db;
use app\api\model\Retire;

class Social extends Common
{
    
    //验证是否申请过社保卡(开始申请)
    public function card_validate(){
        //验证用户扫描的身份证是否已经申请过社保卡
        $where = array(
            'c_code'    => input('post.idcard'),
        );
        if(db("card")->where($where)->count()){
            rjson('', '400', '此身份信息已经有办理/申请记录,不可继续申请了!');
        } else {
            rjson('此身份信息可以录入');
        }
    }
    
    //生成临时订单
    public function temp_order(){
        $where = array(
            "u_id"      => $this->_loginInfo['U_ID'],
            "STATUS"    => '2',
            "STATE"     => '1',
            "TYPE"      => input('post.type'),
            "DELETED"   => '0',
        );
        $info = db("order")->field("id,prepay_id,numbers")->where($where)->find();
        //验证是否有未完成订单
        if(!empty($info)){
            //废弃未完成订单
            if(! db('order')->where($where)->update(array('DELETED'=>'1')) ){   
                
                rjson('', '400', showRegError(-16));
            }
        }
        
        $userInfo = db('user')->field("id,username,phone")->where([ "id"=>$this->_loginInfo["U_ID"] ])->find();
        (new Card())->addOrder($userInfo);
    }

    //支付订单 （回调获取）
    public function pay_order(){
        switch (input('post.pay_type')){
            case '1': 
                $this->pay_card_order();
                break;
            case '2':
                $this->pay_card_mail_order();
                break;
            case '3':
                $this->pay_retires_order();
                break;
            default:
                rjson('未知支付类型, pay_type:['.input('post.pay_type').']');
                break;
        }
    }
    
    //支付社保订单
    private function pay_card_order(){
        $where = array(
            "prepay_id" => input('post.prepay_id'),
        );
        $info = db("CardOrderBak")->where($where)->find();
        if(!empty($info)){
            
            (new Card())->addCard($info);
        } else {
            rjson('', '400', '该订单有问题，请检查');
        }
    }
    
    //社保邮寄费用支付
    private function pay_card_mail_order(){
        $where = array(
            "prepay_id" => input('post.prepay_id'),
        );
        $info = db("CardMail")->where($where)->find();
        if( !empty($info) ){
            
            (new Card())->addCardMailPay($info["ID"],$info['CARD_ID']);
        } else {
            rjson('', '400', '该订单有问题，请检查');
        }
    }
    
    //退休支付
    private function pay_retires_order(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'PREPAY_ID' => $this->_postData['prepay_id'],
        ];
        $info = db('Retire')->where($where)->find();
        if( !empty($info) ){
            (new Retire())->orderPay($info['ID']);
        } else {
            rjson('', '400', '请检查订单号');
        }
    }
    
    //录入监护人信息/修改监督人信息
    public function guardian(){
        $data = input('post.');
        
        $gua_list = [
            'GUARDIAN_NAME'         => $data['guardian_name'],
            'GUARDIAN_PHONE'        => $data['guardian_phone'],
            'GUARDIAN_CARD'         => $data['guardian_card'],
            'GUARDIAN_SEX'          => $data['guardian_sex'],
            'GUARDIAN_ADDRESS'      => $data['guardian_address'],
            'GUARDIAN_START_TIME'   => strtotime($data['guardian_start_time']),
            'GUARDIAN_END_TIME'     => strtotime($data['guardian_end_time']),
            'GUARDIAN_RELATION'     => $data['guardian_relation']
        ];
        
        //判断监护人是否已填写
        if( db('guardian')->where([ 'PID'=>$data['card_id'] ])->count() ){
            $where = [
                'PID'   => $data['card_id']
            ];
            $gua_list['UPDATE_TIME']    = time();
            if( db("guardian")->where($where)->update($gua_list) ){
                rjson('监护人信息修改成功');
            } else {
                rjson('', '400', showRegError(-16));
            }
        } else {
            $gua_list['PID']        = $data['card_id'];
            $gua_list['ADD_TIME']   = time();
            
            if( db("guardian")->insert($gua_list) ){
                rjson('监护人信息录入成功');
            } else {
                rjson('', '400', showRegError(-16));
            }
        }
    }
    
    //上传2寸照片(申请完成)
    public function update_head_img(){
        
        $data = input('post.');
        
        $save['HEAD_IMG']= $data['head_img'];
        $save['STEP_STSTUS'] = 1 ;
        $save['EXAM_STATUS'] = 1 ;
        $save['IS_DOWN'] = 1 ;
        
        $where = [ 
            "id" => $data['card_id'],
        ];
        
        Db::startTrans();
        try{
            if( db('card')->where($where)->update($save) ){
                
                /*社保卡进度更新*/
                $add_status = card_status($data['card_id'], '1', '您的社保卡申请资料已经提交,请等待审核');
                if( db("CardStatus")->insert($add_status) ){
                    
                    /*用户推送消息*/
                    $find_user = db("user")->where([ "phone"=>$data['token_phone'] ])->find();
                    $add_msg = user_msg($find_user['ID'],'社保卡办理','您的社保卡申请资料已经提交,请等待审核',1);
                    if( db("msg")->insert($add_msg) ){
                        
                        /*极光推送*/
//                         pushMessages($find_user['JPUSH_ID'],'您的社保卡申请资料已经提交,请等待审核');
                        Db::commit(); 
                        rjson('您的社保卡申请资料已经全部提交完成,请等待审核');
                    } else {
                        exception(showRegError(-16).'[3]');
                    }
                } else {
                    exception(showRegError(-16).'[2]');
                }
            } else {
                exception(showRegError(-16),'[1]');
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    //获取已提交社保列表
    public function card_list(){
        $where = [
            'U_ID'  => $this->_loginInfo['U_ID'],
        ];
        $list = db('card')->where($where)->select();
        rjson($list);
    }
    
    //获取社保详情
    public function card_detail(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'PREPAY_ID' => input('post.prepay_id'),
        ];
        $info = db('cardOrderBak')->where($where)->find();
        rjson($info);
    }
    
    //获取监护人详情
    public function guardian_detail(){
        $where = [
            'PID'   => input('post.card_id'),
        ];
        $info = db("guardian")->where($where)->find();
        rjson($info);
    }
    
    //保存社保详情
    public function card_detail_edit(){
        
        $where = [
            'U_ID'          => $this->_loginInfo['U_ID'],
            'PREPAY_ID'     => input('post.prepay_id'),
            'EXAM_STATUS'   => 2,
        ];
        if( db('card')->where($where)->count() ) {
            (new Card())->cardEdit();
        } else {
            rjson('', '400', '该社保状态不能修改');
        }
    }

    //社保邮寄
    public function card_mail(){
        
        $data = input('post.');
        $where = [
            'ID'            => $data['card_id'],
            'EXAM_STATUS'   => 3,
        ];
        if( db("Card")->where($where)->count() ){
            (new Card())->addMailOrder($this->_loginInfo['U_ID']);
        } else {
            rjson('', '400', '社保号有问题');
        }
    }
}