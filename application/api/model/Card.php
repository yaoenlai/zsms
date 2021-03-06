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
    
    //社保订单验证
    public function card_validate($u_id){
        //验证社保号是否已提交
        $where = [
            'C_CODE'    => input('post.idcard')
            ,'IS_LOCK'  => '1'
        ];
        $card_detail = db("CardOrderBak")->where($where)->find();
        
        if(empty($card_detail)){
            //验证是否导入
            if( db("Card")->where($where)->count() > 0){
                return ['status'=> 'detail'];
            }      
            
            //验证社保办理数量
            $where = [
                'U_ID'    => $u_id
                ,'IS_LOCK'  => '1'
            ];
            if( db('Card')->where($where)->count() >= 3 ){
//                 return ['status'=> '5'];
                return ['status'=>'1'];
            } else {
                return ['status'=>'1'];
            }      
        }
        //验证社保号是否已支付
        $where = [
            'PREPAY_ID' => $card_detail['PREPAY_ID']
            ,'TYPE'     => '1'
        ];
        $order_info = db('Order')->where($where)->find();
        if($order_info['STATUS'] == '2'){
            return ['status'=>'2', 'prepay_id'=>$card_detail['PREPAY_ID']];
        }
        //验证社保号是否已拍照
        $where = [
            'ID'            => $order_info['PID']
        ];
        $card_info = db('Card')->where($where)->find();
        if(!empty($card_info) && empty($card_info['HEAD_IMG'])){
            return ['status'=>'3', 'card_id'=>$card_info['ID']];
        }
        //验证社保号是否图片审核不通过
        if( (!empty($card_info)) && ($card_info['REFUSE_STATUS'] == '2') ){
            return ['status'=>'4', 'card_id'=>$card_info['ID']];
        }
        
        return ['status'=>'detail'];
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
            $insert['TYPE']         = 1;
            $insert['CREATE_TIME']  = time();
            $insert['CREATE_DATE']  = date("Y-m-d H:i:s");
            $insert['STATUS']       = 2;
            $insert['STATE']        = 1;
            if( db('order')->insert($insert) ){
                /* 判断是否有待处理的社保卡信息 */
                $where = [
                    'C_CODE'        => $this->_postData['c_code']
                    ,'IS_LOCK'      => '1'
                    ,'EXAM_STATUS'  => '0'
                ];
                if(db("Card")->where($where)->count() > 0){
                    exception("请求超时，请返回列表");
                }
                /* 删除遗留社保详情表  */
                $where = [
                    'C_CODE'    => $this->_postData['c_code']
                    ,'IS_LOCK'  => '1'
                ];
                db("CardOrderBak")->where($where)->update(['IS_LOCK'=>'0']);
                db("Card")->where($where)->update(['IS_LOCK'=>'0']);
                
                $insert_info = [
                    'U_ID'          => $user_info['ID']
                    ,'PREPAY_ID'    => $number
                    ,'U_NAME'       => $user_info['USERNAME']
                    ,'U_CARDS'      => $user_info['CODE']
                    ,'C_NAME'       => $this->_postData['c_name']
                    ,'C_CODE'       => $this->_postData['c_code']
                ];
                
                /*即将录入的个人信息的存储*/
                $insert_bak                     = $insert_info;
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
                    if(empty($this->_postData['residence_img'])){
                        exception('小于16岁，未拍摄户口本');
                    } else {
                        $insert_bak['RESIDENCE_IMG'] = $this->_postData['residence_img'];
                    }
                }
                if( db('CardOrderBak')->insert($insert_bak) ){
                    
                    /* 即将录入的社保基础信息的存储 */
                    $insert_card                = $insert_info;
                    /* 社保卡录入是否邮寄的信息 */
                    if(!empty(input('post.card_id'))){
                        $insert_card['IS_EXPRESS'] = db('Card')->where(['ID'=>input('post.card_id')])->value("IS_EXPRESS");
                    }
                    $insert_card['C_ADD_TIME']  = time();
                    $insert_card['C_ADD_DATE']  = date("Y-m-d H:i:s", time());
                    if( db('Card')->insert($insert_card) ){
                        /* 重新生成订单时 */
                        $card_id = db('Card')->where($where)->value("ID");
                        if(!empty($card_id) && !empty(input('post.card_id'))){
                            
                            /* 修改已添加的监护人信息 */
                            db('guardian')->where(['PID'=>input('post.card_id')])->update(['PID'=>$card_id]);
                            /* 修改已添加邮寄的社保号 */
                            db('CardMail')->where(['CARD_ID'=>input('post.card_id'), 'TYPE'=>'1'])->update(['CARD_ID'=>$card_id,'PREPAY_ID'=>$number]);
                        }
                        Db::commit(); 
                        rjson(array("prepay_id"=>$number,"numbers"=>$numbers));
                    } else {
                        exception(showRegError(-16).'card');
                    }
                } else {
                    exception(showRegError(-16).'card_bak');
                }
            } else {
                exception(showRegError(-16).'order');
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    public function addCard(){
        Db::startTrans();
        try {
            
            /* 获取社保基础信息 */
            $where = [
                'PREPAY_ID' => $this->_postData['prepay_id']
                ,'IS_LOCK'  => '1'
            ];
            $card_info = db("Card")->where($where)->find();
            if(!empty($card_info)){
                /* 修改社保基础信息 */
                $save = [
                    'IS_PAY'    => 1
                    ,'PAY_TIME' => time()
                    ,'LR_TYPE'  => '1'
                ];
                if( db('Card')->where($where)->update($save) ){
                    
                    $save = [
                        'STATUS'        => '1'
                        ,'PID'          => $card_info["ID"]
                        ,'FINISH_TIME'  => time()
                        ,'FINISH_DATE'  => date("Y-m-d H:i:s")
                        ,'PRICE'        => get_price('1')
                        ,'PAYMENT'      => $this->_postData['payment']
                    ];

                    if( db("order")->where(["prepay_id"=>$this->_postData['prepay_id'] ])->update($save) ){
                        
                        Db::commit();
                        rjson(array('card_id'=>$card_info["ID"]), '200', '支付成功');
                    } else {
                        exception('支付修改出错');
                    }
                } else {
                    exception('修改社保信息出错');
                }
            } else {
                exception('card_id获取失败');
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
                'PREPAY_ID'     => $this->_postData['prepay_id'],
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
                $insert['C_UPDATE_DATE']    = date("Y-m-d H:i:s");
                $insert['EXAM_STATUS']      = 1;
                
                /* 不是图片不通过 */
                $where['REFUSE_STATUS'] = array('NEQ', '2');
                
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
    
    //上传2寸照片(申请完成)
    public function update_head_img($u_id){
        $data = input('post.');        
        
        $save['HEAD_IMG']= $data['head_img'];
        $save['STEP_STSTUS'] = 1 ;
        $save['EXAM_STATUS'] = 1 ;
        $save['IS_DOWN'] = 1 ;
        
        $where = [
            "ID"            => $data['card_id'],
            'EXAM_STATUS'   => array('NEQ', '2'),
        ];
        
        Db::startTrans();
        try{
            if( db('card')->where($where)->update($save) ){
                
                if ( msg_add('社保卡办理', '您的社保卡申请资料已经提交,请等待审核', $u_id) ){
                    /*极光推送*/
                    //                     pushMessages($find_user['JPUSH_ID'],'您的社保卡申请资料已经提交,请等待审核');
                    Db::commit();
                    rjson('您的社保卡申请资料已经全部提交完成,请等待审核');
                } else {
                    exception(showRegError(-16).'[2]');
                }
            } else {
                exception(showRegError(-16),'1');
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson('', '400', $e->getMessage());
        }
    }
    
    //社保邮寄订单
    public function addMailOrder($u_id, $cardInfo){
        //判断社保卡邮寄单是否有生成
        $where = [
            'CARD_ID'  => $this->_postData['card_id']
        ];
        $card_mail_info = db('CardMail')->where($where)->find();
        if(!empty($card_mail_info)){
            rjson(['mail_id'=>$card_mail_info['ID'], 'prepay_id'=>$card_mail_info['PREPAY_ID']], '210', '该信息已存在，请去邮寄列表查看');
        }
        
        if( empty($this->_postData['address_id']) || empty($this->_postData['express_id']) ){
            rjson('', '400', '传入信息不全,请检查是否未选择收货地址或选择邮寄方式');
        }
        
        //生成社保卡邮寄订单
        Db::startTrans();
        try {
            /*订单信息开始*/
            $number = date('YmdHis').rand(1000000, 9999990).$this->_postData['token_phone'];
            $numbers = date('YmdHis').rand(1000000, 9999990);
            $insert['U_ID']         = $u_id;
            $insert['PREPAY_ID']    = $number;
            $insert['NUMBERS']      = $numbers;
            //社保卡邮寄费用 2
            $insert['TYPE']         = 2;
            $insert['CREATE_TIME']  = time();
            $insert['STATUS']       = 2;
            $insert['STATE']        = 1;
            if( db('order')->insert($insert) ){
                $insert_mail = [
                    'ADDRESS_ID'    => $this->_postData['address_id'],
                    'EXPRESS_ID'    => $this->_postData['express_id'],
                    'PREPAY_ID'     => $number,
                    'CARD_ID'       => $this->_postData['card_id'],
                    'ADDTIME'       => time(),
                    'ADD_DATE'      => date("Y-m-d H:i:s", time()),
                    'C_CODE'        => $cardInfo['C_CODE'],
                    'C_NAME'        => $cardInfo['C_NAME'],
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
                    && db('Card')->where(['ID'=>$card_id])->update(['IS_EXPRESS'=>'3'])
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
    
    public function sure_mail($mail_id, $card_id){
        Db::startTrans();
        try {
            $save = [
                'STEP_STSTUS'   => '1'
            ];
            if( db('CardMail')->where(['ID'=>$mail_id])->update($save) ){
                $save = [
                    'EXAM_STATUS'   => '7'
                ];
                if( db('Card')->where(['ID'=>$card_id])->update($save) ){
                    Db::commit();
                    rjson('社保卡邮寄已完成，谢谢使用!');
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
}