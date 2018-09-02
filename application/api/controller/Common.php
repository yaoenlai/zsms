<?php
/** 
 * 公共类
 *  */
namespace app\api\controller;

use think\Controller;

class Common extends Controller
{
    protected $_loginInfo=[];
    protected $_postData=[];
    
    public function _initialize(){
        if(! request()->isPost()){
            
            rjson("",'400','请求方式错误');
        } else {
            //验证用户登录信息
            if(empty(input('post.token_phone')) || empty(input('post.token_token')) ){
                
                rjson('', '400', showRegError(-17));
            } else {
                
                $where = array(
                    'phone'=>input('post.token_phone'),
                    'token'=>input('post.token_token'),
                );
                $info = db('AppToken')->where($where)->find();
                if(empty($info)){
                    
                    rjson('', '400', showRegError(-12));
                } else {
                    
                    $this->_loginInfo = $info;
                    $this->_postData = input('post.');
                }
            }
        }
    }
}