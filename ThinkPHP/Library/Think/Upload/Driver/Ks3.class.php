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

namespace Think\Upload\Driver;
require_once __DIR__.'/Ks3/Ks3Client.class.php';

class Ks3{
	private $http = 'http';
	private $config = array(
        'secrectKey'     => '', //
        'accessKey'      => '', //
        'bucket'         => '', //
     	'domain' => '',
    );
	public function __construct($root,$config) {
		$this->config = array_merge($this->config,$config);
		$this->ks3 = new \Ks3Client($this->config['accessKey'],$this->config['secrectKey']);
	}


	public function save(&$file,$replace=false) {
		$file['name'] = $file['savepath'] . $file['savename'];
		$config['content'] = file_get_contents($file['tmp_name']);
		$url = $this->upload($config);
		$file['url'] = $url;
		return false ===$url ? false : true;
	}

	public function upload($config) {
		if(empty($config['content'])) return false;

		$image_type = $this->_getImgType($config['content']);
		$upload_name = $config['filename'] ? $config['filename'] : date('Y-m-d') . '_' . uniqid() . '.'.$image_type;
		$request_args = array(
			"Bucket"=>$config['bucket'] ? $config['bucket'] : $this->config['bucket'],
		    "Key"=> $upload_name,
		    "Content"=>$config['content'] ,//要上传的内容
		    "ACL"=>$config['acl'] ? $config['acl'] : "public-read",//可以设置访问权限,合法值,private、public-read
		    "ObjectMeta"=>array(//设置object的元数据,可以设置"Cache-Control","Content-Disposition","Content-Encoding","Content-Length","Content-MD5","Content-Type","Expires"。当设置了Content-Length时，请勿大于实际长度，如果小于实际长度，将只上传部分内容。
		        "Content-Type"=>"binay/ocet-stream",
		    ),
		);
		try {
			$this->ks3->putObjectByContent($request_args);
			return $this->http.'://'.$this->config['domain'].'/'.$upload_name;
		} catch(Exception $e) {
			return false;
		}
	}

	private function _getImgType($header)
    {
        if ($header{0}.$header{1} == "\x89\x50") {
            return 'png' ;
        } elseif ($header{0}.$header{1} == "\xff\xd8") {
            return 'jpeg' ;
        } elseif ($header{0}.$header{1}.$header{2} == "\x47\x49\x46") {
            return 'gif';
        }
        return 'jpg';
    }

	/**
	 * [checkRootPath description]
	 * @return [type] [description]
	 */
    public function checkRootPath(){
    	return true;
    }

   /**
    * [checkSavePath description]
    * @param  [type] $savepath [description]
    * @return [type]           [description]
    */
	public function checkSavePath($savepath){
		return true;
    }

    /**
     * [mkdir description]
     * @param  [type] $savepath [description]
     * @return [type]           [description]
     */
    public function mkdir($savepath){
    	return true;
    }

}

