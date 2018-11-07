<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

return [
    '__pattern__' => [
        'name' => '\w+',
    ],
    '[hello]'     => [
        ':id'   => ['index/hello', ['method' => 'get'], ['id' => '\d+']],
        ':name' => ['index/hello', ['method' => 'post']],
    ],

    // 图片 文件下载API
    'img' 			=> 'Upload/Index/img',
    'down' 			=> 'Upload/Index/down',
    'card_img'      => 'Upload/Index/card_img',
    'retire_img'    => 'Upload/Index/retire_img',
    
    // 图片 上传
    'upload' 		=> 'Upload/Index/upload',
    'editor_upload' => 'Upload/Index/editor_upload',
    
    // 文件上传
    'upload_file' 	=> 'Upload/Index/upload_file',
    'replace_upload_file' 	=> 'Upload/Index/replace_upload_file',  // 覆盖上传
    'video_upload_file' 	=> 'Upload/Index/video_upload_file',  // 覆盖上传
];
