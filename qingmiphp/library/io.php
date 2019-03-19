<?php
/* 
 *  [ Core.IO ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class io{
    /**
    * 读文件
    * @param $filename
    */
    public function readfile($filename,$mode='rb'){
		if(file_exists($filename)){
			$fp=fopen($filename,$mode);
			$fd='';
			flock($fp,LOCK_SH);
			while (!feof($fp)) {
			   $fd.= fgets($fp,1024);
			}
			flock($fp,LOCK_UN);
			fclose($fp);
			return $fd;
		}else{
			trigger_error('文件: '.$filename.'  不存在!');
		}
    }
    /**
    * 写文件
    * @param $filename
    * @param $string
    * @param $mode
    */
	public  function writefile($filename,$string,$mode='w+'){
		$dir = dirname($filename);
		if(!is_dir($dir)){
			mkdir($dir,0777,true);
		}
		$fp=fopen($filename,$mode);
		flock($fp,LOCK_EX);
		fwrite($fp,$string);
		flock($fp,LOCK_UN);
		fclose($fp);
	}

   /**
    * 创建目录
    * @param $path
    */
	public  function create_dir($path){
		if (!is_dir($path)){
			mkdir($path,0777,true);
		}
	}

}

