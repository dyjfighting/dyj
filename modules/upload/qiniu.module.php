<?php
/**
 * 七牛云上传
 *
 */
use Qiniu\Auth;
use Qiniu\Storage\UploadManager;
use Qiniu\Storage\BucketManager;
require_once  DIR_VENDOR . 'Qiniu/functions.php';

class ModuleUploadQiniu extends Module{
	
	private $accesskey;
	private $secretkey;
	private $bucket;
	
	public function __construct($di) {
		parent::__construct($di);
		$config = $this->base->get_config(['qny_accesskey','qny_secretkey','qny_bucket','qny_is_open']);
		$this->accesskey = $config['qny_accesskey'];
		$this->secretkey = $config['qny_secretkey'];
		$this->bucket = $config['qny_bucket'];
	}
	
	/**
	* 上传文件
	*/	
    public function upload($filepath){
		
		// 构建鉴权对象
		$auth = new Auth($this->accesskey,$this->secretkey);
		
		// 生成上传 Token
		$token = $auth->uploadToken($this->bucket);
		
		// 要上传文件的本地路径
		$filePath = DIR_ROOT . ltrim($filepath,'/');

		// 上传到七牛后保存的文件名
		$key = ltrim($filepath,'/');

		// 初始化 UploadManager 对象并进行文件的上传。
		$uploadMgr = new UploadManager();

		// 调用 UploadManager 的 putFile 方法进行文件的上传。
		list($ret, $err) = $uploadMgr->putFile($token, $key, $filePath);

		if ($err !== null) {
			return false;
		} else { 
			$host = $this->base->get_config('qny_host');
			return rtrim($host,'/').'/'.$ret['key'];
		}
		
    }
}
