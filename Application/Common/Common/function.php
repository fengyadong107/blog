<?php

function gen_log_id() {
	$log_id = rand(1, 10000000000);
	C('log_id', $log_id);
}

function mygzdecode($data) {
	$flags = ord(substr($data, 3, 1));
	$headerlen = 10;
	$extralen = 0;
	$filenamelen = 0;
	if ($flags & 4) {
		$extralen = unpack('v', substr($data, 10, 2));
		$extralen = $extralen[1];
		$headerlen += 2 + $extralen;
	}
	if ($flags & 8) // Filename
		$headerlen = strpos($data, chr(0), $headerlen) + 1;
	if ($flags & 16) // Comment
		$headerlen = strpos($data, chr(0), $headerlen) + 1;
	if ($flags & 2) // CRC at end of file
		$headerlen += 2;
	$unpacked = @gzinflate(substr($data, $headerlen));
	if ($unpacked === false)
		return false;

	return $unpacked;
}

function format_cache_key_with_page($type, $value, $page, $pageSize) {
	return $type . '_' . $value . '_' . $page . '_' . $pageSize;
}

function format_cache_key_without_page($type, $value) {
	return $type . '_' . $value;
}

function matchPattern($str, $pattern) {
	if ($str != null) {
		$matchResult = preg_match($pattern, $str);
		if (is_numeric($matchResult) && $matchResult > 0) {
			return true;
		}
	}

	return false;
}

function verify_channel($channelId) {
	if (!matchPattern($channelId, C('CHANNEL_PATTERN'))) {
		E('', C('ILLEGAL_CHANNEL'));
	}
}

function format_log($data) {
	$ret = '';
	if (is_array($data)) {
		foreach ($data as $k => $v) {
			$ret .= '[' . $k . ':' . $v . ']';
		}
	}

	return $ret;
}

/*
* @param array $array 要排序的数组
* @param string $keyname 排序的键
* @param int $dir 排序方向
*
* @return array 排序后的数组
*/
function sortByCol($array, $keyname, $dir = SORT_DESC) {
	return sortByMultiCols($array, array($keyname => $dir));
}

/**
 * 将一个二维数组按照多个列进行排序，类似 SQL 语句中的 ORDER BY
 * 用法：
 * @code php
 * $rows = Helper_Array::sortByMultiCols($rows, array(
 *           'parent' => SORT_ASC,
 *           'name' => SORT_DESC,
 * ));
 * @endcode
 * @param array $rowset 要排序的数组
 * @param array $args   排序的键
 * @return array 排序后的数组
 */
function sortByMultiCols($rowset, $args) {
	$sortArray = array();
	$sortRule = '';
	foreach ($args as $sortField => $sortDir) {
		foreach ($rowset as $offset => $row) {
			$sortArray[$sortField][$offset] = $row[$sortField];
		}
		$sortRule .= '$sortArray[\'' . $sortField . '\'], ' . $sortDir . ', ';
	}
	if (empty($sortArray) || empty($sortRule)) {
		return $rowset;
	}
	eval('array_multisort(' . $sortRule . '$rowset);');

	return $rowset;
}

/**
 * 字符串截取，支持中文和其他编码
 * @static
 * @access public
 * @param string $str     需要转换的字符串
 * @param string $start   开始位置
 * @param string $length  截取长度
 * @param string $charset 编码格式
 * @param string $suffix  截断显示字符
 * @return string
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = true) {
	if (function_exists("mb_substr"))
		$slice = mb_substr($str, $start, $length, $charset); elseif (function_exists('iconv_substr')) {
		$slice = iconv_substr($str, $start, $length, $charset);
		if (false === $slice) {
			$slice = '';
		}
	} else {
		$re['utf-8'] = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|[\xe0-\xef][\x80-\xbf]{2}|[\xf0-\xff][\x80-\xbf]{3}/";
		$re['gb2312'] = "/[\x01-\x7f]|[\xb0-\xf7][\xa0-\xfe]/";
		$re['gbk'] = "/[\x01-\x7f]|[\x81-\xfe][\x40-\xfe]/";
		$re['big5'] = "/[\x01-\x7f]|[\x81-\xfe]([\x40-\x7e]|\xa1-\xfe])/";
		preg_match_all($re[$charset], $str, $match);
		$slice = join("", array_slice($match[0], $start, $length));
	}

	return $suffix ? $slice . '...' : $slice;
}

/**
 * 系统加密方法
 * @param string $data   要加密的字符串
 * @param string $key    加密密钥
 * @param int    $expire 过期时间 单位 秒
 * @return string
 */
