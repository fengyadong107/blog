<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2013 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;
defined('THINK_PATH') or exit();

/**
 * Redis缓存驱动
 * 要求安装phpredis扩展：https://github.com/nicolasff/phpredis
 */
class Redis extends Cache {
	 /**
	 * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    public function __construct($options=array()) {
        if ( !extension_loaded('redis') ) {
            E(L('_NOT_SUPPERT_').':redis');
        }

        //tiancheng
        //覆盖如下几项，但并不影响过期时间等设置
        $options['host'] = C('REDIS_HOST') ? C('REDIS_HOST') : '127.0.0.1';
        $options['port'] = C('REDIS_PORT') ? C('REDIS_PORT') : 6379;
        $options['timeout'] = C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false;
        $options['persistent'] = false;

        $this->options =  $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
        $func = $options['persistent'] ? 'pconnect' : 'connect';
        $this->handler  = new \Redis;
        $options['timeout'] === false ?
            $this->handler->$func($options['host'], $options['port']) :
            $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        //tiancheng
        //N('cache_read',1);
        if (empty($this->handler->socket)) {
            return false;
        }
        $value = $this->handler->get($this->options['prefix'].$name);
        $jsonData  = json_decode( $value, true );
        return ($jsonData === NULL) ? $value : $jsonData;	//检测是否为JSON数据 true 返回JSON解析数组, false返回源数据
    }

    /**
     * 写入缓存
     * @access public
     * @param string $name 缓存变量名
     * @param mixed $value  存储数据
     * @param integer $expire  有效时间（秒）
     * @return boolean
     */
    public function set($name, $value, $expire = null) {
        //tiancheng
        //N('cache_write',1);
        if (empty($this->handler->socket)) {
            return false;
        }

        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }

        $name   =   $this->options['prefix'].$name;
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        if(is_int($expire) && 0 !== $expire) {
            $result = $this->handler->setex($name, $expire, $value);
        }else{
            $result = $this->handler->set($name, $value);
        }
        if($result && $this->options['length']>0) {
            // 记录缓存队列
            $this->queue($name);
        }
        return $result;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @return boolean
     */
    //tiancheng
    public function rm($name) {
        if (empty($this->handler->socket)) {
            return false;
        }
        if (is_array($name)) {
            foreach ($name as $v) {
                $key[] = $this->options['prefix'].$v;
            }
        } else {
            $key = $this->options['prefix'].$name;
        }
        return $this->handler->del($key);
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        if (empty($this->handler->socket)) {
            return false;
        }
        return $this->handler->flushDB();
    }

    //tiancheng
    /**
     * KEY值自增
     * @access public
     * @param String $name 缓存变量名
     * @param $step 增加值
     * @return int
     */
    public function incre($name, $step = 1) {
        if (empty($this->handler->socket)) {
            return false;
        }
        return $this->handler->INCRBY($this->options['prefix'].$name, $step);
    }

    /**
     * KEY值自减1
     * @access public
     * @param string $name 缓存变量名
     * @param $step 减少值
     * @return int
     */
    public function decre($name, $step = 1) {
        if (empty($this->handler->socket)) {
            return false;
        }
        return $this->handler->DECRBY($this->options['prefix'].$name, $step);
    }

    /**
     * 设定KEY过期时间
     * @access public
     * @param string $name 缓存变量名
     * @return int
     */
    public function expire($name) {
        if (empty($this->handler->socket)) {
            return false;
        }
        return $this->handler->EXPIRE($this->options['prefix'].$name);
    }

    /**
     * 按照规则批量获取KEY
     * @access public
     * @param string $name 缓存变量名
     * @return int
     */
    public function keys($name) {
        if (empty($this->handler->socket)) {
            return false;
        }
        return $this->handler->KEYS($this->options['prefix'].$name);
    }

    /**
     * 批量设置 redis 值
     *
     * @param array $list
     * @return boolean
     *
     * @example
     * <pre>
     * $redis->mset(array('key0' => 'value0', 'key1' => 'value1'));
     * </pre>
     */
    public function mset($list)
    {
        if (empty($this->handler->socket)) {
            return false;
        }
        $newList = array();
        foreach ($list as $key => $value) {
            $newKey = $this->options['prefix'] . $key;
            $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
            $newList[$newKey] = $value;
        }
        return $this->handler->mset($newList);
    }

    /**
     * 批量获取 redis 值
     *
     * @param array $list
     * @return bool
     */
    public function mget($list)
    {
        if (empty($this->handler->socket)) {
            return false;
        }
        $newKeys = array();
        foreach ($list as $key) {
            $newKeys[] = $this->options['prefix'] . $key;
        }
        return $this->handler->mget($newKeys);
    }


    public function setnx($key,$val) {
        $name = $this->options['prefix'].$key;
        return $this->handler->setNx($name,$val);
    }
}
