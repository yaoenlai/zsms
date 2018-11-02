<?php
/**  
 * 文章资讯
 *  */
namespace app\api\controller;

use think\Controller;

class News extends Controller
{
    //文章分类列表
    public function getClass(){
        $list = db("NewsClass")->where(['IS_LOCK'=>'1'])->select();
        rjson($list);
    }
    //文章列表
    public function getList(){
        $data = input('post.');
        $where = [
            'IS_LOCK'   => '1',
            'PID'       => $data['pid'],
        ];
        $list = db('News')->field('ID,TITLE,HEAD_IMG')->where($where)->select();
        rjson($list);
    }
    //文章详情
    public function detail(){
        $where = [
            'ID'    => input('post.id')
        ];
        $info = db('News')->where($where)->find();
        $info["CONTENT"] = getContent($info['CONTENT']);
        rjson($info);
    }
    
    //搜素文章
    public function search(){
        $where = [
            "TITLE" => array('like', '%'.input('post.title').'%')
        ];
        $list = db("News2")->where($where)->select();
        rjson($list);
    }
}