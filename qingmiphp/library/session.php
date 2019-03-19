<?php
class session {
        public $data = array();
		private $lefttime = 1440;
		private $di;
		
		public function __get($key) {
				return $this->di->get($key);
		}

		public function __set($key, $value) {
				$this->di->set($key, $value);
		}
    
		public function __construct($di) {
			$this->di = $di;
			// 获取系统设置 -- session过期时间 ,为空或值类型不正确则取php.ini 默认过期时间
			$config = $this->db->table(DB_PREFIX."shop_config")->field("value")->where(['code'=>'session_lefttime'])->find();
			if($config){
				$session_lefttime = !empty($config['value']) ? intval($config['value']) : 0;
				if($session_lefttime > 0) $this->lefttime = $session_lefttime;
			} 
		
		}
		
		public function sess_open(){
			return true;
		}
		
		public function sess_close(){
			return true;
		}
		
		// 读取session
		public function sess_read($sess_id)	{
			//从数据库读取数据
			//根据sess_id（由系统提供）获取数据
			//读数据时要过滤掉过期的数据
			$expire = time() - $this->lefttime;
			$sess = $this->db->table(DB_PREFIX.'session')->where(['sess_id'=>$sess_id,'sess_expire>='=>$expire])->find();
			if($sess){
				return $sess['sess_info'];
			}
			return false;
		}
		
		private function getuserkey(){
			return $this->config->get('sessionuseridkey');
		}

		
		// 写入操作
		public function sess_write($sess_id, $sess_info){
			
			if($this->db->table(DB_PREFIX.'session')->where(['sess_id'=>$sess_id])->total()){
				$bind = [
					'sess_info' => $sess_info,
					'sess_expire' => TIME,
				];
				$this->db->table(DB_PREFIX.'session')->bind($bind)->where(['sess_id'=>$sess_id])->update();
			}else{
				$bind = [
					'sess_id' => $sess_id,
					'sess_info' => $sess_info,
					'sess_expire' => TIME,
				];
				$this->db->table(DB_PREFIX.'session')->bind($bind)->insert(false);
			}
			
			$key = $this->getuserkey();
			$info = unserialize($sess_info);
			$userid = $info[$key];
			if($userid > 0){
				
				$this->db->table(DB_PREFIX.'session')->where(['userid'=>$userid,'sess_id<>'=>$sess_id])->delete();
				
				$this->db->table(DB_PREFIX.'session')->bind(['userid'=>$userid,'ip'=>$this->base->getip()])->where(['sess_id'=>$sess_id])->update();
				
			}
			return true;

		}
		 //5. 销毁
		public function sess_destroy($sess_id){
			//删除数据库中信息
			$res = $this->db->table(DB_PREFIX.'session')->where(['sess_id'=>$sess_id])->delete();
			return $res;
		}
		 //6.回收
		public function sess_gc(){
			//删除过期的数据
			$expire = time() - $this->lefttime;
			$res = $this->db->table(DB_PREFIX.'session')->where(['sess_expire<'=>$expire])->delete();
			return $res;
		}
		
		public function sessioninit() {
			if (!session_id()) {
				if($this->config->get('sessiontype')=='mysql'){
					ini_set("session.save_handler", "user");  
					session_set_save_handler(
						array($this,'sess_open'),
						array($this,'sess_close'),
						array($this,'sess_read'),
						array($this,'sess_write'),
						array($this,'sess_destroy'),
						array($this,'sess_gc')
					);
				}else{
					if(!file_exists(DIR_SESSION . 'z')){
							$s = '0123456789abcdefghijklmnopqrstuvwxyz';
							$len = strlen($s);
							$io = new io();
							for($i = 0; $i < $len; $i++) {
								 $io->create_dir(DIR_SESSION . $s[$i]);
							}
					}
					session_save_path('1;' . DIR_SESSION);
				}
				ini_set('session.serialize_handler','php_serialize');
				ini_set('session.auto_start', 0);
				ini_set('session.name','MEDBK');
				ini_set('session.use_only_cookies', 'On');
				ini_set('session.use_trans_sid', 0);
				ini_set('session.cookie_httponly', 'On');
				session_start();
			}
			$this->data = & $_SESSION;
			
		}
		


        public function get($name){
            if(!isset($_SESSION)) $this->sessioninit();
            return isset($_SESSION[$name])?$_SESSION[$name]:false;
        }

        public function set($name,$value){
           if(!isset($_SESSION)) $this->sessioninit();
            $_SESSION[$name] = $value;
        }

        public function clear($name){
            if(!isset($_SESSION)) $this->sessioninit();
            if(isset($_SESSION[$name])){
                unset($_SESSION[$name]);
            }
        }


        public function getid() {
                if(!isset($_SESSION)) $this->sessioninit();
                return session_id();
        }

        public function destroy() {
                return session_destroy();
        }

}

