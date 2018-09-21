<?php 
/** 
 * 退休类
 *  */
namespace app\api\controller;

use app\api\model\Retire;

class Retires extends Common
{
    //退休申请列表
    public function index(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
        ];
        $list = db('Retire')->where($where)->select();
        rjson($list);
    }
    
    //申请退休
    public function add(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'C_CODE'    => $this->_postData['c_code'],
        ];
        if( db('Retire')->where($where)->count() ){
            rjson('', '400', '该身份证已申请');
        } else {
            (new Retire())->add($this->_loginInfo['U_ID']);
        }
    }  
    
    public function edit(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'ID'        => $this->_postData['retire_id'],
        ];
        if( db('Retire')->where($where)->count() ){
            (new Retire())->edit();
        } else {
            rjson('', '400', '请检查退休申请号');
        }
    }
    
    /**
     * 获取二寸照片
     *  @param AAC999 个人管理编码
     * */
    public function getImage(){
        $where = [
            'C_CODE'    => input('post.code')
            ,'U_ID'     => $this->_loginInfo["U_ID"]
        ];
        $head_img = db('Card')->where($where)->value('HEAD_IMG');
        if(empty($head_img)){
            rjson('', '400', '该身份证没有录入图片');
        } else {
            rjson($head_img);
        }
    }
}