<?php
use think\Hook;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
function rjson($data, $code='200', $msg='success'){
    
    echo json_encode(array('code'=>$code,'msg'=>$msg,'data'=>$data));die;
}

function rjson_error($msg){
    rjson('', '400', $msg);
}

/** 
 * 行为方法
 * @param string $name 行为名称
 * @param string $admin_id 操作人ID
 * @param string $table 操作表
 * @param string $type 操作类型 1、新增；2、编辑；3、删除；31、软删除；32、软还原；4、查询
 * @param array $where 条件
 * @param array $data 请求参数
 *  */
function behavior($name, $admin_id, $table, $type, $where=[], $data=[]){
    $params = [
        'admin_id'  => $admin_id
        ,'table'    => $table
        ,'type'     => $type
        ,'where'    => $where
        ,'data'     => $data
    ];
    Hook::listen($name, $params);
}

/**
 * 创建(导出)Excel数据表格
 * @param  array   $list        要导出的数组格式的数据
 * @param  string  $filename    导出的Excel表格数据表的文件名
 * @param  array   $indexKey    $list数组中与Excel表格表头$header中每个项目对应的字段的名字(key值)
 * @param  array   $startRow    第一条数据在Excel表格中起始行
 * @param  [bool]  $excel2007   是否生成Excel2007(.xlsx)以上兼容的数据表
 * 比如: $indexKey与$list数组对应关系如下:
 *     $indexKey = array('id','username','sex','age');
 *     $list = array(array('id'=>1,'username'=>'YQJ','sex'=>'男','age'=>24));
 */
function toExcel($list,$filename,$indexKey,$startRow=1,$excel2007=false){

    ob_end_clean();
    if(empty($filename)) $filename = time();
    if( !is_array($indexKey)) return false;
    
    $header_arr = array('A','B','C','D','E','F','G','H','I','J','K','L','M', 'N','O','P','Q','R','S','T','U','V','W','X','Y','Z');
    //初始化PHPExcel()
    $objPHPExcel = new \PHPExcel();
    
    //设置保存版本格式
    if($excel2007){
        $objWriter = new \PHPExcel_Writer_Excel2007($objPHPExcel);
        $filename = $filename.'.xlsx';
    }else{
        $objWriter = new \PHPExcel_Writer_Excel5($objPHPExcel);
        $filename = $filename.'.xls';
    }
    
    //接下来就是写数据到表格里面去
    $objActSheet = $objPHPExcel->getActiveSheet();
    //$startRow = 1;
    foreach ($list as $row) {
        foreach ($indexKey as $key => $value){
            //这里是设置单元格的内容
            $objActSheet->setCellValue($header_arr[$key].$startRow,$row[$value]);
        }
        $startRow++;
    }
    
    // 下载这个表格，在浏览器输出
//     header("Pragma: public");
//     header("Expires: 0");
//     header("Cache-Control:must-revalidate, post-check=0, pre-check=0");
//     header("Content-Type:application/force-download");
//     header("Content-Type:application/vnd.ms-execl");
//     header("Content-Type:application/octet-stream");
//     header("Content-Type:application/download");;
//     header('Content-Disposition:attachment;filename='.$filename.'');
//     header("Content-Transfer-Encoding:binary");
//     $objWriter->save('php://output');

    $file_path = './Uploads/excel/'.$filename;
    if(mkdirs($file_path)){
        //保存文件
        $objWriter->save($file_path);
        return "/excel/".$filename;
    } else {
        return false;
    }
    
}

/**
* Created by PhpStorm.
* function: data_import
* Description:导入数据
* User: Xiaoxie
* @param $filename
* @param string $exts
*
*/
function data_import($filename, $exts = 'xls'){
    //创建PHPExcel对象，注意，不能少了
    $PHPExcel = new \PHPExcel();
    //如果excel文件后缀名为.xls，导入这个类
    if ($exts == 'xls') {
        
        Vendor('PHPExcel.PHPExcel.Reader.Excel5');
        $PHPReader = new \PHPExcel_Reader_Excel5();
    } else if ($exts == 'xlsx') {
        
        Vendor('PHPExcel.PHPExcel.Reader.Excel2007');
        $PHPReader = new \PHPExcel_Reader_Excel2007();
    }
    //载入文件
    $PHPExcel = $PHPReader->load($filename);
    //获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
    $currentSheet = $PHPExcel->getSheet(0);
    //获取总列数
    $allColumn = $currentSheet->getHighestColumn();
    //获取总行数
    $allRow = $currentSheet->getHighestRow();
    //循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始
    $data = [];
    for ($currentRow = 1; $currentRow <= $allRow; $currentRow++) {
        //从哪列开始，A表示第一列
        for ($currentColumn = 'A'; $currentColumn <= $allColumn; $currentColumn++) {
            //数据坐标
            $address = $currentColumn . $currentRow;
            //读取到的数据，保存到数组$data中
            $cell = $currentSheet->getCell($address)->getValue();
            
            if ($cell instanceof PHPExcel_RichText) {
                $cell = $cell->__toString();
            }
            
            $data[$currentRow - 1][$currentColumn] = $cell;
        }
    }
    return $data;
}
