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
            $number = date('YmdHis').rand(1000000, 9999990).$this->_postData['token_phone'];
            $numbers = date('YmdHis').rand(1000000, 9999990);
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
                    ,'CYC'                  => date("Ym")
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
    
    public function validatePolicy(){
        //获取用户基本信息
        $user_where = [
            'CODE'  => $this->_postData['code']
            ,'NAME' => $this->_postData["name"]
        ];
        $user_info = db('RetireInfo')->where($user_where)->find();
        if(empty($user_info['XZ_CODE'])){
            rjson('', '400', '退休用户 周期编码未设置');
        }
        //获取周期
        $policy_where = [
            'INSURANCE'     => $user_info['XZ_CODE']
            ,"PERIOD_BEGIN" => array('ELT', date('m'))
            ,'PERIOD_END'   => array('EGT', date('m'))
        ];
        $policy_info = db('Policy')->where($policy_where)->find();
        if(empty($policy_info)){
            rjson('', '400', '退休用户 周期未设置');
        }
        if($policy_info['PERIOD_BEGINYEAR'] == 0){
            $begin_date = date("Y", strtotime("-1 year")).$policy_info['PERIOD_BEGIN'];
        }
        if($policy_info['PERIOD_BEGINYEAR'] == 1){
            $begin_date = date("Y").$policy_info['PERIOD_BEGIN'];
        }
        if($policy_info['PERIOD_BEGINYEAR'] == 2){
            $begin_date = date("Y", strtotime("+1 year")).$policy_info['PERIOD_BEGIN'];
        }
        
        if($policy_info['PERIOD_ENDYEAR'] == 0){
            $end_date = date("Y", strtotime("-1 year")).$policy_info['PERIOD_END'];
        }
        if($policy_info['PERIOD_ENDYEAR'] == 1){
            $end_date = date("Y").$policy_info['PERIOD_END'];
        }
        if($policy_info['PERIOD_ENDYEAR'] == 2){
            $end_date = date("Y", strtotime("+1 year")).$policy_info['PERIOD_END'];
        }
        //判断是否已认证
        $retire_where = [
            'CODE'  => $this->_postData['code']
            ,'NAME' => $this->_postData["name"]
            ,'CYC'  => array('BETWEEN', array($begin_date,$end_date))
        ];
        $retire_info = db("Retire")->where($retire_where)->find();
        if(empty($retire_info)){
            return false;
        } else if($retire_info['FACE_STATUS'] == '1'){
            switch ($policy_info['PERIOD'])
            {
                case '10': $next_date = date('Y-m-d H:i:s',strtotime("+12 month"));break;
                case '20': $next_date = date('Y-m-d H:i:s',strtotime("+6 month"));break;
                case '30': $next_date = date('Y-m-d H:i:s',strtotime("+3 month"));break;
                case '40': $next_date = date('Y-m-d H:i:s',strtotime("+1 month"));break;
            }
            //下次认证开始时间
            switch ($policy_info['PERIOD_BEGINYEAR_NEXT']){
                case '0' : $create_date = date("Y-", strtotime("-1 year")).$policy_info['PERIOD_BEGIN_NEXT'];break;
                case '1' : $create_date = date("Y-").$policy_info['PERIOD_BEGIN_NEXT'];break;
                case '2' : $create_date = date("Y-", strtotime("+1 year")).$policy_info['PERIOD_BEGIN_NEXT'];break;
            }
            //下次认证结束时间
            switch ($policy_info['PERIOD_ENDYEAR_NEXT']){
                case '0' : $end_date = date("Y-", strtotime("-1 year")).$policy_info['PERIOD_END_NEXT'];break;
                case '1' : $end_date = date("Y-").$policy_info['PERIOD_END_NEXT'];break;
                case '2' : $end_date = date("Y-", strtotime("+1 year")).$policy_info['PERIOD_END_NEXT'];break;
            }
            rjson('', '400', "已经成功认证,下次认证时间为:{$create_date}-{$end_date}");
        } else {
            return $retire_info;
        }
    }
}