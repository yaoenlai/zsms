<?php
namespace app\admin\controller;

class Card extends Common
{
    
    public function list(){
        $where = [
            'REFUSE_STATUS' => '1'
        ];
        
        if(!empty(input('post.code'))){
            $where['C_CODE|U_CARDS'] = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.name'))){
            $where['C_NAME|U_NAME'] = array("LIKE", '%'.input('post.name').'%');
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
        if(!empty(input('post.is_import'))){
            if(input('post.is_import') == '00'){
                $where['IS_IMPORT'] = array("EQ", '0');
            } else {
                $where['IS_IMPORT'] = array("EQ", input('post.is_import'));
            }
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['C_ADD_TIME'] = array('BETWEEN',array(input('post.date_value_0'),input('post.date_value_1')));
        } elseif ( !empty(input('post.date_value_0')) ){
            $where['C_ADD_TIME'] = array('EGT', input('post.date_value_0'));
        } elseif ( !empty(input('post.date_value_1')) ){
            $where['C_ADD_TIME'] = array('ELT', input('post.date_value_1'));
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
        $u_id = db('Card')->where( ['ID'=>$data['ID']] )->value('U_ID');
        $save = [
            'EXAM_STATUS'=>$data['exam_status'],
        ];
        if($data['exam_status'] == '2'){
            $save['EXAM_INFO'] = $data['exam_info'];
            if(!empty($data['refuse_status'])){
                $save['REFUSE_STATUS'] = 2;
            }
            msg_add('社保卡办理', '社保卡办理审核不通过['.$data['exam_info'].']', $u_id);
        }
        if($data['exam_status'] == '3'){

            $info = db('Card2')->where(['ID'=>$data["ID"]])->find();
            //实例化, 参数传入模板文件地址
            $templateProcessor = new TemplateDocx('static/demo.docx');
            //替换(设置)变量值，我在测试的时候替换的字符比较长，这里缩短了
            $templateProcessor->setValue('ID', $info['ID']);
            $templateProcessor->setValue('C_CODE', $info['C_CODE']);
            $templateProcessor->setValue('C_NAME', $info['C_NAME']);
            $templateProcessor->setValue('C_SEX', $info['C_SEX']);
            $templateProcessor->setValue('C_NATION', $info['C_NATION']);
            $templateProcessor->setValue('C_BIRTHDAY', $info['C_BIRTHDAY']);
            $templateProcessor->setValue('C_PHONE', $info['C_PHONE']);
            $templateProcessor->setValue('C_END_TIME', $info['C_END_TIME']);
            $templateProcessor->setValue('C_ADDRESS', $info['C_ADDRESS']);
            $templateProcessor->setValue('GUARDIAN_NAME', $info['GUARDIAN_NAME']);
            $templateProcessor->setValue('GUARDIAN_CARD', $info['GUARDIAN_CARD']);
            //大于16岁的才有身份证
            if($info['TYPE'] == '1'){
                $templateProcessor->setImg('FRONT_IMG', array(
                    'src'=>config('file_path.card_path').str_replace('/card_img?path=', '', $info['FRONT_IMG']),
                    'size' => array( 150, 150 ) //图片大小，单位px
                ));
                $templateProcessor->setImg('OPPOSITE_IMG', array(
                    'src'=>config('file_path.card_path').str_replace('/card_img?path=', '', $info['OPPOSITE_IMG']),
                    'size' => array( 150, 150 ) //图片大小，单位px
                ));
            }
            //保存文件
            $path = config('file_path.doc_path').'/'.date("Ymd").'/'.$info['C_CODE'].'_'.time().'.docx';
            if(mkdirs($path)){
                $templateProcessor->saveAs($path);
            }
            
            msg_add('社保卡办理', '社保卡办理审核通过', $u_id);
        }
        if($data['exam_status'] == '6'){
            $email_save = [
                'EXPRESS_NUM'   => $data['express_num']
            ];
            if(! db("CardMail")->where(['CARD_ID'=>$data["ID"]])->update($email_save) ){
                rjson("", "400", "订单号填写失败");
            }
            msg_add('社保卡邮寄', '社保卡邮寄单号['.$data['express_num'].']', $u_id);
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
    
    //未填写监护人，拒绝审核
    public function refuse_examine(){
        $where = [
            'ID'    => input('post.card_id'),
        ];
        $save = [
            'STEP_STSTUS'   => '2'
            ,'EXAM_INFO'    => input('post.exam_info')
        ];
        if(db("Card")->where($where)->update($save)){
            rjson('修改成功');
        } else {
            rjson_error('修改状态失败');
        }
    }
    
    //获取社保申请详情
    public function detail(){
        
        if( empty(input("post.card_id")) ){
            rjson_error('参数传输有空');
        }
        
        $info = [];
        
        $where = [
            'ID' => input("post.card_id")
        ];
        $info['card_info'] = db('Card')->where($where)->find();
        //详细信息
        if(empty(input("post.prepay_id"))){
            $where = [
                'C_CODE'   => $info['card_info']['C_CODE']
            ];
        } else {
            $where = [
                'PREPAY_ID' => input("post.prepay_id")
            ];
        }
        $info['card_detail'] = db("CardOrderBak")->where($where)->find();
        if(empty($info['card_detail'])){
            rjson_error('详情信息不存在');
        }

        if($info['card_detail']['TYPE'] != '1'){
            //监督人信息
            $info['guardian_detail'] = db('guardian')->where(['PID' => input("post.card_id")])->find();
            if(empty($info['guardian_detail'])){
                rjson_error('监护人信息不存在');
            }
            if($info['guardian_detail']['GUARDIAN_SEX'] == '1'){
                $info['guardian_detail']['GUARDIAN_SEX_NAME'] = '男';
            } elseif ($info['guardian_detail']['GUARDIAN_SEX'] == '2'){
                $info['guardian_detail']['GUARDIAN_SEX_NAME'] = '女';
            }
            
            if(empty($info['card_detail']['RESIDENCE_IMG'])){
                $info['card_detail']['RESIDENCE_IMG'] = ",";
            }
        }
        rjson($info);
    }   
    
    //修改
    public function edit(){
        $data = [
            'AREA'          => input('post.AREA')
            ,'INSURANCE'    => input('post.INSURANCE')
            ,'C_SEX'        => input('post.C_SEX')
            ,'C_NATION'     => input('post.C_NATION')
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
    
    //获取民族
    public function getMz(){
        $where = [];
        $list = db("Mz")->where($where)->select();
        rjson($list);
    }
    //获取网点
    public function getDot(){
        $where = [];
        $list = db("Dot")->where($where)->select();
        rjson($list);
    }
    
    //获取险种
    public function getXz(){
        $where = [
            'STATUS'    => '2'
        ];
        $list = db("RetireInsurance")->where($where)->select();
        rjson($list);
    }
}