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
    // +----------------------------------------------------------------------
    // | 应用设置
    // +----------------------------------------------------------------------

    // 应用调试模式
    'app_debug'              => true,
    // 应用Trace
    'app_trace'              => false,
    // 应用模式状态
    'app_status'             => '',
    // 是否支持多模块
    'app_multi_module'       => true,
    // 入口自动绑定模块
    'auto_bind_module'       => false,
    // 注册的根命名空间
    'root_namespace'         => [],
    // 扩展函数文件
    'extra_file_list'        => [THINK_PATH . 'helper' . EXT],
    // 默认输出类型
    'default_return_type'    => 'html',
    // 默认AJAX 数据返回格式,可选json xml ...
    'default_ajax_return'    => 'json',
    // 默认JSONP格式返回的处理方法
    'default_jsonp_handler'  => 'jsonpReturn',
    // 默认JSONP处理方法
    'var_jsonp_handler'      => 'callback',
    // 默认时区
    'default_timezone'       => 'PRC',
    // 是否开启多语言
    'lang_switch_on'         => false,
    // 默认全局过滤方法 用逗号分隔多个
    'default_filter'         => '',
    // 默认语言
    'default_lang'           => 'zh-cn',
    // 应用类库后缀
    'class_suffix'           => false,
    // 控制器类后缀
    'controller_suffix'      => false,

    // +----------------------------------------------------------------------
    // | 模块设置
    // +----------------------------------------------------------------------

    // 默认模块名
    'default_module'         => 'api',
    // 禁止访问模块
    'deny_module_list'       => ['common'],
    // 默认控制器名
    'default_controller'     => 'Index',
    // 默认操作名
    'default_action'         => 'index',
    // 默认验证器
    'default_validate'       => '',
    // 默认的空控制器名
    'empty_controller'       => 'Error',
    // 操作方法后缀
    'action_suffix'          => '',
    // 自动搜索控制器
    'controller_auto_search' => false,

    // +----------------------------------------------------------------------
    // | URL设置
    // +----------------------------------------------------------------------

    // PATHINFO变量名 用于兼容模式
    'var_pathinfo'           => 's',
    // 兼容PATH_INFO获取
    'pathinfo_fetch'         => ['ORIG_PATH_INFO', 'REDIRECT_PATH_INFO', 'REDIRECT_URL'],
    // pathinfo分隔符
    'pathinfo_depr'          => '/',
    // URL伪静态后缀
    'url_html_suffix'        => 'html',
    // URL普通方式参数 用于自动生成
    'url_common_param'       => false,
    // URL参数方式 0 按名称成对解析 1 按顺序解析
    'url_param_type'         => 0,
    // 是否开启路由
    'url_route_on'           => true,
    // 路由使用完整匹配
    'route_complete_match'   => false,
    // 路由配置文件（支持配置多个）
    'route_config_file'      => ['route'],
    // 是否强制使用路由
    'url_route_must'         => false,
    // 域名部署
    'url_domain_deploy'      => false,
    // 域名根，如thinkphp.cn
    'url_domain_root'        => '',
    // 是否自动转换URL中的控制器和操作名
    'url_convert'            => true,
    // 默认的访问控制器层
    'url_controller_layer'   => 'controller',
    // 表单请求类型伪装变量
    'var_method'             => '_method',
    // 表单ajax伪装变量
    'var_ajax'               => '_ajax',
    // 表单pjax伪装变量
    'var_pjax'               => '_pjax',
    // 是否开启请求缓存 true自动缓存 支持设置请求缓存规则
    'request_cache'          => false,
    // 请求缓存有效期
    'request_cache_expire'   => null,
    // 全局请求缓存排除规则
    'request_cache_except'   => [],

    // +----------------------------------------------------------------------
    // | 模板设置
    // +----------------------------------------------------------------------

    'template'               => [
        // 模板引擎类型 支持 php think 支持扩展
        'type'         => 'Think',
        // 模板路径
        'view_path'    => '',
        // 模板后缀
        'view_suffix'  => 'html',
        // 模板文件名分隔符
        'view_depr'    => DS,
        // 模板引擎普通标签开始标记
        'tpl_begin'    => '{',
        // 模板引擎普通标签结束标记
        'tpl_end'      => '}',
        // 标签库标签开始标记
        'taglib_begin' => '{',
        // 标签库标签结束标记
        'taglib_end'   => '}',
    ],

    // 视图输出字符串内容替换
    'view_replace_str'       => [
        '__STATUS__'=> '/static',
    ],
    // 默认跳转页面对应的模板文件
    'dispatch_success_tmpl'  => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',
    'dispatch_error_tmpl'    => THINK_PATH . 'tpl' . DS . 'dispatch_jump.tpl',

    // +----------------------------------------------------------------------
    // | 异常及错误设置
    // +----------------------------------------------------------------------

    // 异常页面的模板文件
    'exception_tmpl'         => THINK_PATH . 'tpl' . DS . 'think_exception.tpl',

    // 错误显示信息,非调试模式有效
    'error_message'          => '页面错误！请稍后再试～',
    // 显示错误信息
    'show_error_msg'         => false,
    // 异常处理handle类 留空使用 \think\exception\Handle
    'exception_handle'       => '',

    // +----------------------------------------------------------------------
    // | 日志设置
    // +----------------------------------------------------------------------

    'log'                    => [
        // 日志记录方式，内置 file socket 支持扩展
        'type'  => 'File',
        // 日志保存目录
        'path'  => LOG_PATH,
        // 日志记录级别
        'level' => [],
    ],

    // +----------------------------------------------------------------------
    // | Trace设置 开启 app_trace 后 有效
    // +----------------------------------------------------------------------
    'trace'                  => [
        // 内置Html Console 支持扩展
        'type' => 'Html',
    ],

    // +----------------------------------------------------------------------
    // | 缓存设置
    // +----------------------------------------------------------------------

    'cache'                  => [
        // 驱动方式
        'type'   => 'File',
        // 缓存保存目录
        'path'   => CACHE_PATH,
        // 缓存前缀
        'prefix' => '',
        // 缓存有效期 0表示永久缓存
        'expire' => 0,
    ],

    // +----------------------------------------------------------------------
    // | 会话设置
    // +----------------------------------------------------------------------

    'session'                => [
        'id'             => '',
        // SESSION_ID的提交变量,解决flash上传跨域
        'var_session_id' => '',
        // SESSION 前缀
        'prefix'         => 'think',
        // 驱动方式 支持redis memcache memcached
        'type'           => '',
        // 是否自动开启 SESSION
        'auto_start'     => true,
    ],

    // +----------------------------------------------------------------------
    // | Cookie设置
    // +----------------------------------------------------------------------
    'cookie'                 => [
        // cookie 名称前缀
        'prefix'    => '',
        // cookie 保存时间
        'expire'    => 0,
        // cookie 保存路径
        'path'      => '/',
        // cookie 有效域名
        'domain'    => '',
        //  cookie 启用安全传输
        'secure'    => false,
        // httponly设置
        'httponly'  => '',
        // 是否使用 setcookie
        'setcookie' => true,
    ],

    //分页配置
    'paginate'               => [
        'type'      => 'bootstrap',
        'var_page'  => 'page',
        'list_rows' => 15,
    ],
    
    //Jpush key
    'jpush'     => array(
        'title'  => '掌上民生' ,
        'key'    => 'a88697f86c48bd4c52cab063',
        'secret' => 'e4d8c32e8aa2a6da9b8b6b40',
    ) ,
    
    'MY_EXT'    =>  '-aaa',
    
    // +----------------------------------------------------------------------
    // | 支付宝支付参数
    // +----------------------------------------------------------------------
    'alipay'=>[
    		//应用ID,您的APPID。
    	'app_id' => "2018101661650776",
    	
    	//异步通知地址
    	'notify_url' => "http://47.96.12.115:82/pay/Ali/notify",
        
    	//商户私钥
        'merchant_private_key' => "MIIEowIBAAKCAQEAqk36iXaXDS9X8uNyGu+jCHtnvS4JSPHieyz0f/YA5PrrKJlILbq/FVOUEwrR+uPBdfROARuFy+PMmWbBiHxXigqcbjPjAF1SQNCOclDPYNMSfgN7SORANvVg2Rqy3DIAKSlvOzbkccy8J6r6Ev48o11hZMOdUac+eWiWT8XA84gd0AaqVjW6RHC/a5yoseWdUJgam8Tt/EAkPPG8XnpyYBe9XZTIVNccBWfAp9F4svEDywuiLIh51nWcvVYNfNaEwHoYeH6/b3n5jp1164kjpLYrKIB8Rm/VYAlGJHQPDCwZWHoNSAzMkI17v0eIhmJPSTbxER61HuQgcSduNYJ3yQIDAQABAoIBACCeyRNVdL9gxx7N9Ag2yGqSgJ9a1wpy0me3h6mO7ELv0OU43vtrXrhuBtnS47+DqoW2Ys6RgAI6wvDLHtzOvFHDkI0HQT7LoTqq9+3rjp1EMLGUZyPiHG6qd39+Pq4woZlAx353GDC3/341oKrMqb683y/WzCZcu3mzz7696SR8KQQ17vBb4AEzehPCXpM9OHdJXr1jU6+Yxwp4gCet7s2IImwRU+EtRuBzqT90iM0qNEKtymO/acyDd/+ZvFYMm55HoNrYTMM2+U4ZKHyzv4nPQ0tfcYQU+rDxe0VkEQmHAzD23IRr9h27RDAUSYTLfV3r6eUI3TJKbtfmBKylVlECgYEA0ejaXrG7SyH+a68TA4Z+DZYgf8LdPyqPga+XroHhpD1/cadnTLFM8qZZxEZmlCfHi5M1g0AdN6NhBIScX+j0CQ6C8nttxw8Zg0gDaooIHE1X3ysNbVqsVIYzaf6+augfbtvGAPcp2E+OTt8+P+sz9vb66fJlgNd3I6o2fauH/HUCgYEAz7LnymHgdJ5t79LhP713mMXUalDMFsQ4hkLiSmLTSLYP0rurK1wOowtuIIkwev4pN1lCp12bar5NN44JcD0CZ+U1lzQqnOanN4s3bgb46tcTok2KSe3ap5RuxCrH2Ae+A4vQWDHMCozfkSShYwOduy5BK7UCIHQN6PfeOiAtM4UCgYAvwjoE9400j/xyBbhewffmIXUtfGmYWJekGw54zuZG1xMrCbqQEXr/bmfYJ2hpZw+YqxquzSSYpxyIyZ9GlxsHAH08rGaSttXIL9dPIy6rOdG3XfVFHy88X/hMsoAilMOqFmjbiWDE7XzkZxijtGDzXMhD+Dmt8dgSjkStnRxojQKBgQC+s9fbnQ7IRQdQImIrcj2zikFE9LbPWI9Fx3ebMS4qHvBcX3AEudu+nKobOZvH8kHJzi1DGtGuqtifX80OvxXMa0pPcNiHg8iZWTwlzEUU6zHd3jBRPRF8uO8TMuyXUFSJUQCVWfOs5DgKPoXoMdPLn+a6yNIRFj4HYD8tQ4n9OQKBgGDQUiQqU0Ma7pSCfs964wiqjuTjjX7JkDdAqppQ3ezQp1LpFKjjme+2uen0zwIViKJpPp1oMPBVJ9ZJWVCeG/UjwYYCCrHzWlG9ia9/ZZBWZOeI+p+hG18fDQIOVJzdhtiIzPCLEprBthHo+w78hWWvY8lQoSf38AaOX+GXXGbo",

        //支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
        'alipay_public_key' => "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAsWVWc8GUKiG0L9BiUh7D43bFeEBVM45H8cCXvFiEXU+I8poRhdbmXGDZqCp1n1EAqKJ7yTyaWPjBbJ9k+XktH57LnIh1eUo/RkTpNlKM3jmSi30F0HirlZkff+NP2QyHfWB8fBwyvXgSGHDg0D43c69+ESmyi2y4mkmzi15MJRV/H2ZuK/TyTSa6Oo3ECFf6HiqocC6vElfUdDpLl/s7vsVNT3w5Mbxd+gup9hxPZ4XSRW7OTBqhwbvrYlXIRqvMFf+rUVJIKl58jvTliqqaTJe6Avb0F5V3uPgi5uuvxfIYD2ZO7dc+Bf6loJ6JU5XZyPBV8jfbaTTESW8o6PlUwQIDAQAB",
        
        //商户公钥
        'ali_private_key'   => 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAqk36iXaXDS9X8uNyGu+jCHtnvS4JSPHieyz0f/YA5PrrKJlILbq/FVOUEwrR+uPBdfROARuFy+PMmWbBiHxXigqcbjPjAF1SQNCOclDPYNMSfgN7SORANvVg2Rqy3DIAKSlvOzbkccy8J6r6Ev48o11hZMOdUac+eWiWT8XA84gd0AaqVjW6RHC/a5yoseWdUJgam8Tt/EAkPPG8XnpyYBe9XZTIVNccBWfAp9F4svEDywuiLIh51nWcvVYNfNaEwHoYeH6/b3n5jp1164kjpLYrKIB8Rm/VYAlGJHQPDCwZWHoNSAzMkI17v0eIhmJPSTbxER61HuQgcSduNYJ3yQIDAQAB',
    ],
    
    // +----------------------------------------------------------------------
    // | 微信支付参数
    // +----------------------------------------------------------------------
    'wxpay'=>[
        
        'appid' => 'wx350a1212664243a1',
        'mch_id'=> '1483140212',
        'notify_url' => 'http://47.96.12.115:82/pay/Wx/notify',
        'key'  => 'YDKJ20181021ydkjYDKJ20181021ydkj',
    ],   
];
