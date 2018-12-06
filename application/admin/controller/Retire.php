<?php
namespace app\admin\controller;

class Retire extends Common 
{
    public function list(){
        
        $where = [];
        if(!empty(input('post.code'))){
            $where['CODE']  = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.live_status'))){
            if(input('post.live_status') == '00'){
                $where['LIVE_STATUS']  = array("EQ", 0);
            } else {
                $where['LIVE_STATUS']  = array("EQ", input('post.live_status'));
            }
        }
        if(!empty(input('post.xz_code'))){
            $where['INSURANCE']  = array("EQ", input('post.xz_code'));
        }
        if(!empty(input('post.period'))){
            $where['PERIOD']  = array("EQ", input('post.period'));
        }
        if(!empty(input('post.type'))){
            $where['TYPE']  = array("EQ", input('post.type'));
        }
        if(!empty(input('post.zone_code'))){
            $where['AREA']  = array("EQ", input('post.zone_code'));
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['CREATE_TIME'] = array(array('EGT', input('post.date_value_0')), array('ELT',input('post.date_value_1')) );
        } elseif ( !empty(input('post.date_value_0')) ){
            $where['CREATE_TIME'] = array('EGT', input('post.date_value_0'));
        } elseif ( !empty(input('post.date_value_1')) ){
            $where['CREATE_TIME'] = array('ELT', input('post.date_value_1'));
        }
        
        $this->_where = $where;
        $this->_order = 'CREATE_TIME DESC';
        $this->_model = "RetireList";
        
        parent::list();
    }
    
    public function authentication(){
        $where = [
            'ID'    => input('post.ID')
        ];
        $save_data = [
            'LIVE_STATUS'   => 1
        ];
        if( db("Retire")->where($where)->update($save_data) ){
            msg_add('退休认证', '退休认证手动认证通过', input('post.U_ID'));
            rjson('手动认证通过');
        } else {
            rjson_error('未知错误');
        }
    }
    
    //获取详情
    public function getDetail(){
        $data = [];
        //获取基本信息
        $data['info'] = db('RetireInfo')->where(['CODE'=>input('post.code')])->find();
        //获取详细信息
        $data['detail'] = db('Retire')->where(['ID'=>input('post.id')])->find();
        //获取活体对比记录
        $data['face'] = db("RetireFace")->where(['PID'=>input('post.id'),'CYC'=>$data['detail']['CYC']])->order("CREATE_TIME DESC")->select();      
        
        rjson($data);
    }
    
    //获取退休险种
    public function getInsuranceList(){
        
        $where = [
            'STATUS'    => '1'
        ];
        $list = db("RetireInsurance")->where($where)->select();
        rjson($list);
    }
    
    //获取参保区域
    public function getZoneList(){
        $where = [];
        $list = db("Zone")->where($where)->select();
        rjson($list);
    }
    
    //获取民族
    public function getMz(){
        $where = [];
        $list = db("Mz")->where($where)->select();
        rjson($list);
    }
}