function think_encrypt($data, $key = '', $expire = 0) {
	$key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
	$data = base64_encode($data);
	$x = 0;
	$len = strlen($data);
	$l = strlen($key);
	$char = '';

	for ($i = 0; $i < $len; $i++) {
		if ($x == $l)
			$x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}

	$str = sprintf('%010d', $expire ? $expire + time() : 0);

	for ($i = 0; $i < $len; $i++) {
		$str .= chr(ord(substr($data, $i, 1)) + (ord(substr($char, $i, 1))) % 256);
	}

	return str_replace(array(
		'+',
		'/',
		'=',
	), array(
		'-',
		'_',
		'',
	), base64_encode($str));
}

/**
 * 系统解密方法
 * @param  string $data 要解密的字符串 （必须是think_encrypt方法加密的字符串）
 * @param  string $key  加密密钥
 * @return string
 */
function think_decrypt($data, $key = '') {
	$key = md5(empty($key) ? C('DATA_AUTH_KEY') : $key);
	$data = str_replace(array(
		'-',
		'_',
	), array(
		'+',
		'/',
	), $data);
	$mod4 = strlen($data) % 4;
	if ($mod4) {
		$data .= substr('====', $mod4);
	}
	$data = base64_decode($data);
	$expire = substr($data, 0, 10);
	$data = substr($data, 10);

	if ($expire > 0 && $expire < time()) {
		return '';
	}
	$x = 0;
	$len = strlen($data);
	$l = strlen($key);
	$char = $str = '';

	for ($i = 0; $i < $len; $i++) {
		if ($x == $l)
			$x = 0;
		$char .= substr($key, $x, 1);
		$x++;
	}

	for ($i = 0; $i < $len; $i++) {
		if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
			$str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
		} else {
			$str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
		}
	}

	return base64_decode($str);
}

/**
 * 对查询结果集进行排序
 * @access public
 * @param array  $list   查询结果
 * @param string $field  排序的字段名
 * @param array  $sortby 排序类型
 *                       asc正向排序 desc逆向排序 nat自然排序
 * @return array
 */
function list_sort_by($list, $field, $sortby = 'asc') {
	if (is_array($list)) {
		$refer = $resultSet = array();
		foreach ($list as $i => $data)
			$refer[$i] = &$data[$field];
		switch ($sortby) {
			case 'asc': // 正向排序
				asort($refer);
				break;
			case 'desc':// 逆向排序
				arsort($refer);
				break;
			case 'nat': // 自然排序
				natcasesort($refer);
				break;
		}
		foreach ($refer as $key => $val)
			$resultSet[] = &$list[$key];

		return $resultSet;
	}

	return false;
}

/**
 * 把返回的数据集转换成Tree
 * @param array  $list  要转换的数据集
 * @param string $pid   parent标记字段
 * @param string $level level标记字段
 * @return array
 */
function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
	// 创建Tree
	$tree = array();
	if (is_array($list)) {
		// 创建基于主键的数组引用
		$refer = array();
		foreach ($list as $key => $data) {
			$refer[$data[$pk]] =& $list[$key];
		}
		foreach ($list as $key => $data) {
			// 判断是否存在parent
			$parentId = $data[$pid];
			if ($root == $parentId) {
				$tree[] =& $list[$key];
			} else {
				if (isset($refer[$parentId])) {
					$parent =& $refer[$parentId];
					$parent[$child][] =& $list[$key];
				}
			}
		}
	}

	return $tree;
}

/**
 * 将list_to_tree的树还原成列表
 * @param  array  $tree  原来的树
 * @param  string $child 孩子节点的键
 * @param  string $order 排序显示的键，一般是主键 升序排列
 * @param  array  $list  过渡用的中间数组，
 * @return array        返回排过序的列表数组
 */
function tree_to_list($tree, $child = '_child', $order = 'id', &$list = array()) {
	if (is_array($tree)) {
		$refer = array();
		foreach ($tree as $key => $value) {
			$reffer = $value;
			if (isset($reffer[$child])) {
				unset($reffer[$child]);
				tree_to_list($value[$child], $child, $order, $list);
			}
			$list[] = $reffer;
		}
		$list = list_sort_by($list, $order, $sortby = 'asc');
	}

	return $list;
}

/**
 * 格式化字节大小
 * @param  number $size      字节数
 * @param  string $delimiter 数字和单位分隔符
 * @return string            格式化后的带单位的大小
 */
function format_bytes($size, $delimiter = '') {
	$units = array(
		'B',
		'KB',
		'MB',
		'GB',
		'TB',
		'PB',
	);
	for ($i = 0; $size >= 1024 && $i < 5; $i++)
		$size /= 1024;

	return round($size, 2) . $delimiter . $units[$i];
}

