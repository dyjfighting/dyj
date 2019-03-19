<?php
/* 
 *  [ Core.loader ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */

final class loader{
    private $di;
    public function __construct($di) {
        $this->di = $di;
    }
    
    public function __get($key) {
        return $this->di->get($key);
    }
    
	
    public function model($model) {
		// $model = strtolower($model); 统一小写
		$class = 'Model' . preg_replace('/[^a-zA-Z0-9]/', '', $model);
		$di_modelname = 'model_' . str_replace('/', '_', $model);
		if(!$this->di->has($di_modelname)){
			$mfile = DIR_MODEL . strtr($model,['/'=>DS]) . '.model.php';
			if (file_exists($mfile)) {
				include_once($mfile);
				$this->di->set($di_modelname, new $class($this->di));
			} else {
				debug::addmsg('模型文件：'.$mfile.' 未找到!',2);
			}
		}
    }

    public function view($template, $___sys__assign = [] ,$callback = false, $cache = false,$htmlformat=false) {
		$tplfile = strtr(DIR_VIEW . $template,['/'=>DS]);
		if(file_exists($tplfile)){
			$tplinfo=pathinfo($template);
			$compiledir =  ($tplinfo['dirname']!='.') ? $tplinfo['dirname']. DS : '';
			$compiledir = DIR_COMPILE . $compiledir;
			$compilefile=$compiledir.$tplinfo['filename'].'.tpl.php';
			extract($___sys__assign);
			if(($cache || APP_CACHE_COMPILE) && file_exists($compilefile)){
				// return $compilefile;
				if($callback){
					ob_start();
					require $compilefile;
					$output = ob_get_contents();
					ob_end_clean();
					return $output;
				}else{
					require $compilefile;
					return true;
				}
			}else{
				$io = new io();
				$tpldata = $io->readfile($tplfile);
				$io->create_dir($compiledir);
				## 创建编译目录
				$view = new view();
				## 模板编译
				$tplphp = $view->compilefile($tpldata,$htmlformat);
				if($htmlformat){
					#清除换行符  清除换行符  清除制表符 
					$tplphp  =strtr($tplphp,["\r\n"=>'',"\n"=>'',"\t"=>'']);
					//$pattern = array ("/> *([^ ]*) *</","/[\s]+/","/<!--[^!]*-->/","/\" /", "/ \"/", "'/\*[^*]*\*/'" ); 
					//$replace = array ( ">\\1<", " ","", "\"","\"",""); 
					$pattern = ["#> *(.*?) *<#is","/[\s]+/","/<!--(.*?)-->/", "/ \"/", "'/\*[^*]*\*/'"]; 
					$replace = [">\\1<", " ","","\"",""]; 
					$tplphp = preg_replace($pattern, $replace, $tplphp);    
				}
				$io->writefile($compilefile, $tplphp);
			}

			if($callback){
				ob_start();
				require $compilefile;
				$output = ob_get_contents();
				ob_end_clean();
				return $output;
			}else{
				require $compilefile;
			}
		}else{
			debug::addmsg('模板文件: ' . $tplfile . ' 未找到!',2);
		}
    }
    
    public function module($module) {
		// $module = strtolower($module); 统一小写
		$class = 'Module' . preg_replace('/[^a-zA-Z0-9]/', '', $module);
		$di_modulename = 'module_' . str_replace('/', '_', $module);

		if(!$this->di->has($di_modulename)){
			$modulefile = DIR_MODULES . strtr($module,['/'=>DS]) . '.module.php';

			if (file_exists($modulefile)) {
				include_once($modulefile);
				$this->di->set($di_modulename, new $class($this->di));

			} else {
				debug::addmsg('类文件：'.$modulefile.' 未找到!',2);
			}
		}
    }
    
    
    public function controller($query, $args = []) {
		$action = new Action($query, $args);

		return $action->execute($this->di);
    }
    
    /**
     * 自动加载
     * @param type $class
     */
    public function _qingmiphpautoload($class){
        $this->di->set($class, new $class($this->di));
    }
	
}