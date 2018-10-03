<?php
namespace app\admin\controller;

class Card extends Common
{
    public function list(){
        $this->_order = 'C_ADD_TIME DESC';
        parent::list();
    }
    
    //修改状态
    public function status_edit(){
        $data = input('post.');
        
        $save = [
            'EXAM_STATUS'=>$data['exam_status'],
        ];
        if($data['exam_status'] == '2'){
            $save['EXAM_INFO'] = $data['exam_info'];
        }
        if($data['exam_status'] == '6'){
            $email_save = [
                'EXPRESS_NUM'   => $data['express_num']
            ];
            if(! db("CardMail")->where(['CARD_ID'=>$data["ID"]])->update($email_save) ){
                rjson("", "400", "订单号填写失败");
            }
        }
        if($data['exam_status'] == '7'){
            if(! db("CardMail")->where(['CARD_ID'=>$data["ID"]])->update(['STEP_STSTUS'=>'1']) ){
                rjson("", "400", "订单号填写失败");
            }
        }
        if( db("Card")->where(['ID'=>$data['ID']])->update($save) ){
            rjson('修改成功');
        } else {
            rjson_error('修改状态失败');
        }
    }
    
    //获取社保申请详情
    public function detail(){
        
        $info = [];
        $info['card_info'] = db('Card')->where(['ID' => input("post.card_id")])->find();
        $info['card_detail'] = db("CardOrderBak")->where(['PREPAY_ID' => input("post.prepay_id")])->find();
        if($info['card_detail']['TYPE'] != '1'){
            
        }
        $info['guardian_detail'] = db('guardian')->where(['PID' => input("post.card_id")])->find();
        rjson($info);
    }   
}