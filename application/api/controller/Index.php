<?php 
namespace app\api\controller;

use think\Controller;

class Index extends Controller
{
    public function index(){
        if( db('admin')->select() ){
            echo "OK";
        }
    }
}