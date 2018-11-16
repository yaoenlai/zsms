<?php
namespace app\admin\controller;

class Admin extends Common
{
    public function list(){
        $this->_order='ADD_TIME desc';
        
        parent::list();
    }
    
    //新增
    public function add(){
        
        $data = input('post.');
        $data['ADD_TIME']    = date("Y-m-d H:i:s", time());
        $data['PID']   = $this->admin_id;
        $data['PWD']        = md5('admin123');
        $data['IP']         = getIp();
        
        $instal_data = array_change_key_case($data, CASE_UPPER );
        
        if ( db('Admin')->insert($instal_data) ){
            behavior('app_init', $this->admin_id, 'Admin', '1', [], $instal_data);
            rjson('添加成功');
        } else {
            rjson_error('添加失败');
        }
    }
    
    //删除
    public function del(){
        
        $this->_where = ['ADMIN_ID'=>input('post.id')];
        
        parent::del();
    }
    
    //还原
    public function rel(){
        
        $this->_where = ['ADMIN_ID'=>input('post.id')];
        
        parent::rel();
    }
    
    //修改
    public function save(){
        
        $this->_where = ['ADMIN_ID'=>input('post.ADMIN_ID')];
        
        parent::save();
    }
    
    //个人信息
    public function detail(){
        $where = [
            'admin_id'  => $this->admin_id,
        ];
        $info = db("Admin")->where($where)->find();
        $info['GROUP_NAME'] = db("Group")->where(['ID'=>$info['GROUP_ID']])->value("NAME");
        rjson($info);
    }
    
    //修改个人信息
    public function editInfo(){
        $data = input('post.');
        if( $data['ADMIN_ID'] != $this->admin_id){
            rjson('', '400', '操作失败');
        }
        $where = [
            'admin_id'  => $this->admin_id,
        ];
        if( db("Admin")->where($where)->update($data) ){
            behavior('app_init', $this->admin_id, 'Admin', '2', $where, $data);
            rjson('修改成功');
        } else {
            rjson('', '400', '修改失败');
        }
    }
    
    //修改密码
    public function editPwd(){
        $data = input('post.');
        $where = [
            'admin_id'  => $this->admin_id,
            'pwd'       => md5($data['oldPassWord']),
        ];
        if( db("Admin")->where($where)->count() <= 0){
            rjson('', '400', '历史密码错误');
        }
        
        $where = [
            'admin_id'  => $this->admin_id,
        ];
        $save_data = [
            'PWD'   => md5($data['newPassWord']),
        ];
        if( db('Admin')->where($where)->update($save_data) ){
            behavior('app_init', $this->admin_id, 'Admin', '2', $where, $save_data);
            rjson('修改成功');
        } else {
            rjson('', '400', '修改失败');
        }
    }
}