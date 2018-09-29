<?php
namespace app\api\model;

use think\Model;
use think\Db;

class Retire extends Model
{
    private $_postData=[];  //post数据
    
    public function initialize(){
        $this->_postData = input("post.");
        
        parent::initialize();
    }
    
    //创建退休认证临时单
    public function add2($u_id, $u_name){
        Db::startTrans();
        try {
            /*订单信息开始*/
            $number = date('YmdHis').rand(10000000, 99999900).$this->_postData['token_phone'];
            $numbers = date('YmdHis').rand(10000000, 99999900);
            $insert['U_ID']         = $u_id;
            $insert['PREPAY_ID']    = $number;
            $insert['NUMBERS']      = $numbers;
            //退休办理费用 3
            $insert['TYPE']         = 3;
            $insert['CREATE_TIME']  = time();
            $insert['STATUS']       = 2;
            $insert['STATE']        = 1;
            if( db('order')->insert($insert) ){
                $insert_data = [
                    'U_ID'                  => $u_id
                    ,'U_NAME'               => $u_name
                    ,'PREPAY_ID'            => $number
                    ,'CODE'                 => $this->_postData['code']
                    ,'NAME'                 => $this->_postData['name']
                    ,'CYC'                  => date("Ymd")
                    ,'CREATE_DATE'          => date('Y-m-d H:i:s')
                    ,'AUTHENTICATION_STATUS'=> '3'
                ];
                if(! db("Retire")->insert($insert_data) ){
                    exception('创建退休临时数据失败');
                } else {
                    Db::commit();
                    return db("Retire")->where($insert_data)->find();
                }
            } else {
                exception(showRegError(-16));
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    //申请退休(废弃)
    public function add($u_id){
        Db::startTrans();
        try {
            /*订单信息开始*/
            $number = date('YmdHis').rand(10000000, 99999900).$this->_postData['token_phone'];
            $numbers = date('YmdHis').rand(10000000, 99999900);
            $insert['U_ID']         = $u_id;
            $insert['PREPAY_ID']    = $number;
            $insert['NUMBERS']      = $numbers;
            //退休办理费用 3
            $insert['TYPE']         = 3;
            $insert['CREATE_TIME']  = time();
            $insert['STATUS']       = 2;
            $insert['STATE']        = 1;
            if( db('order')->insert($insert) ){
                /*即将录入的个人信息的存储*/
                $insert_bak['U_ID']             = $u_id;
                $insert_bak['PREPAY_ID']        = $number;
                $insert_bak['C_NAME']           = $this->_postData['c_name'];
                $insert_bak['C_CODE']           = $this->_postData['c_code'];
                $insert_bak['C_SEX']            = $this->_postData['c_sex'];
                $insert_bak['C_BIRTHDAY']       = $this->_postData['c_birthday'];
                $insert_bak['C_NATION']         = $this->_postData['c_nation'];
                $insert_bak['C_PHONE']          = $this->_postData['c_phone'];
                $insert_bak['C_ADDRESS']        = $this->_postData['c_address'];
                $insert_bak['C_STATR_TIME']     = $this->_postData['c_statr_time'];
                $insert_bak['C_END_TIME']       = $this->_postData['c_end_time'];
                $insert_bak['C_DEPARTMENT']     = $this->_postData['c_department'];
                $insert_bak['NOW_ADDRESS']      = $this->_postData['now_address'];
                $insert_bak['ENTRANCE']         = $this->_postData['entrance'];
                $insert_bak['FRONT_IMG']        = $this->_postData['front_img'];
                $insert_bak['OPPOSITE_IMG']     = $this->_postData['opposite_img'];
                $insert_bak['C_ADD_TIME']       = date("Y-m-d H:i:s", time());
                $insert_bak['C_ADD_IP']         = getIp();
                $insert_bak['ADDTIME']          = time();
                if( db('Retire')->insert($insert_bak) ){
                    Db::commit();
                    rjson(array("prepay_id"=>$number,"numbers"=>$numbers));
                } else {
                    exception(showRegError(-16));
                }
            } else {
                exception(showRegError(-16));
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    //支付退休申请
    public function orderPay($retire_id){
        Db::startTrans();
        try {
            $save = [
                'IS_PAY'       => 1,
                'PAY_TIME'     => time(),
                'LR_TYPE'      => 1,
            ];
            if( db('Retire')->where([ 'ID'=>$retire_id ])->update($save) ){
                $order_save = [
                    'STATUS'         => 1,
                    'PID'            => $retire_id,
                    'FINISH_TIME'    => time(),
                    'PRICE'          => $this->_postData['price'],
                    'PAYMENT'        => $this->_postData['payment'],
                ];
                if( db("order")->where(["prepay_id"=>$this->_postData['prepay_id'] ])->update($order_save) ){
                    Db::commit();
                    rjson(array('retire_id'=>$retire_id), '200', '支付成功');
                } else {
                    exception('支付失败');
                }
            } else {
                exception(showRegError(-16));
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    //修改退休申请资料
    public function edit(){
        $insert_bak['C_NAME']           = $this->_postData['c_name'];
        $insert_bak['C_CODE']           = $this->_postData['c_code'];
        $insert_bak['C_SEX']            = $this->_postData['c_sex'];
        $insert_bak['C_BIRTHDAY']       = $this->_postData['c_birthday'];
        $insert_bak['C_NATION']         = $this->_postData['c_nation'];
        $insert_bak['C_PHONE']          = $this->_postData['c_phone'];
        $insert_bak['C_ADDRESS']        = $this->_postData['c_address'];
        $insert_bak['C_STATR_TIME']     = $this->_postData['c_statr_time'];
        $insert_bak['C_END_TIME']       = $this->_postData['c_end_time'];
        $insert_bak['C_DEPARTMENT']     = $this->_postData['c_department'];
        $insert_bak['NOW_ADDRESS']      = $this->_postData['now_address'];
        $insert_bak['ENTRANCE']         = $this->_postData['entrance'];
        $insert_bak['FRONT_IMG']        = $this->_postData['front_img'];
        $insert_bak['OPPOSITE_IMG']     = $this->_postData['opposite_img'];
        $insert_bak['C_ADD_TIME']       = date("Y-m-d H:i:s", time());
        $insert_bak['C_ADD_IP']         = getIp();
        $insert_bak['ADDTIME']          = time();
        
        $where = [
            'ID'    => $this->_postData['retire_id'],
        ];
        
        if( db('Retire')->where($where)->update($insert_bak) ){
            rjson('修改成功');
        } else {
            rjson('', '400', '修改失败');
        }
    }
}