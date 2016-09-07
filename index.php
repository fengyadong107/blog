<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用入口文件
// 检测PHP环境
if (version_compare(PHP_VERSION, '5.3.0', '<'))
	die('require PHP > 5.3.0 !');

//关闭PHP报错
error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG', true);

// 定义应用目录
define('APP_PATH', './Application/');

//cross-domain add by tiancheng123
header('Access-Control-Allow-Origin: *');

define('GZIP_ENABLE', function_exists('ob_gzhandler'));
ob_start(GZIP_ENABLE ? 'ob_gzhandler' : null);

//设置全局cookie http only属性
ini_set('session.cookie_httponly', 0);

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';

// 亲^_^ 后面不需要任何代码了 就是如此简单
