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
        $list = db('Retire')->where($where)->limit($this->page_size)->page($this->page_index)->select();
        foreach ($list as $key=>$value){
            $list[$key] = array_merge($value, getCItyName($value['AREA']));
        }
        rjson($list);
    }
    
    //退休详情
    public function detail(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'PREPAY_ID' => input('post.prepay_id'),
            'IS_LOCK'   => '1',
        ];
        $info= db('Retire')->where($where)->find();
        $info = array_merge($info, getCItyName($info['AREA']));
        
        rjson($info);
    }
    
    //获取退休人员区域
    public function getInfoZone(){
        $where = [
            "CODE"  => $this->_postData['code']
        ];
        $list = db("RetireInfo")->field('ZONE_CODE,ZONE_NAME')->where($where)->group('ZONE_CODE,ZONE_NAME')->select();
        rjson($list);
    }
    
    //获取退休会员险种
    public function getInfoList(){
        $where = [
            "CODE"      => $this->_postData['code']
            ,"ZONE_CODE"=> $this->_postData['zone_code']
        ];
        $list = db("RetireInfo")->field('ZONE_CODE,XZ_CODE,XZ_NAME')->where($where)->select();
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
        if(empty($find)){
            rjson('', '400', '未找到退休人员认证信息，请与当地参保机构联系');
        }
        $find['PID'] = $retire_info['ID'];
        //判断是否需要支付
        if($find['IS_PAY'] == '1'){
            $find['PREPAY_ID'] = $retire_info['PREPAY_ID'];
            $find['STATUS'] = db("Order")->where(['PREPAY_ID'=>$retire_info['PREPAY_ID'],'TYPE'=>'3'])->value('STATUS');
        } else {
            db("Order")->where(['PREPAY_ID'=>$retire_info['PREPAY_ID']])->delete();
        }
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
      
        if( (new Retire())->add() ){
            msg_add('退休认证', '退休认证完成', $this->_loginInfo['U_ID']);
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
        
        $image_path = "/retire_img/source_img/".input('post.code').".jpg";
        $path = config('file_path.retire_path').$image_path;
        
        if(mkdirs($path)){
            if( get_source_img( input('post.code'),  $path) ){
                rjson('/retire_img?path='.$image_path);
            } else {
                rjson('','400','获取源照片失败');
            }
        } else {
            rjson('', '400', '目录创建失败');
        }
        
        
    }
    
    //活体截图、拍照照片
    public function retire_upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(config('file_path.retire_path'). '/retire_img');
            if($info){
                // 成功上传后 获取上传信息
                $return = array(
                    'ext'       => $info->getExtension(),
                    'path'      => '/retire_img?path='.'/retire_img/'.$info->getSaveName(),
                    'file_name' => $info->getFilename()
                );;
                rjson($return);
            }else{
                // 上传失败获取错误信息
                rjson('', '400', $file->getError());
            }
        }
    }
}