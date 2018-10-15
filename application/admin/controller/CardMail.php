<?php
namespace app\admin\controller;

class CardMail extends Common
{
    public function list(){
        $where = [];
        if(!empty(input('post.code'))){
            $where['C_CODE'] = array("LIKE", '%'.input('post.code').'%');
        }
        if( !empty(input('post.date_value_0')) && !empty(input('post.date_value_1')) ){
            $where['ADDTIME'] = array('BETWEEN',array(input('post.date_value_0'),input('post.date_value_1')));
        }
        $page_index = empty(input('post.page_index')) ? "1" : input("post.page_index");
        $page_size = empty(input('post.page_size')) ? "20" : input("post.page_size");
        
        $data = [];
        $data['list'] = db("Mail")->where($where)->limit($page_size)->page($page_index)->order('ADDTIME DESC')->select();
        $data['total'] = db("Mail")->where($where)->count();
        
        rjson($data);
    }
    
    public function getExpress(){
        $where = [];
        $list = db('Express')->where($where)->select();
        rjson($list);
    }
}