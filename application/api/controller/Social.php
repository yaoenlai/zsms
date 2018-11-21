<?php
/**
 * 社保 
 *  */
namespace app\api\controller;

use think\Controller;
use app\api\model\Card;
use app\api\model\Retire;

class Social extends Common
{
    
    //验证是否申请过社保卡(开始申请)
    public function card_validate(){
        
        $return = (new Card())->card_validate($this->_loginInfo['U_ID']);
        
        switch ($return['status']){
            case '1':
                rjson('此身份证未录入信息，可以录入');
                break;
            case '2':
                rjson(['prepay_id'=>$return['prepay_id']], '201', '此身份证未支付');
                break;
            case '3':
                rjson(['card_id'=>$return['card_id']], '202', '此身份证未拍照');
                break;
            case '4':
                rjson(['card_id'=>$return['card_id']], '203', '证件照审核不通过');
                break;
            case '5':
                rjson('', '204', '该用户办理次数已达到3次');
                break;
            default:
                rjson('', '400', '该人员已采集社保卡信息，请到历史社保卡进度查询详情！');
        }       
    }
    
    //生成临时订单
    public function temp_order(){
        $where = array(
            "u_id"      => $this->_loginInfo['U_ID'],
            "STATUS"    => '2',
            "STATE"     => '1',
            "TYPE"      => input('post.type'),
            "DELETED"   => '0',
        );
        $info = db("order")->field("id,prepay_id,numbers")->where($where)->find();
        //验证是否有未完成订单
        if(!empty($info)){
            //废弃未完成订单
            if(! db('order')->where($where)->update(array('DELETED'=>'1')) ){   
                
                rjson('', '400', showRegError(-16));
            }
        }
        
        $userInfo = db('user')->field("id,username,phone,code")->where([ "id"=>$this->_loginInfo["U_ID"] ])->find();
        (new Card())->addOrder($userInfo);
    }

    //支付订单 （回调获取）
    public function pay_order(){
        switch (input('post.pay_type')){
            case '1': 
                $this->pay_card_order();
                break;
            case '2':
                $this->pay_card_mail_order();
                break;
            case '3':
                $this->pay_retires_order();
                break;
            default:
                rjson('', '400', '未知支付类型, pay_type:['.input('post.pay_type').']');
                break;
        }
    }
    
    //支付社保订单
    private function pay_card_order(){
        $where = array(
            "prepay_id" => input('post.prepay_id')
        );
        $info = db("CardOrderBak")->where($where)->find();
        if(!empty($info)){
            msg_add('社保卡办理','社保卡办理支付成功',$this->_loginInfo['U_ID']);
            (new Card())->addCard();
        } else {
            rjson('', '400', '该订单有问题，请检查');
        }
    }
    
    //社保邮寄费用支付
    private function pay_card_mail_order(){
        $where = array(
            "prepay_id" => input('post.prepay_id'),
        );
        $info = db("CardMail")->where($where)->find();
        if( !empty($info) ){
            msg_add('社保卡邮寄','社保卡邮寄支付成功',$this->_loginInfo['U_ID']);
            (new Card())->addCardMailPay($info["ID"],$info['CARD_ID']);
        } else {
            rjson('', '400', '该订单有问题，请检查');
        }
    }
    
