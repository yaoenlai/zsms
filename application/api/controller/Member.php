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
            'DELETED'   => '0'
            ,'U_ID'     => $this->_loginInfo['U_ID']
            ,'STATE'    => 1
        ];
        
        if(!empty($this->_postData['status'])) $where['STATUS'] = $this->_postData['status'];       
            
        $list['data'] = db('Order')->where($where)->limit($this->page_size)->page($this->page_index)->order('CREATE_TIME DESC')->select();
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
}