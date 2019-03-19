<?php 
/* 
 *  [ Core.Config ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class config{
	
	private $data = array();
	
	public function __construct($config) {
		$this->data = $config;
	}
	
	public function get($key) {
		return (isset($this->data[$key]) ? $this->data[$key] : null);
	}
	
	public function set($key, $value) {
		$this->data[$key] = $value;
	}
	
	public function has($key) {
		return isset($this->data[$key]);
	}
	
}