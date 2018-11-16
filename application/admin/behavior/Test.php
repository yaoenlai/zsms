<?php
namespace app\admin\behavior;

class Test
{
    public function run(&$params)
    {
        // 行为逻辑
        
    }
    
    //应用初始化标签位
    public function appInit(&$params)
    {
        $insert = [
            'ADMIN_ID'      => $params['admin_id']
            ,'TABLE_NAME'   => $params['table']
            ,'TYPE'         => $params['type']
            ,'CONDITION'    => json_encode($params['where'])
            ,'DATA'         => json_encode($params['data'])
            ,'ADD_TIME'     => time()
        ];
        db("Behavior")->insert($insert);
        
//         file_put_contents('log.json', json_encode($params)."\r\n", '8');
    }
    
    //应用结束标签位
    public function appEnd(&$params)
    {
        
    }  
    
}