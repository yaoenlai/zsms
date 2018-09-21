<?php
/** 
 * 会员 
 *  */
namespace app\api\controller;
use think\Controller;
use app\api\model\Verifiy;
use app\api\model\User;
use app\api\model\AppToken;

class Users extends Controller
{
    private $_postData=[];
    
    public function _initialize(){
        if(! request()->isPost()){
            rjson("",'400','请求方式错误');
        } else {
            $this->_postData=input("post.");
        }
    }
    
    //发送验证码
    public function send_msg(){
        
        $msg_type = $this->_postData['msg_type'];
        if(empty($this->_postData['user_phone'])) die(rjson('', '400', '手机号错误'));
        
        $verifiy = new Verifiy();
        switch ($msg_type){
            case '1':
                $verifiy->regVerifiy($this->_postData['user_phone']);
                break;
            case '2':
                $verifiy->loginVerifiy($this->_postData['user_phone']);
                break;
            case '3':
                $verifiy->editVerifiy($this->_postData['user_phone']);
                break;
            default:
                rjson('', '400', '未知获取验证码状态');
        }
    }
    
    //注册
    public function registers(){
        $verifiy = new Verifiy($this->_postData);
        if ( $verifiy->getCount(1) ){
            //验证码通过
            $User = new User();
            if($User->addUser($this->_postData)){
                //注册成功
                rjson(showRegError(-8));
            } else {
                rjson('', '400', showRegError(-16));
            }
        } else {
            rjson('', '400', showRegError(-7));
        }
    }
    
    //登录
    public function login(){
        
        $User = new User();
        $find = [];
        
        $login_type = $this->_postData['login_type'];
        switch ($login_type){
            case '1':
                if (empty( $this->_postData['user_phone'] ) || empty( $this->_postData['password'] )) 
                    die( rjson('', '400', showRegError(-17)) );
               
                $User->login1($this->_postData);
                break;
            case '2':
                $verifiy = new Verifiy($this->_postData);
                if($verifiy->getCount(2)){
                    if (empty($this->_postData['user_phone']) || empty($this->_postData['user_phone']) || empty($this->_postData['verifiy'])) 
                        die( rjson('', '400', showRegError(-17)) );
                    
                    $User->login2($this->_postData);
                } else {
                    rjson('', '400', showRegError(-7));
                }
                break;
            default: rjson('', '400', '未定义登录类型:'.$login_type);
        }
    }
    
    //忘记密码
    public function forget_pwd(){
        $verifiy = new Verifiy($this->_postData);
        if($verifiy->getCount(3)){
            (new User())->editPwd2($this->_postData);
        } else {
            rjson('', '400', showRegError(-7));
        }
    }

    //获取用户状态
    public function user_status(){
        
        if(!empty($this->_postData['token_phone']) && !empty($this->_postData['token_token'])){
            $AppToke = new AppToken();
            $find = $AppToke->getFind( ['phone'=>$this->_postData['token_phone'],'token'=>$this->_postData['token_token'] ]);
            if(empty($find)){
                rjson('', '400', showRegError(-12));
            } else {
                $find_user_status = db("user")->field("IS_LOCK as status,is_real")->where([ "phone"=>$this->_postData['token_phone'] ])->find();
                if(empty($find_user_status)){
                    rjson('', '400', '用户异常');
                } else {
                    rjson($find_user_status);
                }
            }
        } else {
            rjson('', '400', showRegError(-17));
        }
    }
    
    //用户实名认证
    public function user_real(){
        if(!empty($this->_postData['token_phone']) && !empty($this->_postData['token_token'])){
            $AppToke = new AppToken();
            $find = $AppToke->getFind( ['phone'=>$this->_postData['token_phone'],'token'=>$this->_postData['token_token'] ]);
            if(empty($find)){
                rjson('', '400', showRegError(-12));
            } else {
                if( db('user')->where([ "code"=>$this->_postData['code'] ])->count() ){
                    rjson('', '400', showRegError(-13));
                } else {
                    $save['USERNAME']   = $this->_postData['username'];
                    $save['CODE']       = $this->_postData['code'];
                    $save['IS_REAL']    = 1;
                    if( db('user')->where([ "phone"=>$this->_postData['token_phone'] ])->update($save) ){
                        rjson(showRegError(-14));
                    } else {
                        rjson('', '400', showRegError(-16));
                    }
                }
            }
        } else {
            rjson('', '400', showRegError(-17));
        }
    }

    //退出登录
    public function login_out(){
        if(db('app_token')->where(array('phone'=>$this->_postData['token_phone'],'token' => $this->_postData['token_token']))->delete()){
            rjson("成功退出登录");
        } else {
            rjson('', '400', '退出失败');
        }
    }

}