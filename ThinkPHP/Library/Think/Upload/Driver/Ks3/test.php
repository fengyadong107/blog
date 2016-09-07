<?php
// vim: set expandtab cindent tabstop=4 shiftwidth=4 fdm=marker:
// +----------------------------------------------------------------------+
// | The Wanka Inc                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 2015, Wanka Inc. All rights reserved.                  |
// +----------------------------------------------------------------------+
// | Authors: The PHP Dev Team, BY xuechuanchuan.                         |
// | Descript:                                                            |
// +----------------------------------------------------------------------+ 

/**
 * @email    xuechuanchuan@gm825.com
 * @descript  **
 * @author   xuechuanchuan
 */
define("KS3_API_VHOST",FALSE);
//是否开启日志(写入日志文件)
define("KS3_API_LOG",TRUE);
//是否显示日志(直接输出日志)
define("KS3_API_DISPLAY_LOG", TRUE);
//定义日志目录(默认是该项目log下)
define("KS3_API_LOG_PATH","");
//是否使用HTTPS
define("KS3_API_USE_HTTPS",FALSE);
//是否开启curl debug模式
define("KS3_API_DEBUG_MODE",FALSE);

include('Ks3Client.class.php');

$client = new Ks3Client("B7GCLTYXtvmHcuFm3Yy9","A9MFyCpEYnaFABdEx7jJ/axjcp9+NSoTY8zugFb4");
$args = array(
    "Bucket"=>"pic-wanka",
    "Key"=>"logo.png",
    "Content"=>file_get_contents('http://wankacms.com/static/msite/images/logo.png'),//要上传的内容
    "ACL"=>"public-read",//可以设置访问权限,合法值,private、public-read
    "ObjectMeta"=>array(//设置object的元数据,可以设置"Cache-Control","Content-Disposition","Content-Encoding","Content-Length","Content-MD5","Content-Type","Expires"。当设置了Content-Length时，请勿大于实际长度，如果小于实际长度，将只上传部分内容。
        "Content-Type"=>"binay/ocet-stream",
        ),
    );
$client->putObjectByContent($args);