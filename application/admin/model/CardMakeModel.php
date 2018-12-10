<?php
namespace app\admin\model;

use think\Model;
use think\Db;

class CardMakeModel extends Model
{
    public function upload($data){
        Db::startTrans();
        try {
            foreach ($data AS $key => $value){
                $where = [
                    'ID'    => $value['A']
                ];
                $save_data = [
                    'EXPRESS_NUM'   => $value['B'],
                    'ADD_DATE'      => date("Y-m-d H:i:s"),
                    'ADD_TIME'      => time(),
                ];
                db("CardMake")->where($where)->update($save_data);
            }
            Db::commit();
            return true;
        } catch (\Exception $e){
            Db::rollback();
            return false;
        }
    }
}