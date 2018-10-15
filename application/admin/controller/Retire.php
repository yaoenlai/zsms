<?php
namespace app\admin\controller;

class Retire extends Common 
{
    public function list(){
        $this->_order = 'CREATE_DATE DESC';
        $where = [];
        if(!empty(input('post.code'))){
            $where['CODE']  = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.is_pay'))){
            $where['IS_PAY']  = array("EQ", input('post.is_pay'));
        }
        if(!empty(input('post.xz_code'))){
            $where['INSURANCE']  = array("EQ", input('post.xz_code'));
        }
        if(!empty(input('post.zone_code'))){
            $where['AREA']  = array("EQ", input('post.zone_code'));
        }
        $this->_where = $where;
        
        parent::list();
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
    
    //获取详情
    public function getDetail(){
        $data = [];
        //获取基本信息
        $data['info'] = db('RetireInfo')->alias("ri")
                        ->field("ri.*,z.ZONE_NAME")
                        ->join('sb_zone z', 'z.ZONE_CODE = ri.ZONE_CODE', 'LEFT')
                        ->where(['CODE'=>input('post.code')])->find();
        //获取活体对比记录
        $data['face'] = db("RetireFace")->where(['PID'=>input('post.id')])->select();
        //获取详细信息
        $data['detail'] = db('Retire')->where(['ID'=>input('post.id')])->find();
        rjson($data);
    }
}