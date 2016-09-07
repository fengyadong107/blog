<?php
namespace Common\Util;

use Think\Log;

class Http_Response{
	private static $_headers = array();
	private static $_outputs = array();
	private static $_cookies = array();
	private static $_rawData = null;
	private static $_exception = null;
	private static $_error = null;

	public static function location($url) {
		header('location:' . $url);
	}

	public static function contextType($strType = 'text/html', $strCharset = 'UTF-8'/*UTF8DIFF*/) {
		header('Content-Type:' . $strType . '; charset=' . $strCharset);
	}

	public static function setHeader($header) {
		self::$_headers [] = $header;
	}

	public static function setCookie($key, $value, $expires = null, $path = '/', $domain = null, $secure = false, $httponly = false) {
		self::$_cookies [] = array(
			$key,
			$value,
			$expires,
			$path,
			$domain,
			$secure,
			$httponly,
		);
	}

	public static function delCookie($key, $value = '', $expires = 1, $path = '/', $domain = null, $secure = false, $httponly = false) {
		self::$_cookies [] = array(
			$key,
			$value,
			$expires,
			$path,
			$domain,
			$secure,
			$httponly,
		);
	}

	public static function set($key, $value = null) {
		self::$_outputs [$key] = $value;
	}

	public static function setArray($array) {
		if (!$array) {
			return;
		}
		foreach ($array as $key => $value) {
			self::$_outputs [$key] = $value;
		}
	}

	public static function setRaw($data) {
		self::$_rawData = $data;
	}

	public static function setException($ex) {
		self::$_exception = $ex;
	}

	public static function setError($err) {
		self::$_error = $err;
	}

	public static function redirect($url) {
		self::setHeader('Location: ' . $url);
		self::sendHeaders();
		exit ();
	}

	public static function send($of = 'json') {
		$data = self::_formatResponse($of);
		self::sendHeaders();
		//获取缓冲数据
		// $ob = ini_get ( 'output_buffering' );
		// if ($ob && strtolower ( $ob ) !== 'off') {
		//     $str = ob_get_clean ();
		//     //忽略前后空白
		//     $data = trim ( $str ) . $data;
		// }
		if ($data) {
			//tiancheng123
			$processTime = microtime(true) - $GLOBALS['_beginTime'];
			//$prefix = '[process_time:'.$processTime.']';
			Log::addBasic('process_time', $processTime);
			Log::addBasic('http_code', C('LOG_HTTP_CODE'));
			Log::record($data, 'ALERT');
			echo $data;
		}
		exit;
	}

	//add by tiancheng123
	public static function asyncSend($of) {
		$data = self::_formatResponse($of);
		self::sendHeaders();
		//获取缓冲数据
		$ob = ini_get('output_buffering');
		if ($ob && strtolower($ob) !== 'off') {
			$str = ob_get_clean();
			//忽略前后空白
			$data = trim($str) . $data;
		}
		if ($data) {
			Log::record(json_encode($data), 'DEBUG');
			echo $data;
		}
		if (function_exists("fastcgi_finish_request")) {
			fastcgi_finish_request();
		}
	}

	private static function _buildContentType($of) {
		switch ($of) {
			case 'json' :
				self::$_headers = array_merge(array('Content-Type: application/json; charset=UTF-8'), self::$_headers);
				break;
			case 'html' :
				self::$_headers = array_merge(array('Content-Type: text/html; charset=UTF-8'), self::$_headers);
				break;
			default :
				self::$_headers = array_merge(array('Content-Type: text/plain; charset=UTF-8'), self::$_headers);
		}
	}

	private static function _getResult() {
		if (self::$_exception || self::$_error) {
			$result = self::$_outputs;
			$result ["request_id"] = C('log_id');
		} else {
			if (!empty(self::$_outputs)) {
				$result = self::$_outputs;
				$result ["request_id"] = C('log_id');
			} else {
				$result = array("request_id" => C('log_id'));
			}
		}

		return $result;
	}

	private static function _formatResponse($of) {
		$result = self::_getResult();
		self::_buildContentType($of);
		if (self::$_rawData) {
			return self::$_rawData;
		} else {
			if ($of == 'json') {
				return json_encode($result);
			} else {
				return print_r($result, true);
			}
		}
	}

	private static function sendHeaders() {
		$headers = self::$_headers;
		if (self::$_cookies) {
			//echo cookie
			foreach (self::$_cookies as $_cookie) {
				call_user_func_array('setcookie', $_cookie);
			}
		}
		if ($headers) {
			//echo header
			foreach ($headers as $header) {
				header($header);
			}
		}
	}
}