<?php
/* 
 *  [ Core.action ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */

final class action {
	private $file;
	private $class;
	private $method;
	private $args = array();
	public function __construct($query, $args = array()) {
		$query = (substr($query,-1)=='/')?substr($query,0,-1):$query;
		$parts = explode('/',(string)$query);
		$this->module = array_shift($parts);
		if(empty($parts)){
			$parts[0] = APP_DEFAULT_CONTROLLER;
		}
		$partstring = implode(DS, $parts);
		$f = DIR_APP  . 'controller' . DS . $partstring .'.controller.php';
		if(is_file($f)){
			$this->method = APP_DEFAULT_ACTION;
			$this->file=$f;
		}else{
			$this->method = array_pop($parts);
			$partstring = implode(DS, $parts);
			if(empty($partstring)){
				$partstring = APP_DEFAULT_CONTROLLER;
			}
			$f = DIR_APP  . 'controller' . DS . $partstring .'.controller.php';
			$this->file=$f;
		}
		$this->class = 'Controller' . preg_replace('/[^a-zA-Z0-9]/', '', $partstring);
		if(file_exists($f)){
			$this->file=$f;
		}else{
			$this->geterr('控制器: '.$f.' 不存在!');
		}
		if ($args)  $this->args = $args;
	}
	public function execute($di) {
		if (substr($this->method, 0, 2) == '__') {
			$this->geterr('无效的' . $this->method.' 动作!');
		}
		include_once($this->file);
		if(class_exists($this->class)){
			$class = $this->class;
			$controller = new $class($di);
		}else{
			$this->geterr('在 '.$this->file.' 中未发现 '.$this->class.' 控制器!');
		}
		## method
		if (is_callable(array($controller, $this->method))) {
		## 如果发现存在默认动作，先执行默认动作
		if(is_callable([$controller,'init'])) call_user_func([$controller,'init']);
			return call_user_func_array([$controller, $this->method], $this->args);
		} else {
			$this->geterr('在控制器 '.$class.' 中未发现 '.$this->method.' 动作!');
		}
	}

	public function geterr($msg = ''){
		if(DEBUG){
			
			echo '<div style="font-size:14px;text-align:left; border:1px solid #9cc9e0;line-height:25px; padding:5px 10px;color:#000;font-family:Arial, Helvetica,sans-serif;"><b> Error : </b>'. $msg .' </div>';
			exit;
		}else{
			error_log('<?php exit;?> Error: '.date('m-d H:i:s').' | Controller: '.$msg."\r\n", 3, DIR_QINGMI.'error_log.php');
			qingmi::halt('很抱歉，您查看的页面找不到了！');
			exit;
		}

	}

}