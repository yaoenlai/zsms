<?php
/* 
 * 验证码
 *  */
namespace app\api\model;

use think\Model;
use Lib\SendHxrt\SendHxrt;

class Verifiy extends Model
{
    private $_data=[];
    
    public function __construct(array $data=[]){
        $this->_data = $data;
        parent::__construct();
    }
    
    //注册验证码是否有效
    public function getCount($type='1')
    {
        if(empty($this->_data['user_phone']) || empty($this->_data['verifiy'])) die(rjson('', '400', '参数不全'));
        
        if( db('user')->where([ 'phone'=>$this->_data['user_phone'] ])->count() && ($type=='1')){
            //验证手机号是否已经注册过
            rjson('', '400', showRegError(-1));
        }else {
            $time = time();
            $overtime = $time - 300;
            $where = array(
                'phone'     => $this->_data['user_phone'],
                'type'      => $type,
                'verifiy'   => $this->_data['verifiy'],
                'addtime'   => array('EGT', $overtime),
            );
            return $this->where($where)->count();
        }
    }
    
    //注册验证码
    public function regVerifiy(string $phone)
    {
        if( db('user')->where(['phone'=>$phone])->count() ){
            //验证手机号是否已经注册过
            rjson('', '400', showRegError(-1));
        }else {
            $this->_Verifiy($phone, 1);
        }
    }
    //登录验证码
    public function loginVerifiy(string $phone){
        if( db('user')->where(['phone'=>$phone])->count() ){
            $this->_Verifiy($phone, 2);
        }else {
            //验证手机号是否已经注册过
            rjson('', '400', showRegError(-6));
        }
    }
    //修改验证码
    public function editVerifiy(string $phone){
        if( db('user')->where(['phone'=>$phone])->count() ){
            $this->_Verifiy($phone, 3);
        }else {
            //验证手机号是否已经注册过
            rjson('', '400', showRegError(-6));
        }
    }
    //验证码
    private function _Verifiy(string $phone, int $msg_type){
        $time = time();
        $overtime = $time - 300;
        
        $where = array(
            'phone'     => $phone,
            'type'      => $msg_type,
            'addtime'   => array('EGT', $overtime),
        );
        if( $this->where($where)->count() ){
            //验证验证码是否已经获取过了（三分钟之内）
            rjson('', '400', showRegError(-5));
        } else {
            $rand_num = rand(100000,999999);
            $sendHxrt = new SendHxrt($phone,$rand_num);
            $back_return = $sendHxrt->sendSMS();
            dump($back_return);die;
            if( $back_return == '0'){
                //如果发送成功
                $insert['PHONE'] = $phone ;
                $insert['VERIFIY'] = $rand_num ;
                $insert['ADDTIME'] = time() ;
                $insert['TYPE'] = $msg_type;
                $insert['IPS'] = getIp() ;
                $do_insert = $this->insert($insert);
                if($do_insert){
                    rjson(showRegError(-3));
                } else {
                    rjson('', '400', showRegError(-16));
                }
            } else {
                rjson('', '400', showRegError(-4));
            }
        }
    }
}