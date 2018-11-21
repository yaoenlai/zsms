<?php
namespace app\admin\controller;

use think\Db;
use think\queue\connector\Database;

class RetireInfo extends Common
{
    public function list(){
        $this->_order='ID DESC';
        $where = [];
        
        if(!empty(input('post.code'))){
            $where['CODE']  = array("LIKE", '%'.input('post.code').'%');
        }
        if(!empty(input('post.name'))){
            $where['NAME']  = array("LIKE", '%'.input('post.name').'%');
        }
        if(!empty(input('post.is_pay'))){
            if( input('post.is_pay') == '00'){
                $where['IS_PAY']  = array("EQ", 0);
            } else {
                $where['IS_PAY']  = array("EQ", input('post.is_pay'));
            }
        }
        if(!empty(input('post.xz_code'))){
            $where['XZ_CODE']  = array("EQ", input('post.xz_code'));
        }
        if(!empty(input('post.zone_code'))){
            $where['ZONE_CODE']  = array("EQ", input('post.zone_code'));
        }
        
        $this->_where = $where;
        
        parent::list();
    }
    
    public function get_source_img(){
        if(empty(input('post.code'))) rjson_error('身份证号码为空');
        
        $path = "/source_img/admin/".input('post.code').".jpg";
        if(mkdirs($path)){
            if(get_source_img(input('post.code'), config('file_path.retire_path').$path)){
                rjson("retire_img?path=".$path);
            } else {
                rjson_error('获取源照片失败');
            }
        } else {
            rjson_error('路径生成错误');
        }
    }
    
    public function save(){
        Db::startTrans();
        try {
            $where = [
                'CODE'  => input('post.CODE')
            ];
            $save_data = [
                'XZ_CODE'       => input('post.XZ_CODE')
                ,'ZONE_CODE'    => input('post.ZONE_CODE')
                ,'COMP_NAME'    => input('post.COMP_NAME')
                ,'COMP_CODE'    => input('post.COMP_CODE')
                ,'IS_PAY'       => input('post.IS_PAY')
            ];
            $insuance_status = db('PerInsuance')->where($where)->update($save_data);
            $save_data = [
                'NAME'          => input('post.NAME')
                ,'SEX'          => input('post.SEX')
                ,'NATION'       => input('post.MZ_CODE')
                ,'UPDATE_TIME'  => date("Y-m-d H:i:s")
                ,'UPDATE_ID'    => $this->admin_id
            ];
            $info_status = db('PerInfo')->where($where)->update($save_data);
            if(!empty($insuance_status) && !empty($info_status)){
                Db::commit(); 
                rjson('修改成功');
            } else {
                exception('修改失败');
            }
        } catch (\Exception $e){
            Db::rollback();
            rjson_error($e->getMessage());
        }
        
    }
}