<?php 
/** 
 * 收货地址
 *  */
namespace app\api\controller;

class Address extends Common
{
    
    //可用地址列表
    public function index(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
        ];
        $list = db("Address")->where($where)->order('IS_DEFAULT DESC')->select();
        foreach ($list AS $key=>$value){
            $list[$key] = array_merge($value, getCItyName($value['AREA']));
        }
        rjson($list);
    }
    
    //新增地址
    public function add(){
        $insert = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'NAME'      => $this->_postData['name'],
            'PHONE'     => $this->_postData['phone'],
            'EMAIL'     => $this->_postData['email'],
            'AREA'      => $this->_postData['area'],
            'ADDRESSS'  => $this->_postData['address'],
        ];
        if( db('Address')->insert($insert) )
        {
            rjson('添加成功');
        } else {
            rjson('', '400', showRegError(-16));
        }
    }
    
    //修改地址
    public function edit(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'ID'        => $this->_postData['id'],
        ];
        $save = [
            'NAME'      => $this->_postData['name'],
            'PHONE'     => $this->_postData['phone'],
            'EMAIL'     => $this->_postData['email'],
            'AREA'      => $this->_postData['area'],
            'ADDRESSS'  => $this->_postData['address'],
        ];
        if( db('Address')->where($where)->update($save) )
        {
            rjson('修改成功');
        } else {
            rjson('', '400', showRegError(-16));
        }
    }
    
    //删除地址
    public function del(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'ID'        => $this->_postData['id'],
        ];
        
        if( db('Address')->where($where)->update(['IS_LOCK'=>'0']) )
        {
            rjson('删除成功');
        } else {
            rjson('', '400', '删除失败');
        }
    }
    
    //设定默认地址
    public function default(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'ID'        => $this->_postData['id'],
        ];
        
        if( db('Address')->where(['IS_LOCK'=>'1'])->update(['IS_DEFAULT'=>'0']) && db('Address')->where($where)->update(['IS_DEFAULT'=>'1']) )
        {
            rjson('设定成功');
        } else {
            rjson('', '400', '设定失败，检查是否已是默认地址');
        }
    }
    
}