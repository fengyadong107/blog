<?php

namespace Common\Libs;

use Think\Log;

class SmartyWrapper{
	public static $view = null;

	public static function getView() {
		if (empty(self::$view)) {
			vendor('Smarty.Smarty#class');
			self::$view = new \Smarty();

//            $config = array(
//                'template_dir' => C('template_dir'),
//                'compile_dir' => CACHE_PATH,
//                'cache_dir' => TEMP_PATH,
//                'left_delimiter' => C('left_delimiter'),
//                'right_delimiter' => C('right_delimiter'),
//            );
			$config = C('TMPL_ENGINE_CONFIG');
			self::setOptions($config);
		}

		return self::$view;
	}

	public static function setOptions($arrConfig) {
		$objSmarty = self::getView();
		if (isset($arrConfig['template_dir']))
			$objSmarty->template_dir = $arrConfig['template_dir'];
		if (isset($arrConfig['compile_dir']))
			$objSmarty->compile_dir = $arrConfig['compile_dir'];
		if (isset($arrConfig['config_dir']))
			$objSmarty->config_dir = $arrConfig['config_dir'];
		if (isset($arrConfig['cache_dir']))
			$objSmarty->cache_dir = $arrConfig['cache_dir'];
		if (isset($arrConfig['plugins_dir']))
			$objSmarty->plugins_dir = $arrConfig['plugins_dir'];
		if (isset($arrConfig['left_delimiter']))
			$objSmarty->left_delimiter = $arrConfig['left_delimiter'];
		if (isset($arrConfig['right_delimiter']))
			$objSmarty->right_delimiter = $arrConfig['right_delimiter'];
		if (isset($arrConfig['compile_check']))
			$objSmarty->compile_check = $arrConfig['compile_check'];
	}

	public static function assign($key, $value) {
		self::getView()->assign($key, $value);
	}

	public static function display($template) {
		if (isset($_GET['debug']) && 1 == $_GET['debug']) {
			header('Content-Type:text/html; charset=utf-8');
			self::getView()->dumpTplVars();
			$processTime = microtime(true) - $GLOBALS['_beginTime'];
			Log::addBasic('process_time', $processTime);
			Log::record('', 'ALERT');
			die();
		}
		self::getView()->display($template);
		$processTime = microtime(true) - $GLOBALS['_beginTime'];
		Log::addBasic('process_time', $processTime);
		Log::record('', 'ALERT');
		exit ();
	}
}