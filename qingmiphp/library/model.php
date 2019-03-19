<?php
/* 
 *  [ Core.model ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
abstract class model {
	protected $di;
           
	public function __construct($di) {
		$this->di = $di;
	}
        
	public function __get($key) {
		return $this->di->get($key);
	}

	public function __set($key, $value) {
		$this->di->set($key, $value);
	}
}