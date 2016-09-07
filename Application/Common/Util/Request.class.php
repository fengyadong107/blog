<?php
namespace Common\Util;

class Request{
	private static $_errno;
	private static $_error;

	public static function get($url, $params = null) {
		return self::_send($url, $params, 'get');
	}

	public static function post($url, $params = null) {
		return self::_send($url, $params, 'post');
	}

	public static function getData($url, $param = null, $contentType = 'application/x-www-form-urlencoded', $headers = array()) {
		return self::sendData($url, $param, 'get', $contentType, $headers);
	}

	public static function postData($url, $param = null, $contentType = 'application/x-www-form-urlencoded', $headers = array()) {
		return self::sendData($url, $param, 'post', $contentType, $headers);
	}

	public static function sendData($url, $params = null, $method = 'get', $contentType = 'application/x-www-form-urlencoded', $headers = array()) {
		$headers[] = 'Content-Type: ' . $contentType;

		$options = array(
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_TIMEOUT        => 60,
			CURLOPT_HTTPHEADER     => $headers,
		);

		if ($params) {
			if ('get' == $method) {
				if (strpos($url, '?')) {
					$url .= '&' . http_build_query($params);
				} else {
					$url .= '?' . http_build_query($params);
				}
			} else {
				if ('application/x-www-form-urlencoded' == $contentType && is_array($params)) {
					$params = http_build_query($params);
				}
				$options[CURLOPT_POSTFIELDS] = $params;
			}
		}

		$ch = curl_init($url);
		curl_setopt_array($ch, $options);
		$res = curl_exec($ch);
		self::$_errno = curl_errno($ch);

		if (self::$_errno) {
			self::$_error = curl_error($ch);
			curl_close($ch);

			return false;
		} else {
			curl_close($ch);

			return $res;
		}
	}

	public static function getErrno() {
		return self::$_errno;
	}

	public static function getError() {
		return self::$_error;
	}

	private static function _send($url, $params = null, $method = 'get', $contentType = 'application/x-www-form-urlencoded') {
		$options = array(
			CURLOPT_AUTOREFERER    => true,
			CURLOPT_FOLLOWLOCATION => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_CONNECTTIMEOUT => 10,
			CURLOPT_HTTPHEADER     => array(
				'Content-Type: ' . $contentType,
			),
			CURLOPT_TIMEOUT        => 60,
		);

		if ($params) {
			if ('get' == $method) {
				$url = $url . '?' . http_build_query($params);
			} else {
				if ('application/x-www-form-urlencoded' == $contentType && is_array($params)) {
					$params = http_build_query($params);
				}
				$options[CURLOPT_POSTFIELDS] = $params;
			}
		}

		for ($i = 0; $i < 3; $i++) {
			$ch = curl_init($url);
			curl_setopt_array($ch, $options);
			$res = curl_exec($ch);

			if (!curl_errno($ch) && 200 == curl_getinfo($ch, CURLINFO_HTTP_CODE)) {
				curl_close($ch);

				return $res;
			} else {
				self::$_error = 'curl_error:' . curl_error($ch) . ' --- curl_httpcode:' . curl_getinfo($ch, CURLINFO_HTTP_CODE) . "\n";
			}
			curl_close($ch);
		}

		return false;
	}
}
