<?php
namespace app\admin\controller;

use app\admin\model\CardMakeModel;

class CardMake extends Common
{
    
    public function list()
    {     
        $where = [];
        if(!empty(input('post.bank'))){
            $where['BANK']  = array("EQ", input('post.bank'));
        }
        if(!empty(input('post.batch_num'))){
            $where['BATCH_NUM']  = array("LIKE", '%'.input('post.batch_num').'%');
        }      
        if(!empty(input('post.express_type'))){
            $where['EXPRESS_TYPE']  = array("EQ", input('post.express_type'));
        }
        if(!empty(input('post.make_status'))){
            $where['MAKE_STATUS']  = array("EQ", input('post.make_status'));
        }
        if(is_numeric(input('post.express_status'))){
            $where['EXPRESS_STATUS']  = array("EQ", input('post.express_status'));
        }
        if(!empty(input('post.zone_code'))){
            $where['ZONE_CODE']  = array("EQ", input('post.zone_code'));
        }
        
        if( !empty(input('post.import_date_star')) && !empty(input('post.import_date_end')) ){
            $where['MAKE_IMPORT_TIME'] = array(array('EGT', input('post.import_date_star')), array('ELT',input('post.import_date_end')) );
        } elseif ( !empty(input('post.import_date_star')) ){
            $where['MAKE_IMPORT_TIME'] = array('EGT', input('post.import_date_star'));
        } elseif ( !empty(input('post.import_date_end')) ){
            $where['MAKE_IMPORT_TIME'] = array('ELT', input('post.import_date_end'));
        }
        
        if( !empty(input('post.end_date_star')) && !empty(input('post.end_date_end')) ){
            $where['MAKE_END_TIME'] = array(array('EGT', input('post.end_date_star')), array('ELT',input('post.end_date_end')) );
        } elseif ( !empty(input('post.end_date_star')) ){
            $where['MAKE_END_TIME'] = array('EGT', input('post.end_date_star'));
        } elseif ( !empty(input('post.end_date_end')) ){
            $where['MAKE_END_TIME'] = array('ELT', input('post.end_date_end'));
        }
        
        if( !empty(input('post.express_add_date_star')) && !empty(input('post.express_add_date_end')) ){
            $where['EXPRESS_ADD_TIME'] = array(array('EGT', input('post.express_add_date_star')), array('ELT',input('post.express_add_date_end')) );
        } elseif ( !empty(input('post.express_add_date_star')) ){
            $where['EXPRESS_ADD_TIME'] = array('EGT', input('post.express_add_date_star'));
        } elseif ( !empty(input('post.express_add_date_end')) ){
            $where['EXPRESS_ADD_TIME'] = array('ELT', input('post.express_add_date_end'));
        }
        
        $this->_where = $where;
        $this->_order = "ADD_TIME DESC";
        parent::list();
    }
    
    //获取参保区域
    public function getZoneList(){
        $where = [];
        $list = db("Zone")->where($where)->select();
        rjson($list);
    }
    
    //获取制卡银行
    public function getBankList(){
        $where = [];
        $list = db("Bank")->where($where)->select();
        rjson($list);
    }
    
    //获取网点地址
    public function getDotList(){
        $where = [];
        $list = db("Dot")->where($where)->select();
        rjson($list);
    }
       
    //->制卡完成
    public function make_card_1(){
        $this->_saveData = [
            'MAKE_STATUS'   => 2
            ,'ADMIN_ID'     => $this->admin_id
            ,'ADMIN_NAME'   => db("Admin")->where(['ADMIN_ID'=>$this->admin_id])->value("USERNAME")
        ];
        parent::save();
    }
    //->制卡中
    public function make_card_2(){
        $this->_saveData = [
            'MAKE_STATUS'   => 1
            ,'ADMIN_ID'     => $this->admin_id
            ,'ADMIN_NAME'   => db("Admin")->where(['ADMIN_ID'=>$this->admin_id])->value("USERNAME")
        ];
        parent::save();
    }
    //邮寄->找到
    public function discover(){
        $this->_saveData = [
            'EXPRESS_STATUS'    => 3,
            'EXPRESS_ZK_DATE'   => date("Y-m-d H:i:s"),
            'EXPRESS_ZK_TIME'   => time(),
            'ADMIN_ID'          => $this->admin_id,
            'ADMIN_NAME'        => db("Admin")->where(['ADMIN_ID'=>$this->admin_id])->value("USERNAME")
        ];
        $this->_where = [
            "ID"                => input('post.id'),
            'EXPRESS_STATUS'    => 2
        ];
        parent::save();
    }
    //邮寄办理
    public function make_mail(){
        $this->_saveData                    = input('post.');
        $this->_saveData['ADMIN_ID']        = $this->admin_id;
        $this->_saveData['ADMIN_NAME']      = db("Admin")->where(['ADMIN_ID'=>$this->admin_id])->value("USERNAME");
        $this->_saveData['EXPRESS_ADD_DATE']= date("Y-m-d H:i:s");
        $this->_saveData['EXPRESS_ADD_TIME']= time();
        
        $this->_where = [
            "ID"    =>input('post.ID')
        ];
        
        parent::save();
    }
    
