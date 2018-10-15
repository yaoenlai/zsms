<?php
namespace app\admin\controller;

class Card extends Common
{
    public function list(){
        $where = [];
        
        if(!empty(input('post.code'))){
            $where['C_CODE'] = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.status'))){
            $where['EXAM_STATUS'] = array("EQ", input('post.status'));
        }
        if(!empty(input('post.area'))){
            $where['AREA'] = array("EQ", input('post.area'));
        }
        if(!empty(input('post.is_mail'))){
            $where['IS_EXPRESS'] = array("EQ", input('post.is_mail'));
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['C_ADD_TIME'] = array('BETWEEN',array(input('post.date_value_0'),input('post.date_value_1')));
        }
        $page_index = empty(input('post.page_index')) ? "1" : input("post.page_index");
        $page_size = empty(input('post.page_size')) ? "20" : input("post.page_size");
        
        $data = [];
        $data['list'] = db("card2")->where($where)->limit($page_size)->page($page_index)->order("C_ADD_TIME DESC")->select();
        $data['total'] = db("card2")->where($where)->count();
        rjson($data);
    }
    
    //修改状态
    public function status_edit(){
        $data = input('post.');
        $save = [
            'EXAM_STATUS'=>$data['exam_status'],
        ];
        if($data['exam_status'] == '2'){
            $save['EXAM_INFO'] = $data['exam_info'];
            if(!empty($data['refuse_status'])){
                $save['REFUSE_STATUS'] = 2;
            }
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
    
    //修改参保区域
    public function area_edit(){
        $data = [
            'AREA'  => input('post.AREA')
        ];
        $where = [
            'ID'    => input('post.ID')
        ];
        if( db("CardOrderBak")->where($where)->update($data) ){
            rjson("修改成功");
        } else {
            rjson_error('修改失败');
        }
    }
    
    //获取参保区域
    public function getZone(){
        $where = [];
        $list = db("zone")->where($where)->select();
        rjson($list);
    }
}