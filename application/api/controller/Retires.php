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
        foreach ($list as $key=>$value){
            $list[$key] = array_merge($value, getCItyName($value['AREA']));
        }
        rjson($list);
    }
    
    //获取退休会员信息列表
    public function getInfoList(){
        $where = [
            "CODE"  => $this->_postData['code']
        ];
        $list = db("RetireInfo")->where($where)->select();
        rjson($list);
    }
    
    //获取会员基本信息
    public function getInfo(){
        
        //判断是否存在退休数据
        $retire_info = (new Retire())->validatePolicy();
        if(empty($retire_info))
        {
            $retire_info = (new Retire())->add2($this->_loginInfo['U_ID'],$this->_userInfo["USERNAME"]);
        } 
        //获取基础信息
        $where = [
            'CODE'          => $this->_postData['code']
            ,'NAME'         => $this->_postData["name"]
            ,'XZ_CODE'      => $this->_postData['xz_code']
            ,'ZONE_CODE'    => $this->_postData['zone_code']
        ];
        
        $find = db('RetireInfo')->where($where)->find();
        $find['PID'] = $retire_info['ID'];
        $find['PREPAY_ID'] = $retire_info['PREPAY_ID'];
        rjson($find);
    }
    
    //活体验证
    public function live_verif(){
        $data = $this->_postData;
        
        $insert_data = [
            'U_ID'          => $this->_loginInfo['U_ID']
            ,'CREATE_TIME'  => time()
            ,'CREATE_DATE'  => date("Y-m-d H:i:s", time())
            ,'CODE'         => $data['code']
            ,'CYC'          => date("Ym")
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
            ,'CREATE_DATE'  => date("Y-m-d H:i:s", time())
            ,'IMAGE'        => $data['image']
            ,'CODE'         => $data['code']
            ,'CYC'          => date("Ym")
            ,'STATUS'       => $data['status']
            ,'PID'          => $data['pid']
            ,"TYPE"         => $data['type']
        ];
        
        if(!empty($data['image_ref1'])){ 
            $insert_data['IMAGE1'] = $data['image_ref1'];
            $insert_data['SIMILARITY1'] = $data['similarity1'];
        }
        if(!empty($data['image_ref2'])){ 
            $insert_data['IMAGE2'] = $data['image_ref2'];
            $insert_data['SIMILARITY2'] = $data['similarity2'];
        }
        if(!empty($data['image_ref3'])){ 
            $insert_data['IMAGE3'] = $data['image_ref3'];
            $insert_data['SIMILARITY3'] = $data['similarity3'];
        }
        
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
            ,'CYC'  => date("Ym")
        ];
        
        $save_data = [
            'PHONE'         => $data['phone']
            ,'AREA'         => $data['area']
            ,'ADDRESS'      => $data['address']
            ,'REMARKS'      => $data['remarks']
            ,'LIVE_STATUS'  => $data['live_status']
            ,'FACE_STATUS'  => $data['face_status']
            ,'INSURANCE'    => $data['insurance']
            ,'LIVE_NUM'     => db("RetireLive")->where($where2)->count()
            ,'FACE_NUM'     => db("RetireFace")->where($where2)->count()
            ,'SOURCE_IMG'   => $data['source_img']
            ,"TYPE"         => $data['type']
        ];
        if( db("Retire")->where($where)->update($save_data) ){
            rjson('认证完成');
        } else {
            rjson('', '400', '认证错误');
        }
    }
    
    /**
     * 获取对比照片
     * */
    public function getImage(){
        if(empty(input('post.code'))) rjson_error('身份证号码为空');
        
        $path = get_source_img( input('post.code') );
        if(!empty($path)){
            rjson($path);
        } else {
            rjson('','400','获取源照片失败');
        }
    }
}