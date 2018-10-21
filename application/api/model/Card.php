<?php
/** 
 * 社保
 *  */
namespace app\api\model;

use think\Model;
use think\Db;

class Card extends Model
{
    private $_postData=[];  //post数据
    
    public function initialize(){
        $this->_postData = input("post.");
        
        parent::initialize();
    }
    
    //社保订单
    public function addOrder($user_info){
        Db::startTrans();
        try {
            /*订单信息开始*/
            $number = date('YmdHis').rand(1000000, 9999990).$this->_postData['token_phone'];
            $numbers = date('YmdHis').rand(1000000, 9999990);
            $insert['U_ID']         = $user_info['ID'];
            $insert['PREPAY_ID']    = $number;
            $insert['NUMBERS']      = $numbers;
            //社保卡拍照费用 1
            $insert['TYPE']         = $this->_postData['type'];
            $insert['CREATE_TIME']  = time();
            $insert['STATUS']       = 2;
            $insert['STATE']        = 1;
            if( db('order')->insert($insert) ){
                /*即将录入的个人信息的存储*/
                $insert_bak['U_ID']             = $user_info['ID'];
                $insert_bak['PREPAY_ID']        = $number;
                $insert_bak['U_NAME']           = $user_info['USERNAME'];
                $insert_bak['U_CARDS']          = $user_info['CODE'];
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
                $insert_bak['AREA']             = $this->_postData['area'];
                $insert_bak['NOW_ADDRESS']      = $this->_postData['now_address'];
                $insert_bak['NOW_AREA']         = $this->_postData['now_area'];
                $insert_bak['ENTRANCE']         = $this->_postData['entrance'];
                $insert_bak['TYPE']             = $this->_postData['peo_type'];
                $insert_bak['FRONT_IMG']        = $this->_postData['front_img'];
                $insert_bak['OPPOSITE_IMG']     = $this->_postData['opposite_img'];
                $insert_bak['INSURANCE']        = $this->_postData['insurance'];
                if( ($this->_postData['peo_type'] == '2') || ($this->_postData['peo_type'] == '3')){
                    $insert_bak['RESIDENCE_IMG']    = $this->_postData['residence_img'];
                }
                if( db('CardOrderBak')->insert($insert_bak) ){
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
    
    public function addCard($find_card_info){
        Db::startTrans();
        try {
            $insert['U_ID']     = $find_card_info['U_ID'];
            $insert['U_NAME']   = $find_card_info['U_NAME'];
            $insert['U_CARDS']  = $find_card_info['U_CARDS'];
            $insert['C_NAME']   = $find_card_info['C_NAME'];
            $insert['C_CODE']   = $find_card_info['C_CODE'];
            $insert['PREPAY_ID']= $this->_postData['prepay_id'];
            
            $insert['C_ADD_TIME']   = time();
            $insert['IS_PAY']       = 1;
            $insert['PAY_TIME']     = time();
            $insert['STEP_STSTUS']  = 2;
            $insert['LR_TYPE']      = 1;
            
            if( (db("card")->where([ "C_CODE"=>$find_card_info['C_CODE'],'REFUSE_STATUS' => '1' ])->count() == 0) && db("card")->insert($insert) ){
                
                $do_add = db('card')->where($insert)->value("ID");
                if(!empty($do_add)){
                    $save['STATUS']         = 1;
                    $save['PID']            = $do_add;
                    $save['FINISH_TIME']    = time();
                    $save['PRICE']          = $this->_postData['price'];
                    $save['PAYMENT']        = $this->_postData['payment'];
                    $do_save = db("order")->where(["prepay_id"=>$this->_postData['prepay_id'] ])->update($save);
                    $back['card_id'] = $do_add;
                    
                    Db::commit();
                    rjson(array('card_id'=>$do_add), '200', '支付成功');
                } else {
                    exception('card_id获取失败');
                }
            } else {
                exception(showRegError(-16).'，请检查是否已录入');
            }     
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    public function cardEdit(){
        Db::startTrans();
        try {
            /* 修改个人详情信息 */
            $where = [
                'PREPAY_ID' => $this->_postData['prepay_id'],
            ];
            
            $insert_bak = [];
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
            $insert_bak['AREA']             = $this->_postData['area'];
            $insert_bak['NOW_ADDRESS']      = $this->_postData['now_address'];
            $insert_bak['NOW_AREA']         = $this->_postData['now_area'];
            $insert_bak['ENTRANCE']         = $this->_postData['entrance'];
            $insert_bak['FRONT_IMG']        = $this->_postData['front_img'];
            $insert_bak['OPPOSITE_IMG']     = $this->_postData['opposite_img'];
//             $insert_bak['INSURANCE']        = $this->_postData['insurance'];
            if( ($this->_postData['peo_type'] == '2') || ($this->_postData['peo_type'] == '3')){
                $insert_bak['RESIDENCE_IMG']    = $this->_postData['residence_img'];
            } 
            if( db('CardOrderBak')->where($where)->update($insert_bak) ){
                /* 修改card信息 */
                $insert = [];
                $insert['C_NAME']           = $this->_postData['c_name'];
                $insert['C_CODE']           = $this->_postData['c_code'];
                $insert['PREPAY_ID']        = $this->_postData['prepay_id'];
                if(!empty($this->_postData['head_img']))
                {
                    $insert['HEAD_IMG']         = $this->_postData['head_img'];
                }
                $insert['C_UPDATE_TIME']    = time();
                $insert['EXAM_STATUS']      = 1;
                if( db('card')->where($where)->update($insert) ){
                    Db::commit();
                    rjson('修改成功');
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
    
    //社保邮寄订单
    public function addMailOrder($u_id){
        Db::startTrans();
        try {
            /*订单信息开始*/
            $number = date('YmdHis').rand(1000000, 9999990).$this->_postData['token_phone'];
            $numbers = date('YmdHis').rand(1000000, 9999990);
            $insert['U_ID']         = $u_id;
            $insert['PREPAY_ID']    = $number;
            $insert['NUMBERS']      = $numbers;
            //社保卡邮寄费用 2
            $insert['TYPE']         = $this->_postData['type'];
            $insert['CREATE_TIME']  = time();
            $insert['STATUS']       = 2;
            $insert['STATE']        = 1;
            if( db('order')->insert($insert) ){
                $insert_mail = [
                    'ADDRESS_ID'    => $this->_postData['address_id'],
                    'EXPRESS_ID'    => $this->_postData['express_id'],
                    'PREPAY_ID'     => $number,
                    'CARD_ID'       => $this->_postData['card_id'],
                    'ADDTIME'       => date("Y-m-d H:i:s", time()),
                ];
                if ( db('CardMail')->insert($insert_mail) ){
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

    public function addCardMailPay($card_mail_id, $card_id){
        Db::startTrans();
        try {
            $save = [
                'IS_PAY'       => 1,
                'PAY_TIME'     => time(),
                'STEP_STSTUS'  => 2,
                'LR_TYPE'      => 1,
            ];
            if( db('CardMail')->where([ 'ID'=>$card_mail_id ])->update($save) ){
                $where = [
                    'PREPAY_ID' => $this->_postData['prepay_id'],
                ];
                $order_save = [
                    'STATUS'         => 1,
                    'PID'            => $card_mail_id,
                    'FINISH_TIME'    => time(),
                    'PRICE'          => $this->_postData['price'],
                    'PAYMENT'        => $this->_postData['payment'],
                ];
                if( 
                    db("order")->where(["prepay_id"=>$this->_postData['prepay_id'] ])->update($order_save) 
                    && db('Card')->where(['ID'=>$card_id])->update(['EXAM_STATUS'=>'4'])
                    ){
                    Db::commit();
                    rjson(array('card_mail_id'=>$card_mail_id), '200', '支付成功');
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
}