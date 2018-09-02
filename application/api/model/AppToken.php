<?php
namespace app\api\model;

use think\Model;

class AppToken extends Model
{
    public function getFind($where){
        if($this->where($where)->count()){
            $find = $this->where($where)->find()->toArray();
        } else {
            $find = [];
        }
        return $find;
    }
}