    public function export(){
        if(empty(input('post.id_value'))){
            rjson_error("请选择要导出的数据");
        }
        $id_arr = explode(",", input('post.id_value'));
        $where = [
            'ID'    => array("IN", $id_arr)
        ];
        //利用筛选条件查询订单数据为$list
        $list = db("CardMake")->field("C_CODE,BANK,ZONE_CODE")->where($where)->select();
        
        $indexKey = array('C_CODE','BANK','ZONE_CODE');
        //excel表头内容
        $header = array('C_CODE'=>'身份证','BANK'=>'制卡银行','ZONE_CODE'=>'参保区域');
        //将查询到的订单数据和表头内容合并,构造成数组list
        array_unshift($list,$header);
        
        $path = toExcel($list,uniqid(),$indexKey,1,true);
        if(empty($path)){
            rjson_error('导出失败');
        } else {
            rjson($path);
        }
    }
    
    public function export2(){
        if(empty(input('post.id_value'))){
            rjson_error("请选择要导出的数据");
        }
        $id_arr = explode(",", input('post.id_value'));
        $where = [
            'ID'    => array("IN", $id_arr)
        ];
        //利用筛选条件查询订单数据为$list
        $list = db("CardMake")->field('ID,C_NAME,C_PHONE,C_PROVINCE,C_CITY,C_COUNTY,TAKE_NAME,TAKE_PHONE,TAKE_ADDRESS')->where($where)->select();
        
        $indexKey = array('ID','C_NAME','C_PHONE','C_PROVINCE','C_CITY','C_COUNTY','TAKE_NAME','TAKE_PHONE','TAKE_ADDRESS');
        //excel表头内容
        $header = array('ID'=>'ID','C_NAME'=>'姓名','C_PHONE'=>'联系电话','C_PROVINCE'=>'省','C_CITY'=>'市','C_COUNTY'=>'区/县','TAKE_NAME'=>'收件人姓名','TAKE_PHONE'=>'收件人手机号','TAKE_ADDRESS'=>'收件地址');
        //将查询到的订单数据和表头内容合并,构造成数组list
        array_unshift($list,$header);
        
        $path = toExcel($list,uniqid(),$indexKey,1,true);
        if(empty($path)){
            rjson_error('导出失败');
        } else {
            rjson($path);
        }
    }
    
    public function import(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            
            $config = [
                'size'  => 30000000
                ,'ext' => array('xls', 'xlsx')
            ];
            $savePath = '/uploads/excel/';
            
            $info = $file->validate($config)->move(ROOT_PATH . 'public' . DS . $savePath);
            if($info){
                // 成功上传后 获取上传信息
                $return = array(
                    'ext'       => $info->getExtension(),
                    'path'      => $savePath.$info->getSaveName(),
                    'file_name' => $info->getFilename()
                );;
                $data = data_import('.'.$return['path'], $return['ext']);
                if( (new CardMakeModel())->upload($data) ){
                    rjson('导入更新成功');
                } else {
                    rjson_error('导入更新失败');
                }
            }else{
                // 上传失败获取错误信息
                rjson_error($file->getError());
            }
        }
    }
    
    public function import2(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            
            $config = [
                'size'  => 30000000
                ,'ext' => array('xls', 'xlsx')
            ];
            $savePath = '/uploads/excel/';
            
            $info = $file->validate($config)->move(ROOT_PATH . 'public' . DS . $savePath);
            if($info){
                // 成功上传后 获取上传信息
                $return = array(
                    'ext'       => $info->getExtension(),
                    'path'      => $savePath.$info->getSaveName(),
                    'file_name' => $info->getFilename()
                );;
                $data = data_import('.'.$return['path'], $return['ext']);
                if( (new CardMakeModel())->upload2($data) ){
                    rjson('导入更新成功');
                } else {
                    rjson_error('导入更新失败');
                }
            }else{
                // 上传失败获取错误信息
                rjson_error($file->getError());
            }
        }
    }
    
    public function import3(){
        $file = request()->file('file');
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            
            $config = [
                'size'  => 30000000
                ,'ext' => array('xls', 'xlsx')
            ];
            $savePath = '/uploads/excel/';
            
            $info = $file->validate($config)->move(ROOT_PATH . 'public' . DS . $savePath);
            if($info){
                // 成功上传后 获取上传信息
                $return = array(
                    'ext'       => $info->getExtension(),
                    'path'      => $savePath.$info->getSaveName(),
                    'file_name' => $info->getFilename()
                );;
                $data = data_import('.'.$return['path'], $return['ext']);               
                if( (new CardMakeModel())->upload3($data) ){
                    rjson('导入更新成功');
                } else {
                    rjson_error('导入更新失败');
                }
            } else {
                // 上传失败获取错误信息
                rjson_error($file->getError());
            }
        }
    }
}