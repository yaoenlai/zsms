<?php
/** 
 * 后台公共类
 *  */
namespace app\admin\controller;

use think\Controller;

class Common extends Controller
{
    protected $admin_id;        //登录用户ID
    protected $group_id;        //用户组ID
    protected $_where=[];       //查询条件
    protected $_order='ADDTIME DESC';   //排序       
    
    protected $_model;    
    
    public function _initialize(){
        if(request()->isPost()){
            $Auth = !empty($_SERVER['HTTP_AUTHORIZATION']) ? $_SERVER['HTTP_AUTHORIZATION'] : null;
            if(!empty($Auth)){
                $admin_id = db("AdminSession")->where(['ADMIN_S_ID'=>$Auth])->value('ADMIN_ID');
                if(!empty($admin_id)){
                    $this->admin_id=$admin_id;
                    $group_id = db("Admin")->where(['ADMIN_ID'=>$admin_id])->value("GROUP_ID");
                    if(!empty($group_id)){
                        $this->group_id = $group_id;
                    } else {
                        rjson_error('用户组信息不存在');
                    }
                } else {
                    rjson('', '201', '用户信息不存在');
                }
            } else {
                rjson_error('登录信息不存在');
            }
        } else {
            rjson_error('请求方式错误');
        }
        
        $this->_model = request()->controller();
    }
    
    //新增
    public function add(){
        
        $data = input('post.');
        $data['ADDTIME'] = date("Y-m-d H:i:s", time());
        $data['ADMIN_ID'] = $this->admin_id;
        
        $instal_data = array_change_key_case($data, CASE_UPPER );
        
        if ( db($this->_model)->insert($instal_data) ){
            rjson('添加成功');
        } else {
            rjson_error('添加失败');
        }
    }
    
    //删除
    public function del(){
       
        if( db($this->_model)->where(['ID'=>input('post.id')])->update(['IS_LOCK'=>'0']) ){
            rjson('删除成功');
        } else {
            rjson_error('删除失败');
        }
    }
    
    //还原
    public function rel(){
        if( db($this->_model)->where(['ID'=>input('post.id')])->update(['IS_LOCK'=>'1']) ){
            rjson('恢复成功');
        } else {
            rjson_error('恢复失败');
        }
    }
    
    //修改
    public function save(){
        $data = input("post.");
        $data['UPDATE_TIME'] = date("Y-m-d H:i:s", time());
        $data['UPDATE_ID'] = $this->admin_id;
        
        $save_data = array_change_key_case($data, CASE_UPPER );
        
        if( db($this->_model)->where(['ID'=>input('post.ID')])->update($save_data) ){
            rjson('修改成功');
        } else {
            rjson_error('修改失败');
        }
    }
    
    //列表
    public function list(){
        
        $page_index = empty(input('post.page_index')) ? "1" : input("post.page_index");
        $page_size = empty(input('post.page_size')) ? "20" : input("post.page_size");
        
        $data = [];
        $data['list'] = db($this->_model)->where($this->_where)->limit($page_size)->page($page_index)->order($this->_order)->select();
        $data['total'] = db($this->_model)->where($this->_where)->count();
        
        rjson($data);
    }
}