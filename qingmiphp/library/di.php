<?php 
/* 
 *  [ Core.DI ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
final class Di {
		## 服务列表
        private $bind = [];
        /**
         * 获取服务
         * @param string $key 
         * @return  All
         */
        public function get($key) {
               if(isset($this->bind[$key])){
                   return $this->bind[$key];
               }else{
                   if(substr($key,0,6)=='model_'){
                       $this->bind['load']->model(strtr(substr($key,6),array('_'=>'/')));
                       return  $this->bind[$key];
                   }elseif(substr($key,0,7)=='module_'){
                       $this->bind['load']->module(strtr(substr($key,7),array('_'=>'/')));
                       return  $this->bind[$key];
                   }else{
                       $this->bind['load']->_qingmiphpautoload($key);
                       return  $this->bind[$key];
                   }
               }
        }

        /**
         * 设置服务
         * @param string $key
        * @param string $value
         */
        public function set($key, $value) {
           $this->bind[$key] = $value;
        }

        /**
         * 卸载服务
         * @param string $key
         */
        public function remove($key){
            unset($this->bind[$key]);
        }

        /**
        * 检测是否已经绑定
        * @param type $key
        * @return bool
        */
        public function has($key) {
               return isset($this->bind[$key]);
        }

}