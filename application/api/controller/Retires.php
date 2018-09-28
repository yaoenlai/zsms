<?php 
/** 
 * @name 退休类
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
    
    //获取会员基本信息
    public function getInfo(){
        $where = [
            'CODE'  => $this->_postData['code']
            ,'CYC'  => date("Ymd")
        ];
        
        //判断是否存在退休数据
        $retire_info = db("Retire")->where($where)->find();
        if(empty($retire_info))
        {
            $retire_info = (new Retire())->add2($this->_loginInfo['U_ID'],$this->_userInfo["USERNAME"]);
        }
        //获取基础信息
        unset($where['CYC']);
        $where['NAME'] = $this->_postData['name'];
        
        $find = db('RetireInfo')->where($where)->find();
        $find['PID'] = $retire_info['ID'];
        rjson($find);
    }
    
    //活体验证
    public function live_verif(){
        $data = $this->_postData;
        
        $insert_data = [
            'U_ID'          => $this->_loginInfo['U_ID']
            ,'CREATE_TIME'  => time()
            ,'CODE'         => $data['code']
            ,'CYC'          => $data['cyc']
            ,'STATUS'       => $data['status']
            ,'PID'          => $data['pid']
        ];
        
        if( db("RetireLive")->insert($insert_data) ){
            rjson('记录成功');
        } else {
            rjson('', '400', '记录失败');
        }
    }
    
    //人脸识别
    public function face_verif(){
        $data = $this->_postData;
        $insert_data = [
            'U_ID'          => $this->_loginInfo['U_ID']
            ,'CREATE_TIME'  => time()
            ,'IMAGE'        => $data['image']
            ,'CODE'         => $data['code']
            ,'CYC'          => $data['cyc']
            ,'STATUS'       => $data['status']
            ,'PID'          => $data['pid']
        ];
        
        if(!empty($data['image_ref1'])) $insert_data['IMAGE1'] = $data['image_ref1'];
        if(!empty($data['image_ref2'])) $insert_data['IMAGE2'] = $data['image_ref2'];
        if(!empty($data['image_ref3'])) $insert_data['IMAGE3'] = $data['image_ref3'];
        
        //删除成功的照片
//         unlink(ROOT_PATH.'/public/'.input('post.image'));
//         if(!empty($data['image_ref1'])) unlink(ROOT_PATH.'/public/'.input('post.image_ref1'));
//         if(!empty($data['image_ref2'])) unlink(ROOT_PATH.'/public/'.input('post.image_ref2'));
//         if(!empty($data['image_ref3'])) unlink(ROOT_PATH.'/public/'.input('post.image_ref3'));
        
        if( db("RetireFace")->insert($insert_data) ){
            rjson('记录成功');
        } else {
            rjson('', '400', '记录失败');
        }
    }
    
    //退休认证
    public function add(){
      
        $data = $this->_postData;
        
        $where = [
            'ID'   => $data['pid']
        ];
        
        $where2 = [
            'CODE'  => $data['code']
            ,'CYC'  => date("Ymd")
        ];
        
        $save_data = [
            'PHONE'         => $data['phone']
            ,'AREA'         => $data['area']
            ,'ADDRESS'      => $data['address']
            ,'REMARKS'      => $data['remarks']
            ,'LIVE_STATUS'  => $data['live_status']
            ,'FACE_STATUS'  => $data['face_status']
            ,'LIVE_NUM'     => db("RetireLive")->where($where2)->count()
            ,'FACE_NUM'     => db("RetireFace")->where($where2)->count()
        ];
        
        if( db("Retire")->where($where)->update($save_data) ){
            rjson('认证完成');
        } else {
            rjson('', '400', '认证错误');
        }
    }
    
    /**
     * 获取对比照片
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