<?php
/** 
 * 会员类
 *  */ 
namespace app\api\controller;

class Member extends Common
{
    
    //获取用户信息
    public function getInfo(){
        $where = [
            'ID'        => $this->_loginInfo['U_ID']
            ,'IS_LOCK'  => '1'
        ];
        $info = db("User")->field("ID,PHONE,USERNAME,BIRTHDAY,SEX,CODE,NATION,START_TIME,END_TIME,REG_TIME,REG_IP,
            REG_TYPE,MES_TYPE,TYPE,FID,JPUSH_ID,QRIMG,IS_REAL,INTEGRAL,NUM,ADDRESS,DEPARTMENT,HEAD_IMG,USER_TYPE")->where($where)->find();
        $info['INTEGRAL'] = db('UserIntegral')->where($where)->count();
        rjson(array_change_key_case($info));
    }
    
    //编辑用户信息(实名认证)
    public function editInfo(){
        $data = $this->_postData;
        unset($data['token_phone']);
        unset($data['token_token']);
        
        $where = [
            'ID'        => $this->_loginInfo['U_ID']
            ,'IS_LOCK'  => '1'
        ];
        
        if( db("User")->where($where)->update(array_change_key_case($data, CASE_UPPER) ) ){
            rjson('修改认证成功');
        } else {
            rjson('', '400', '修改认证失败');
        }
    }
    
    //修改密码
    public function editPwd(){
        $where = [
            'ID'        => $this->_loginInfo['U_ID']
            ,'PASSWORD' => input('post.old_pwd')
            ,'IS_LOCK'  => '1'
        ];
        
        $data = [
            'PASSWORD'  => input('post.new_pwd')
        ];
        
        if( db("User")->where($where)->update($data) ){
            rjson('修改密码成功');  
        } else {
            rjson('', '400', '修改密码失败,请检查历史密码');
        }
    }
    
    //订单/账单列表
    public function orderList(){
        $where = [
            'DELETED'   => '1'
            ,'U_ID'     => $this->_loginInfo['U_ID']
            ,'STATE'    => 1
        ];
        
        if(!empty($this->_postData['status'])) $where['STATUS'] = $this->_postData['status'];
        
        $page_index = empty($this->_postData['page_index']) ? '1' : $this->_postData['page_index'];
        $page_size  = empty($this->_postData['page_size']) ? '10' : $this->_postData['page_size'];
            
        $list['data'] = db('Order')->where($where)->limit($page_size)->page($page_index)->order('CREATE_TIME DESC')->select();
        $list['total'] = db('Order')->where($where)->count();
        rjson($list);
    }
    
    //获取积分列表
    public function integralList(){
        $where = [
            'U_ID'  => $this->_loginInfo['U_ID']
            ,'IS_LOCK'  => '1'
        ];
        $list = db('UserIntegral')->where($where)->order('ADDTIME DESC')->select();
        rjson($list);
    }
    
    //获取邮寄列表
    public function mailList(){
        $data = $this->_postData;
        $where = [
            'IS_PAY'        => $data['is_pay']
            ,'STEP_STSTUS'  => $data['step_status']
            ,'U_ID'         => $this->_loginInfo['U_ID']
        ];
        
        $page_index = empty($this->_postData['page_index']) ? '1' : $this->_postData['page_index'];
        $page_size  = empty($this->_postData['page_size']) ? '10' : $this->_postData['page_size'];
        
        $list['data'] = db('Mail')->where($where)->limit($page_size)->page($page_index)->order('ADDTIME DESC')->select();
        $list['total'] = db('Mail')->where($where)->count();
        rjson($list);
    }
}