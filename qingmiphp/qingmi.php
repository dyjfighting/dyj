<?php
/* 
 *  [ Core ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class qingmi {
	## 控制器
	public static $_config = [];
	## 容器
	public static $di = [];
	
	/**
	* 初始化
	*/
	public static function _init(){
		## 内容管理
		$__webdirname = pathinfo($_SERVER['PHP_SELF'],PATHINFO_DIRNAME);
		define('WEBDIRNAME',($__webdirname == DS)?'':$__webdirname);
		## 定义默认应用程序目录
		defined('DEFAULT_APP') || define('DEFAULT_APP','default');
		defined('ROUTEQUERY') || define('ROUTEQUERY','route');
		$query = explode('/',(string)isset($_GET[ROUTEQUERY]{0}) ? $_GET[ROUTEQUERY] : DEFAULT_APP);
		## 应用程序目录
		$__appdirname = array_shift($query);
		define('DIR_APP',DIR_APPS . $__appdirname . DS);
		## APP目录名称
		define('APPNAME', $__appdirname);
		## 定义控制器目录
		define('DIR_CONTROLLER', DIR_APP . 'controller' . DS);
		## 定义模型目录
		define('DIR_MODEL',DIR_APP . 'model' .DS);
		## 定义数据目录
		define('DIR_DATA',DIR_APP . 'data' .DS);
		## 定义模板目录
		define('DIR_VIEW',DIR_APP . 'view' .DS);
		## 定义配置文件
		define('FILE_CONFIG',DIR_APP . 'config.php');
		## 定义模板编译目录
		define('DIR_COMPILE',DIR_DATA . 'compile' . DS);
		## 定义APP模块目录
		defined('DIR_MODULES') || define('DIR_MODULES', DIR_CORE.'qingmimodules');

		## 定义session目录
		define('DIR_SESSION',DIR_DATA . 'sessions' . DS);
		## restfull 模式
		$is_get = $is_post = $is_delete = $is_put = false;
		if(isset($_SERVER['REQUEST_METHOD'])){
		switch (strtolower($_SERVER['REQUEST_METHOD'])){
			case 'get':$is_get = true;break;
			case 'post':$is_post = true;break;
			case 'delete':$is_delete = true;break;
			case 'put':$is_put = true;break;
			default:$is_get = true;break;
		}
		}
		## GET
		defined('IS_GET') || define('IS_GET',$is_get);
		## POST
		defined('IS_POST') || define('IS_POST',$is_post);
		## DELETE
		defined('IS_DELETE') || define('IS_DELETE',$is_delete);
		## PUT
		defined('IS_PUT') || define('IS_PUT',$is_put);
		## PUT
		$is_ajax = (isset($_SERVER["HTTP_X_REQUESTED_WITH"]) && strtolower($_SERVER["HTTP_X_REQUESTED_WITH"])=="xmlhttprequest") ? true : false;
		defined('IS_AJAX') || define('IS_AJAX',$is_ajax);
		
		## HTTPS
		$is_https = ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on') || (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https')) ? true : false; 
		defined('IS_HTTPS') || define('IS_HTTPS',$is_https);

		## 引用配置文件
		if(!is_file(FILE_CONFIG)){
			self::halt('not file exists ['.FILE_CONFIG.']');
		}
		## 应用配置文件
		$_config = require FILE_CONFIG;
		## 框架配置文件
		$_file_sysconfig = DIR_QINGMI .'configure.php';
		if(!is_file($_file_sysconfig)){
			self::halt('not file exists ['.$_file_sysconfig.']');
		}
		$_qingmi_config = require $_file_sysconfig;
		self::$_config = array_merge($_qingmi_config,$_config);
		## 定义是否开启编存编译
		define('APP_CACHE_COMPILE',self::$_config['cache_compile']);
		## 定义默认控制器目录
		define('APP_DEFAULT_CONTROLLER',self::$_config['app_default_controller']);
		## 定义默认方法
		define('APP_DEFAULT_ACTION',self::$_config['app_default_action']);
		## 是否伪静态
		define('APP_REWRITE',self::$_config['app_rewrite']);

		if(isset(self::$_config['db_config']['db_prefix'])){
			define('DB_PREFIX',self::$_config['db_config']['db_prefix']);
		}else{
			define('DB_PREFIX','');
		}

		## 模板URL目录
		if(self::$_config['app_tplpath']){
			define('TPLPATH',WEBDIRNAME . self::$_config['app_tplpath']);
		}else{
			define('TPLPATH',WEBDIRNAME . strtr(DIR_VIEW,array(dirname(DIR_APPS)=>'',DS=>'/')));
		}
		## static目录
		if(self::$_config['static_path']){
			define('STATIC_PATH',WEBDIRNAME . self::$_config['static_path']);
		}else{
			define('STATIC_PATH',WEBDIRNAME . strtr(DIR_VIEW,array(dirname(DIR_APPS)=>'',DS=>'/')));
		}
	}

	/**
	* 加载框架核心文件
	*/
	private static function loader($c){
		$cf = DIR_QINGMI . 'library' . DS . strtr(strtolower($c),['\\'=>'/']) . '.php';
		if(is_file($cf)){
			require  $cf;
		}
	}

	/**
	* 加载框架核心文件
	*/
	private static function vendorloader($c){
		$cf = DIR_VENDOR . strtr($c,['\\'=>'/']) . '.php';
		if(is_file($cf)){
			require  $cf;
		}else{
			self::halt('not file exists file ['.$cf.']');
		}
	}

	/**
	* 启动
	*/
	public static function  startup(){
		if(DEBUG){
			ini_set('display_errors',1);
			error_reporting(E_ALL);
		}
		## 系统初始化
		self::_init();
		## 注册自动加载类
		spl_autoload_register(['qingmi','loader'],true,true);
		set_error_handler(array('debug','catcher'));
		## 注册vendor加载类
		spl_autoload_register(['qingmi','vendorloader'],true);
		## 加载容器
		self::$di = new di();
		## 注入核心加载
		self::$di->set('load', new loader(self::$di));
		## 注入config
		self::$di->set('config',new config(self::$_config));
		self::$di->set('request', new request());
		self::$di->set('db',new db(self::$_config['db_config']));
		self::$di->set('session', new session(self::$di));
		self::$di->set('response',new response());
		self::$di->set('base',new base(self::$di));
		self::$di->get('response')->setheader('Content-type: text/html; charset=utf-8');
		self::$di->get('response')->setheader('X-Powered-By:medbk.cn');
		self::dispatch();
		if(DEBUG){
			// debug::stop();
			// debug::message();
		}
	}

	/**
	* 调度
	*/
	public static function dispatch(){
		$query = self::$di->get('request')->get(ROUTEQUERY)?:DEFAULT_APP;
		self::loader('action');
		self::loader('model');
		self::loader('controller');
		self::loader('view');
		self::loader('module');
		$controller = new action($query);
		$controller->execute(self::$di);
	}
	
	/**
	 *  提示信息页面跳转
	 *
	 * @param     string  $msg      消息提示信息
	 * @param     string  $gourl    跳转地址
	 * @param     int     $limittime  限制时间
	 * @return    void
	 */
	public static function showmsg($msg, $gourl, $limittime) {
		$gourl = empty($gourl) ? HTTP_REFERER : $gourl;
		$stop = $gourl!='stop' ? false : true;
		include(DIR_QINGMI.'message.tpl');
	}

	/**
	*  输出错误提示
	*
	* @param     string  $msg      提示信息
	* @return    void
	*/
	public static function halt($msg) {
		header('HTTP/1.1 404 Not Found');
		header('Status:404 Not Found');
		include(DIR_QINGMI.'halt.tpl');
		exit();
	}

}