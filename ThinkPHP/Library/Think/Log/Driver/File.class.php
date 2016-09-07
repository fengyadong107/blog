<?php
// +----------------------------------------------------------------------
// | TOPThink [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2011 http://topthink.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace Think\Log\Driver;

class File {

    protected $config  =   array(
        'log_time_format'   =>  ' c ',
        'log_file_size'     =>  210000000000, //20G
        'log_path'          =>  '',
    );

    // 实例化并传入参数
    public function __construct($config=array()){
        $this->config   =   array_merge($this->config,$config);
    }

    /**
     * 日志写入接口
     * @access public
     * @param string $log 日志信息
     * @param string $destination  写入目标
     * @return void
     */
    public function write($log,$destination='') {
        $now = date($this->config['log_time_format']);
        if(empty($destination))
            $destination = $this->config['log_path'].date('y_m_d').'.log';
        //检测日志文件大小，超过配置大小则备份日志文件重新生成
        if(is_file($destination) && floor($this->config['log_file_size']) <= filesize($destination) )
              rename($destination,dirname($destination).'/'.time().'-'.basename($destination));
        //tiancheng
        if (APP_DEBUG) {
            error_log("[{$now}] ".get_client_ip().' '.$_SERVER['REQUEST_URI']."\r\n{$log}\r\n", 3,$destination);
        } else {
            error_log("[{$now}] ".get_client_ip().' '.$_SERVER['REQUEST_URI']."  {$log}\r\n", 3,$destination);
        }
    }
}
