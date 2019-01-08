<?php
namespace app\index\controller;

use think\Controller;
use think\Request;

class Index extends Controller
{
    //分享用户获取积分
    public function index($u_id){
        
        $send_info = db('User')->where(['ID'=>$u_id])->find();
        if(empty($send_info)){
            $this->error('当前地址有问题');
        }

        if(Request::instance()->isPost()){
            if(empty(input('post.phone'))){
                rjson('', '400', '请输入手机号');
            }
            $where1 = [
                'PHONE'     => input('post.phone')
                ,'IS_LOCK'  => '1'
            ];
            $info = db("User")->where($where1)->find();
            if(empty($info)){
                rjson('', '400', '请先去下载app注册');
            } else {
                if(empty($info['PID'])){
                    
                    db('User')->where($where1)->update(['PID'=>$u_id]);
                    $insert1 = [
                        [
                            'U_ID'      => $send_info['ID']
                            ,'VALUE'    => '50'
                            ,'TYPE'     => '1'
                            ,'ADDTIME'  => time()
                            ,'REMARKS'  => '分享给用户['.$info['USERNAME'].']获得积分'
                        ]
                        ,[
                            'U_ID'      => $info['ID']
                            ,'VALUE'    => '50'
                            ,'TYPE'     => '1'
                            ,'ADDTIME'  => time()
                            ,'REMARKS'  => '接受用户['.$send_info['USERNAME'].']分享获得积分'
                        ]
                    ];
                    if( db('UserIntegral')->insertAll($insert1) ){
                        rjson('恭喜你获取50积分');
                    } else {
                        rjson('', '400', '发生未知错误');                        
                    }
                } else {
                    rjson('', '400', '当前手机号['.input('post.phone').']已参加过活动了');
                }
            }
        } else {
            $this->assign('info', $send_info);
            return $this->fetch();
        }
    }
    
    //分享文章
    public function share_news($news_id){
        
    }
}