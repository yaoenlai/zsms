<?php
namespace app\admin\controller;

class Retire extends Common
{
    public function list(){
        $this->_order = 'C_ADD_TIME DESC';
        parent::list();
    }
    
    //修改状态
    public function status_edit(){
        $data = input('post.');
        
        if( db("Retire")->where(['ID'=>$data['ID'],'IS_PAY'=>'2'])->count() ){
            rjson_error('用户还未支付费用');
        }
        
        $save = [
            'EXAM_STATUS'=>$data['exam_status'],
        ];
        if($data['exam_status'] == '2'){
            $save['EXAM_INFO'] = $data['exam_info'];
        }
        
        if( db("Retire")->where(['ID'=>$data['ID']])->update($save) ){
            rjson('修改成功');
        } else {
            rjson_error('修改状态失败');
        }
    }
}