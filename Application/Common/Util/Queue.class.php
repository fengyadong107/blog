<?php
namespace Common\Util;

use Think\Cache\Driver\Redis;

class Queue{
	protected static $redis = null;

	public static function add($key, $val) {
		return self::$redis->sAdd($key, $val);
	}

	public static function pop($key) {
		return self::$redis->sPop($key);
	}

	public static function redisInstance() {
		if (is_null(self::$redis)) {
			self::$redis = new Redis();
		}

		return self::$redis;
	}

	public static function getQueueKey() {
		return C('WK_SSP_COUNT_QUEUE_KEY');
	}

	/**
	 * 生成20160420_1_1（日期，adx,强制更新）
	 * [createQueueVal description]
	 * @param $date
	 * @param $adx
	 * @param $force
	 * @return string
	 */
	public static function createQueueVal($date, $adx, $force) {
		return $date . '_' . $adx . '_' . $force;
	}

	public static function splitQueueVal($val) {
		return explode('_', $val);
	}

}