/**
 * 处理插件钩子
 * @param string $hook   钩子名称
 * @param mixed  $params 传入参数
 * @return void
 */
function hook($hook, $params = array()) {
	\Think\Hook::listen($hook, $params);
}

/**
 * 获取插件类的类名
 * @param strng $name 插件名
 */
function get_addon_class($name) {
	$class = "Addons\\{$name}\\{$name}Addon";

	return $class;
}

/**
 * 获取插件类的配置文件数组
 * @param string $name 插件名
 */
function get_addon_config($name) {
	$class = get_addon_class($name);
	if (class_exists($class)) {
		$addon = new $class();

		return $addon->getConfig();
	} else {
		return array();
	}
}

/**
 * 插件显示内容里生成访问插件的url
 * @param string $url   url
 * @param array  $param 参数
 */
function addons_url($url, $param = array()) {
	$url = parse_url($url);
	$case = C('URL_CASE_INSENSITIVE');
	$addons = $case ? parse_name($url['scheme']) : $url['scheme'];
	$controller = $case ? parse_name($url['host']) : $url['host'];
	$action = trim($case ? strtolower($url['path']) : $url['path'], '/');

	/* 解析URL带的参数 */
	if (isset($url['query'])) {
		parse_str($url['query'], $query);
		$param = array_merge($query, $param);
	}

	/* 基础参数 */
	$params = array(
		'_addons'     => $addons,
		'_controller' => $controller,
		'_action'     => $action,
	);
	$params = array_merge($params, $param); //添加额外参数

	return U('Addons/execute', $params);
}

/**
 * 时间戳格式化
 * @param int $time
 * @return string 完整的时间显示
 */
function time_format($time = null, $format = 'Y-m-d H:i') {
	$time = $time === null ? NOW_TIME : intval($time);

	return date($format, $time);
}

function http_service_accessor($url, $post = false, $param = null) {
	$times = 0;
	do {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if (true === $post) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}
		$result = curl_exec($ch);
		if (false !== $result) {
			$result = json_decode($result, true);

			return $result;
		} else {
			++$times;
			if (3 > $times) {
				break;
			}
			curl_close($ch);
			continue;
		}
	} while (1);

	return false;
}

function http_service_accessor2($url, $post = false, $param = null) {
	$times = 0;
	do {
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-type: application/json; charset=utf-8"));
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_TIMEOUT, 60);
		if (true === $post) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
		}
		$result = curl_exec($ch);
		if (false !== $result) {
			$result = json_decode($result, true);

			return $result;
		} else {
			++$times;
			if (3 > $times) {
				break;
			}
			curl_close($ch);
			continue;
		}
	} while (1);

	return false;
}

function think_send_mail($to, $name, $subject = '', $body = '', $attachment = null) {

	$config = C('THINK_EMAIL');

	Vendor('PHPMailer.PHPMailerAutoload');   //从PHPMailer目录导class.phpmailer.php类文件

	$mail = new PHPMailer(); //PHPMailer对象

	$mail->CharSet = 'UTF-8'; //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码

	$mail->IsSMTP();  // 设定使用SMTP服务

	$mail->SMTPDebug = 0;                     // 关闭SMTP调试功能

	// 1 = errors and messages

	// 2 = messages only

	$mail->SMTPAuth = true;                  // 启用 SMTP 验证功能

	$mail->SMTPSecure = 'ssl';                 // 使用安全协议

	$mail->Host = $config['SMTP_HOST'];  // SMTP 服务器

	$mail->Port = $config['SMTP_PORT'];  // SMTP服务器的端口号

	$mail->Username = $config['SMTP_USER'];  // SMTP服务器用户名

	$mail->Password = $config['SMTP_PASS'];  // SMTP服务器密码

	$mail->SetFrom($config['FROM_EMAIL'], $config['FROM_NAME']);

	$replyEmail = $config['REPLY_EMAIL'] ? $config['REPLY_EMAIL'] : $config['FROM_EMAIL'];

	$replyName = $config['REPLY_NAME'] ? $config['REPLY_NAME'] : $config['FROM_NAME'];

	$mail->AddReplyTo($replyEmail, $replyName);

	$mail->Subject = $subject;

	$mail->AltBody = "为了查看该邮件，请切换到支持 HTML 的邮件客户端";

	$mail->MsgHTML($body);

	$mail->AddAddress($to, $name);

	if (is_array($attachment)) { // 添加附件

		foreach ($attachment as $file) {

			is_file($file) && $mail->AddAttachment($file);

		}

	}

	return $mail->Send() ? true : $mail->ErrorInfo;

}