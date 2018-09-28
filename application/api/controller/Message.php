<?php
/**
 * 消息类 
 *  */
namespace app\api\controller;

class Message extends Common
{
    //通知
    public function notice(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID']
            ,'IS_LOCK'  => '1'
        ];
        
        if(!empty($this->_postData['status'])) $where['STATUS'] = $this->_postData['status'];
        
        $list = db("Msg")->where($where)->order('ADDTIME DESC')->select();
        rjson($list);
    }
    
    //阅读通知
    public function read_notice(){
        $data = $this->_postData;
        $where = [
            'U_ID'  => $this->_loginInfo["U_ID"]
            ,'ID'   => $data['id']
        ];
        if( db("Msg")->where($where)->update(['STATUS'=>'2']) ){
            rjson('已读成功');
        } else {
            rjson('', '400', '阅读失败');
        }
    }
    
    //用户消息列表
    
    //消息详情
}