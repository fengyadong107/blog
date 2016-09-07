<?php
$dbConf = array(
	/* 数据库设置 */
	// 数据库类型
	'DB_TYPE'             => 'mysqli',
	// 服务器地址
	'DB_HOST'             => '127.0.0.1',
	// 数据库名
	'DB_NAME'             => 'data_server',
	// 用户名
	'DB_USER'             => 'root',
	// 密码
	'DB_PWD'              => 'root',
	// 端口
	'DB_PORT'             => '3306',
	// 数据库表前缀
	'DB_PREFIX'           => 'tb_',
	// 是否进行字段类型检查
	'DB_FIELDTYPE_CHECK'  => false,
	// 启用字段缓存
	'DB_FIELDS_CACHE'     => false,
	// 数据库编码默认采用utf8
	'DB_CHARSET'          => 'utf8',
	// 数据库部署方式:0 集中式(单一服务器),1 分布式(主从服务器)
	'DB_DEPLOY_TYPE'      => 0,
	// 数据库读写是否分离 主从式有效
	'DB_RW_SEPARATE'      => false,
	// 读写分离后 主服务器数量
	'DB_MASTER_NUM'       => 1,
	// 指定从服务器序号
	'DB_SLAVE_NO'         => '',
	// 数据库查询的SQL创建缓存
	'DB_SQL_BUILD_CACHE'  => false,
	// SQL缓存队列的缓存方式 支持 file xcache和apc
	'DB_SQL_BUILD_QUEUE'  => 'file',
	// SQL缓存的队列长度
	'DB_SQL_BUILD_LENGTH' => 20,
	// SQL执行日志记录
	'DB_SQL_LOG'          => false,
	// 数据库写入数据自动参数绑定
	'DB_BIND_PARAM'       => false,
);
$arrErrCode = array(
	'INTERNAL_ERROR'        => 1,
	'PARAM_ERROR'           => 2,
	'API_UNSUPPORTED'       => 3,
	'REQUEST_DUP'           => 4,
	'ILLEGAL_CHANNEL'       => 5,
	'USER_NOT_LOGIN'        => 6,
	'CAPTCHA_ERROR'         => 7,
	'USER_NOT_AUTH'         => 8,
	'REQUEST_ERROR'         => 9,
	'UID_NOT_EXIST'         => 10,
	'FILE_FORMAT_ERROR'     => 11,
	'FILE_UPLOAD_ERROR'     => 12,
	'PASSWORD_ERROR'        => 13,
	'RESOURCE_NOT_EXIST'    => 14,
	'UPLOAD_ERROR'          => 15,
	'GAME_NOT_EXIST'        => 16,
	'QUERY_STRING_TOO_LONG' => 17,
);
$arrError = array(
	'ERROR_MESSAGE' => array(
		$arrErrCode['API_UNSUPPORTED']       => 'Unsupported api',
		$arrErrCode['PARAM_ERROR']           => 'param error',
		$arrErrCode['REQUEST_DUP']           => 'dup request',
		$arrErrCode['ILLEGAL_CHANNEL']       => 'illegal request',
		$arrErrCode['USER_NOT_LOGIN']        => 'user not login',
		$arrErrCode['CAPTCHA_ERROR']         => 'captcha error',
		$arrErrCode['USER_NOT_AUTH']         => 'user not authorized',
		$arrErrCode['UID_NOT_EXIST']         => 'user not exist',
		$arrErrCode['FILE_FORMAT_ERROR']     => 'file format error',
		$arrErrCode['FILE_UPLOAD_ERROR']     => 'file upload error',
		$arrErrCode['PASSWORD_ERROR']        => 'password error',
		$arrErrCode['RESOURCE_NOT_EXIST']    => 'resource not exist',
		$arrErrCode['GAME_NOT_EXIST']        => 'game not exist',
		$arrErrCode['INTERNAL_ERROR']        => 'internal server error',
		$arrErrCode['QUERY_STRING_TOO_LONG'] => 'query string too long',
	),
	'HTTP_CODE'     => array(
		$arrErrCode['INTERNAL_ERROR']        => 500,
		$arrErrCode['API_UNSUPPORTED']       => 403,
		$arrErrCode['PARAM_ERROR']           => 403,
		$arrErrCode['REQUEST_DUP']           => 403,
		$arrErrCode['ILLEGAL_CHANNEL']       => 403,
		$arrErrCode['USER_NOT_LOGIN']        => 403,
		$arrErrCode['CAPTCHA_ERROR']         => 403,
		$arrErrCode['USER_NOT_AUTH']         => 403,
		$arrErrCode['REQUEST_ERROR']         => 403,
		$arrErrCode['FILE_FORMAT_ERROR']     => 403,
		$arrErrCode['FILE_UPLOAD_ERROR']     => 403,
		$arrErrCode['PASSWORD_ERROR']        => 403,
		$arrErrCode['RESOURCE_NOT_EXIST']    => 403,
		$arrErrCode['UPLOAD_ERROR']          => 403,
		$arrErrCode['GAME_NOT_EXIST']        => 403,
		$arrErrCode['QUERY_STRING_TOO_LONG'] => 403,
	),
);


$appConf = array(
	'IS_WEB'               => 0,
	'LOG_HTTP_CODE'        => 200,

	/* 模块相关配置 */
	'DEFAULT_MODULE'       => 'Msite',
	'MODULE_DENY_LIST'     => array(
		'Common',
		'User',
	),

	/* URL配置 */
	//默认false 表示URL区分大小写 true则表示不区分大小写
	'URL_CASE_INSENSITIVE' => true,
	//URL模式
	'URL_MODEL'            => 0,
	// PATHINFO URL参数变量
	'VAR_URL_PARAMS'       => '',
	//PATHINFO URL分割符
	'URL_PATHINFO_DEPR'    => '/',

	/*缓存相关配置 */
	//Redis,Memcache,File
	'DATA_CACHE_TYPE'      => 'Redis',
	'REDIS_HOST'           => '127.0.0.1',
	'REDIS_PORT'           => 6379,
	'DATA_CACHE_TIMEOUT'   => 3,
	//24*60*60
	'DATA_CACHE_TIME'      => 1,
	'DATA_CACHE_PREFIX'    => 'HJ_',
	'DB_NO_DATA'           => -1,
	'CACHE_TO_DB_MOD'      => 3,
);


return array_merge($dbConf, $arrErrCode, $arrError, $appConf);
