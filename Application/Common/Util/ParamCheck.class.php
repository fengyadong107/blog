<?php
namespace Common\Util;

use Think\Log;

class ParamCheck{
	public static function checkNULL($key, $value) {
		if (is_null($value)) {
			Log("param [$key] cannot be null", 'DEBUG');
			E("param [$key] cannot be null", C('PARAM_ERROR'));
		}
	}

	public static function checkNumber($key, $value, $min = null, $max = null) {
		if (!is_numeric($value)) {
			Log("param [ $key ] is not a number [ $value ]", 'DEBUG');
			E("param [ $key ] is not a number [ $value ]", C('PARAM_ERROR'));
		}
		if (null !== $min && $value < $min) {
			Log("param [ $key ] is smaller than $min [ $value ]", 'DEBUG');
			E("param [ $key ] is smaller than $min [ $value ]", C('PARAM_ERROR'));
		}
		if (null !== $max && $value > $max) {
			Log("param [ $key ] is bigger than $max [ $value ]", 'DEBUG');
			E("param [ $key ] is bigger than $max [ $value ]", C('PARAM_ERROR'));
		}
	}

	public static function checkInt($key, $value, $min = null, $max = null) {
		if (!is_numeric($value)) {
			Log("param [ $key ] is not a number [ $value ]", 'DEBUG');
			E("param [ $key ] is not a number [ $value ]", C('PARAM_ERROR'));
		}
		if (strval(intval($value)) !== strval($value)) {
			Log("param [ $key ] is not an integer [ $value ]", 'DEBUG');
			E("param [ $key ] is not an integer [ $value ]", C('PARAM_ERROR'));
		}
		if (null !== $min && $value < $min) {
			Log("param [ $key ] is smaller than $min [ $value ]", 'DEBUG');
			E("param [ $key ] is smaller than $min [ $value ]", C('PARAM_ERROR'));
		}
		if (null !== $max && $value > $max) {
			Log("param [ $key ] is bigger than $max [ $value ]", 'DEBUG');
			E("param [ $key ] is bigger than $max [ $value ]", C('PARAM_ERROR'));
		}
	}

	public static function checkString($key, $value, $min = null, $max = null) {
		if (!is_string($value)) {
			Log::record("param [ $key ] is not a string [ $value ]", 'DEBUG');
			E("param [ $key ] is not a string [ $value ]", C('PARAM_ERROR'));
		}
		if (null !== $min && strlen($value) < $min) {
			Log::record("param [ $key ] is shorter than $min [ $value ]", 'DEBUG');
			E("param [ $key ] is shorter than $min [ $value ]", C('PARAM_ERROR'));
		}
		if (null !== $max && strlen($value) > $max) {
			Log::record("param [ $key ] is longer than $max [ $value ]", 'DEBUG');
			E("param [ $key ] is longer than $max [ $value ]", C('PARAM_ERROR'));
		}
	}

	public static function checkStringArray($array, $min = null, $max = null) {
		foreach ($array as $k => $v) {
			self::checkString($k, $v, $min, $max);
		}
	}

	public static function checkUrl($key, $value) {
		/*
		$pattern = "/^(http|https):\/\//";
		$reg_pattern = array ('options' => array ('regexp' => $pattern ) );
		if (false === filter_var ( $value, FILTER_VALIDATE_REGEXP, $reg_pattern ) ||
			false === filter_var ( $value, FILTER_VALIDATE_URL )) {
			throw new Exception ( "param [ $key ] is not a valid url [ $value ]" );
		}
		*/

		$pattern = '/^(http|https):\/\//i';
		$reg_pattern = array('options' => array('regexp' => $pattern));
		if (false === filter_var($value, FILTER_VALIDATE_REGEXP, $reg_pattern)) {
			Log::record("param [ $key ] is not a valid url [ $value ]", 'DEBUG');
			E("param [ $key ] is not a valid url [ $value ]", C('PARAM_ERROR'));
		}
	}

	public static function checkArray($key, $value, $arrCheck) {
		if (!is_array($value)) {
			Log::record("param [ $key ] is not an array [" . print_r($value, true) . " ]", 'DEBUG');
			E('', C('PARAM_ERROR'));
		}
		foreach ($arrCheck as $v) {
			if (!in_array($v, $value)) {
				Log::record("param [ $key ] contains no key/value [ $v ]", 'DEBUG');
				E('', C('PARAM_ERROR'));
			}
		}
	}

	public static function checkArrayKey($needCheck, $arrChecked) {
		if (!is_array($arrChecked)) {
			Log::record('param the param to be detected is not an array', 'DEBUG');
			E('', C('PARAM_ERROR'));
		}

		if (is_array($needCheck)) {
			foreach ($needCheck as $value) {
				if (!array_key_exists($value, $arrChecked)) {
					Log::record("param $value does not exist in the array", 'DEBUG');
					E('', C('PARAM_ERROR'));
				}
			}
		} else {
			if (!array_key_exists($needCheck, $arrChecked)) {
				Log::record("param $needCheck does not exist in the array", 'DEBUG');
				E('', C('PARAM_ERROR'));
			}
		}
	}

	public static function checkArrayNull($arrChecked) {
		if (!is_array($arrChecked)) {
			Log::record('param the param to be detected is not an array', 'DEBUG');
			E('', C('PARAM_ERROR'));
		}
		if (0 >= count($arrChecked)) {
			E('', C('PARAM_ERROR'));
		}
	}
}