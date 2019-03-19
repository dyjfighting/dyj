<?php 
/* 
 *  [ Core.Config ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
return [
// 	'配置项'=>'值',
	## 数据库配置
	'db_config' => [],
	################### 系统配置
	## 默认控制器
	'app_default_controller'=> 'home',
	## 默认方法
	'app_default_action' => 'index',
	## 是否开启模板编译缓存
	'cache_compile' => false,
           ## 缓存方式
           'cache_driver' => 'file', 
           ## 是否开启调试模式
           'debug' => true,    
           #################### APP自定义配置
           ## 模板URL目录
           'app_tplpath' => '',
           ##################### url访问
           'app_rewrite' => false
];
