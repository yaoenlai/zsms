<?php
namespace app\pay\controller;

use think\Controller;
use think\Db;

class Common extends Controller
{
    protected $perpay_id;       //订单号
    protected $pay_ment;        //支付方式：1：云支付 2：支付宝 3：微信
    protected $pay_money;       //支付金额
    
    private $pid;
        
    public function __destruct(){
        Db::startTrans();
        try {          
            //记录订单号不存在
            if(empty($this->perpay_id)) exception('订单号为空');
         
            $where = [
                'PREPAY_ID' => $this->perpay_id
            ];
            $info = db("Order")->where($where)->find();
            //记录订单号错误
            if(empty($info)) exception('订单号['.$this->perpay_id.']不存在');    
                
            //判断是否已支付
            if($info['STATUS'] == '1') exception('订单号['.$this->perpay_id.']已支付'); 
            
            $return_status = false;
            $u_id = $info['U_ID'];
            switch ($info['TYPE']){
                case '1':
                    $return_status = $this->card();
                    msg_add('社保卡办理','社保卡办理支付成功',$u_id);
                    break;
                case '2':
                    $return_status = $this->card_mail();
                    msg_add('社保卡邮寄','社保卡邮寄支付成功',$u_id);
                    break;
                case '3':
                    $return_status = $this->retire();
                    msg_add('退休认证','退休认证寄支付成功',$u_id);
                    break;
            }
            if(empty($return_status)){
                exception("处理项目出错，订单号[".$this->perpay_id."]");
            }
            $save = [
                'STATUS'        => '1'
                ,'PID'          => $this->pid
                ,'FINISH_TIME'  => time()
                ,'FINISH_DATE'  => date("Y-m-d H:i:s")
                ,'PRICE'        => $this->pay_money
                ,'PAYMENT'      => $this->pay_ment
            ];
            
            if( db("Order")->where($where)->update($save) ){
                Db::commit();
            } else {
                exception('订单['.$this->perpay_id.']修改失败');
            }
        } catch (\Exception $e){
            Db::rollback();
            $this->log( ['msg'=>$e->getMessage(),'pay_ment'=>$this->pay_ment] );
            die;
        }
    }
    
    private function card(){
        /* 获取社保基础信息 */
        $where = [
            'PREPAY_ID' => $this->perpay_id
            ,'IS_LOCK'  => '1'
        ];
        $card_info = db("Card")->where($where)->find();
        if(empty($card_info)){
            return false;
        }
        
        /* 修改社保基础信息 */
        $save = [
            'IS_PAY'    => 1
            ,'PAY_TIME' => time()
            ,'LR_TYPE'  => '1'
        ];
        if(! db('Card')->where($where)->update($save) ){
            return false;
        } 
        
        $this->pid = $card_info['ID'];
        return true;
    }
    
    private function card_mail(){
        $where = [
            "PREPAY_ID" => $this->perpay_id,
        ];
        $info = db("CardMail")->where($where)->find();
        
        if(empty($info)){
            return false;
        }

        //修改社保邮寄状态
        $save = [
            'IS_PAY'       => 1,
            'PAY_TIME'     => time(),
            'STEP_STSTUS'  => 2,
            'LR_TYPE'      => 1,
        ];
        if(! db('CardMail')->where($where)->update($save)){
            return false;
        }
        
        //修改社保卡邮寄状态
        $where = [
            'ID'=> $info['CARD_ID']
        ];
        $save = [
            'IS_EXPRESS'=>'3'
        ];
        if(! db('Card')->where($where)->update($save)){
            return false;
        }
        
        $this->pid = $info['ID'];
        return true;
    }
    
    private function retire(){
        $where = [
            'IS_LOCK'   => '1',
            'PREPAY_ID' => $this->perpay_id,
        ];
        $info = db('Retire')->where($where)->find();
        if(empty($info)){
            return false;
        }
        //修改退休认证状态
        $save = [
            'IS_PAY'       => 1,
            'PAY_TIME'     => time(),
            'LR_TYPE'      => 1,
        ];
        if(! db('Retire')->where($where)->update($save) ){
            return false;
        }
        
        $this->pid = $info['ID'];
        return true;
    }
    
    private function log(array $data, $is_error=true){
        if($is_error){
            $path = "./notify_error/".date("Ym")."/".date("d").".txt";
        } else {
            $path = "./notify_success/".date("Ym")."/".date("d").".txt";
        }
        
        if(mkdirs($path)){
            file_put_contents($path, json_encode($data)."\r\n", '8');
        }
    }
}