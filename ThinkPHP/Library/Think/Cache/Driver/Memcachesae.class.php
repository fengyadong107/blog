<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2012 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
namespace Think\Cache\Driver;
use Think\Cache;

defined('THINK_PATH') or exit();
/**
 * Memcache缓存驱动
 * @category   Extend
 * @package  Extend
 * @subpackage  Driver.Cache
 * @author    liu21st <liu21st@gmail.com>
 */
class Memcachesae extends Cache {

    /**
     * 架构函数
     * @param array $options 缓存参数
     * @access public
     */
    function __construct($options=array()) {
        if(empty($options)) {
            $options = array (
                'host'        =>  C('MEMCACHE_HOST') ? C('MEMCACHE_HOST') : '127.0.0.1',
                'port'        =>  C('MEMCACHE_PORT') ? C('MEMCACHE_PORT') : 11211,
                'timeout'     =>  C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false,
                'persistent'  =>  false,
            );
        }
        //tiancheng
        //覆盖如下几项，但并不影响过期时间等设置
        $options['host'] = C('MEMCACHE_HOST') ? C('MEMCACHE_HOST') : '127.0.0.1';
        $options['port'] = C('MEMCACHE_PORT') ? C('MEMCACHE_PORT') : 11211;
        $options['timeout'] = C('DATA_CACHE_TIMEOUT') ? C('DATA_CACHE_TIMEOUT') : false;
        $options['persistent'] = false;

        $this->options      =   $options;
        $this->options['expire'] =  isset($options['expire'])?  $options['expire']  :   C('DATA_CACHE_TIME');
        $this->options['prefix'] =  isset($options['prefix'])?  $options['prefix']  :   C('DATA_CACHE_PREFIX');
        $this->options['length'] =  isset($options['length'])?  $options['length']  :   0;
      //  $func               =   isset($options['persistent']) ? 'pconnect' : 'connect';
        $this->handler      =  memcache_init();//[sae] 下实例化
        //[sae] 下不用链接
        $this->connected=true;
        // $this->connected    =   $options['timeout'] === false ?
        //     $this->handler->$func($options['host'], $options['port']) :
        //     $this->handler->$func($options['host'], $options['port'], $options['timeout']);
    }

    /**
     * 是否连接
     * @access private
     * @return boolean
     */
    private function isConnected() {
        return $this->connected;
    }

    /**
     * 读取缓存
     * @access public
     * @param string $name 缓存变量名
     * @return mixed
     */
    public function get($name) {
        //N('cache_read',1);
        $value =  $this->handler->get($_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name);
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
    //tiancheng
    public function set($name, $value, $expire = null) {
        //N('cache_write',1);
        if(is_null($expire)) {
            $expire  =  $this->options['expire'];
        }
        //对数组/对象数据进行缓存处理，保证数据完整性
        $value  =  (is_object($value) || is_array($value)) ? json_encode($value) : $value;
        $name   =   $_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name;
        if($this->handler->set($name, $value, 0, $expire)) {
            if($this->options['length']>0) {
                // 记录缓存队列
                $this->queue($name);
            }
            return true;
        }
        return false;
    }

    /**
     * 删除缓存
     * @access public
     * @param string $name 缓存变量名
     * @param bool $ttl
     * @return boolean
     */
    //tiancheng
    public function rm($name, $ttl = false) {

        if (is_array($name)) {
            foreach ($name as $v) {
                $key = $_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$v;
                $ret = $ttl === false ? $this->handler->delete($key) : $this->handler->delete($key, $ttl);
            }
        } else {
            $key = $_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name;
            $ret = $ttl === false ? $this->handler->delete($key) : $this->handler->delete($key, $ttl);
        }
        return $ret;
    }

    /**
     * 清除缓存
     * @access public
     * @return boolean
     */
    public function clear() {
        return $this->handler->flush();
    }

    //tiancheng
    /**
     * KEY值自增1
     * @access public
     * @param String $name 缓存变量名
     * @param int $step
     * @return int
     */
    public function incre($name, $step = 1) {
        return $this->handler->increment($_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name, $step);
    }

    /**
     * KEY值自减1
     * @access public
     * @param string $name 缓存变量名
     * @param int $step
     * @return int
     */
    public function decre($name, $step = 1) {
        return $this->handler->decrement($_SERVER['HTTP_APPVERSION'].'/'.$this->options['prefix'].$name, $step);
    }

    /**
     * 队列缓存
     * @access protected
     * @param string $key 队列名
     * @return mixed
     */
    //[sae] 下重写queque队列缓存方法
    protected function queue($key) {
        $queue_name=isset($this->options['queue_name'])?$this->options['queue_name']:'think_queue';
        $value  =  F($queue_name);
        if(!$value) {
            $value   =  array();
        }
        // 进列
        if(false===array_search($key, $value)) array_push($value,$key);
        if(count($value) > $this->options['length']) {
            // 出列
            $key =  array_shift($value);
            // 删除缓存
            $this->rm($key);
            if (APP_DEBUG) {
                    //调试模式下记录出队次数
                        $counter = Think::instance('SaeCounter');
                        if ($counter->exists($queue_name.'_out_times'))
                            $counter->incr($queue_name.'_out_times');
                        else
                            $counter->create($queue_name.'_out_times', 1);
           }
        }
        return F($queue_name,$value);
    }
}
