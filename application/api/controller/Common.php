<?php
/**  
 * 公共类
 *  */
namespace app\api\controller;

use think\Controller;

class Common extends Controller
{
    protected $_loginInfo=[];   //登录信息
    protected $_userInfo=[];    //登录用户信息
    protected $_postData=[];    //post请求信息
    /* 分页 */
    protected $page_index='';  //第几页
    protected $page_size='';  //每页数量
    
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
                    $this->_userInfo = db("User")->where(['ID'=>$this->_loginInfo['U_ID']])->find();
                    $this->_postData = input('post.');
                    
                    $this->page_index = empty(input('post.page_index')) ? "1" : input("post.page_index");
                    $this->page_size = empty(input('post.page_size')) ? "20" : input("post.page_size");
                }
            }
        }
    }
}