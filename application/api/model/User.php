<?php
/*
 * 会员
 *  */
namespace app\api\model;

use think\Model;

class User extends Model
{
    public function addUser(array $data=[]){
        
        $insert_user['PHONE'] = $data['user_phone'] ;
        $insert_user['PASSWORD'] = md5($data['password']);
        $insert_user['REG_TIME'] = time() ;
        $insert_user['REG_TYPE'] = $data['reg_type'] ;
        $insert_user['REG_IP'] = getIp() ;
        $insert_user['TYPE'] = 1 ;
        $insert_user['JPUSH_ID'] = $data['jpush_id'] ;
        $insert_user['IS_REAL'] = 2 ;
        $insert_user['INTEGRAL'] = 0 ;
        return $this->insert($insert_user);
    }
    
    //密码登录
    public function login1($data){
            
        $where = array(
            'phone'     => $data['user_phone'],
            'password'  => md5($data['password']),
        );
        $find2 = $this->where($where)->find();
        if(!empty($find2)){
            $find = $find2->toArray();
            if($find['IS_LOCK'] == '1'){
                rjson($this->_addToken($find["ID"], $find["PHONE"]));
            } else {
                rjson('', '400', showRegError(-11)); 
            }
        } else {
            rjson('', '400', showRegError(-9));
        }
    }
    
    //验证码登录
    public function login2($data){
        
        $where = array(
            'phone' => $data['user_phone'],
        );
        $find = $this->where($where)->find()->toArray();
        if(!empty($find)){
            if($find['IS_LOCK'] == '1'){
                db('verifiy')->where(array('phone'=>$data['user_phone'],'verifiy' => $data['verifiy'],'type'=>2))->delete();
                rjson($this->_addToken($find["ID"], $find["PHONE"]));
            } else {
                rjson('', '400', showRegError(-11));
            }
        } else {
            rjson('', '400', showRegError(-9));
        }
    }

    //验证码修改密码
    public function editPwd2($data){
        $where = [
            'phone' => $data['user_phone'],
        ];
        $save_data = [
            'PASSWORD'  => $data['new_pwd'],
        ];
        if( $this->where($where)->update($save_data) ){
            db('verifiy')->where(array('phone'=>$data['user_phone'],'verifiy' => $data['verifiy'],'type'=>3))->delete();
            rjson('修改成功');
        } else {
            rjson('', '400', '修改失败');
        }
    }
    
    //存储登录token
    private function _addToken($uid, $phone){
        $find_token = db("app_token")->where(['u_id'=>$uid,"phone "=>$phone])->find();
        $time = time();
        if(!empty($find_token)){
            $save['LOGINTIME'] = $time;
            $save['TOKEN'] = md5($phone.$time);
            db('app_token')->where(['u_id'=>$uid])->update($save);
        }else{
            $token['U_ID'] = $uid;
            $token['PHONE'] = $phone;
            $token['LOGINTIME'] = $time;
            $token['TOKEN'] = md5($phone.$time);
            db('app_token')->insert($token);
        }
        $find_new_token = db("app_token")->where(["u_id"=>$uid,"phone"=>$phone])->find();
        return $find_new_token;
    }
}