    //退休支付
    private function pay_retires_order(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
            'PREPAY_ID' => $this->_postData['prepay_id'],
        ];
        $info = db('Retire')->where($where)->find();
        if( !empty($info) ){
            msg_add('退休认证','退休认证寄支付成功',$this->_loginInfo['U_ID']);
            (new Retire())->orderPay($info['ID']);
        } else {
            rjson('', '400', '请检查订单号');
        }
    }
    
    //录入监护人信息/修改监督人信息
    public function guardian(){
        $data = input('post.');
        
        $gua_list = [
            'GUARDIAN_NAME'         => $data['guardian_name'],
            'GUARDIAN_PHONE'        => $data['guardian_phone'],
            'GUARDIAN_CARD'         => $data['guardian_card'],
            'GUARDIAN_SEX'          => $data['guardian_sex'],
            'GUARDIAN_ADDRESS'      => $data['guardian_address'],
            'GUARDIAN_START_TIME'   => strtotime($data['guardian_start_time']),
            'GUARDIAN_END_TIME'     => strtotime($data['guardian_end_time']),
            'GUARDIAN_START_DATE'   => $data['guardian_start_time'],
            'GUARDIAN_END_DATE'     => $data['guardian_end_time'],
            'GUARDIAN_RELATION'     => $data['guardian_relation'],
            'FRONT_IMG'             => $data['front_img'],
            'OPPOSITE_IMG'          => $data['opposite_img'],
        ];
        
        //判断监护人是否已填写
        if( db('guardian')->where([ 'PID'=>$data['card_id'] ])->count() ){
            $where = [
                'PID'   => $data['card_id']
            ];
            $gua_list['UPDATE_TIME']    = time();
            if( db("guardian")->where($where)->update($gua_list) ){
                rjson('监护人信息修改成功');
            } else {
                rjson('', '400', showRegError(-16));
            }
        } else {
            $gua_list['PID']        = $data['card_id'];
            $gua_list['ADD_TIME']   = time();
            $gua_list['ADD_DATE']   = date("Y-m-d H:i:s");
            
            if(empty($gua_list['FRONT_IMG']) || empty($gua_list['OPPOSITE_IMG'])) rjson('', '400', '监护人身份证拍摄不全');
            
            if( db("guardian")->insert($gua_list) ){
                rjson('监护人信息录入成功');
            } else {
                rjson('', '400', showRegError(-16));
            }
        }
    }
    
    //上传2寸照片(申请完成)
    public function update_head_img(){
               
        (new Card())->update_head_img($this->_loginInfo['U_ID']);
    }
    
    //获取已提交社保列表
    public function card_list(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'IS_LOCK'   => '1',
        ];
        $list = db('card')->where($where)->limit($this->page_size)->page($this->page_index)->order('C_ADD_TIME DESC')->select();
        rjson($list);
    }
    
    //获取社保详情
    public function card_detail(){
        $where = [
            'U_ID'      => $this->_loginInfo['U_ID'],
            'PREPAY_ID' => input('post.prepay_id'),
        ];
        $info = db('cardOrderBak')->where($where)->find();
        if(empty($info)){
            rjson('', '400', '该用户此订单号有问题,请检查该订单号');
        }
        $info = array_merge($info, getCItyName($info['NOW_AREA']));
        rjson($info);
    }
    
    //获取监护人详情
    public function guardian_detail(){
        $where = [
            'PID'   => input('post.card_id'),
        ];
        $info = db("guardian")->where($where)->find();
        rjson($info);
    }
    
    //保存社保详情
    public function card_detail_edit(){
        
        $where = [
            'U_ID'          => $this->_loginInfo['U_ID'],
            'PREPAY_ID'     => input('post.prepay_id'),
            'EXAM_STATUS'   => 2,
        ];
        if( db('card')->where($where)->count() ) {
            (new Card())->cardEdit();
        } else {
            rjson('', '400', '该社保状态不能修改');
        }
    }
    
    //获取无水印照片（获取二寸照照片）
    public function take_cut_pic(){
        $url = input('post.url');
        $post_data = [
            'file_name' => input('post.file_name')
            ,'app_key'  => input('post.app_key')
        ];
        
        $data = $this->http_url($url, json_encode($post_data));
        
        $image_path = '/card_img/source_img/'.$post_data['file_name'].'.jpg';
        $path = config('file_path.card_path').$image_path;
        
        if (mkdirs($path)){
            file_put_contents($path, $data);
            rjson('/card_img?path='.$image_path);
        } else {
            rjson('', '400', '创建路径失败');
        }
    }
    private function http_url($url,$data=null){
        
        $headers = array(
            'Content-Type: application/json;charset=utf-8',
            'Content-Length: ' . strlen($data),
            'y-cli:pc'
        );
        
        $curl=curl_init();
        //设置请求地址
        curl_setopt($curl,CURLOPT_URL,$url);
        curl_setopt($curl,CURLOPT_POST,1);
        curl_setopt($curl,CURLOPT_SSL_VERIFYPEER,false);
        curl_setopt($curl,CURLOPT_SSL_VERIFYHOST,false);
        curl_setopt($curl,CURLOPT_POSTFIELDS,$data);
        curl_setopt($curl,CURLOPT_RETURNTRANSFER,1);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        $output=curl_exec($curl);
        curl_close($curl);
        return $output;
    }
    
    //身份证正反面照片上传
    public function card_upload(){
        // 获取表单上传文件 例如上传了001.jpg
        $file = request()->file('file');
        
        // 移动到框架应用根目录/public/uploads/ 目录下
        if($file){
            $info = $file->move(config('file_path.card_path'). '/card_img');
            if($info){
                // 成功上传后 获取上传信息
                $return = array(
                    'ext'       => $info->getExtension(),
                    'path'      => '/card_img?path='.'/card_img/'.$info->getSaveName(),
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