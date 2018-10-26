<?php
/** 
 * 会员类
 *  */
namespace app\admin\controller;

class User extends Common
{
    public function list(){
        $this->_order = 'REG_TIME desc';
        
        $where = [];
        if(!empty(input('post.phone'))){
            $where['PHONE'] = array("LIKE", '%'.input('post.phone').'%');
        }
        if(!empty(input('post.code'))){
            $where['CODE'] = array("LIKE", '%'.input('post.code').'%');
        }
        if (!empty(input('post.user_type'))){
            $where['USER_TYPE'] = array("EQ", input('post.user_type'));
        } else if (input('post.user_type') == '0'){
            $where['USER_TYPE'] = array("EQ", '0');
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['REG_TIME'] = array('BETWEEN',array(input('post.date_value_0'),input('post.date_value_1')));
        }
        $this->_where = $where;
        
        parent::list();
    }
    
    public function detail(){
        $id = input('post.id');
        $where = [
            "ID"    => $id,
        ];
        $info = db("User")->where($where)->find();
        if($info['SEX'] == '1'){
            $info['SEX_NAME'] = '男';
        } else if ($info['SEX'] == '2'){
            $info['SEX_NAME'] = '女';
        } else {
            $info['SEX_NAME'] = '保密';
        }
        $info['NATION_NAME'] = db("Mz")->where(['MZ_CODE'=>$info['NATION']])->value('NAME');
        rjson($info);
    }
    
    public function shebao(){
        $where = [
            'ID'    => input('post.id')
        ];
        
        $save = [
            'USER_TYPE' => '1'
        ];
        
        if( db('User')->where($where)->update($save) ){
            rjson('设置社保人员成功');
        } else {
            rjson_error('设置失败');
        }
    }
    
    public function puton(){
        $where = [
            'ID'    => input('post.id')
        ];
        
        $save = [
            'USER_TYPE' => '0'
        ];
        
        if( db('User')->where($where)->update($save) ){
            rjson('设置普通人员成功');
        } else {
            rjson_error('设置失败');
        }
    }
}