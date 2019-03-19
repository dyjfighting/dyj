<?php
namespace Driver\Db;

class pdomysql{
        public $dblink;
        private $options;
        public function __construct($dbconfig) {
            if(!class_exists('PDO')) \qingmi::halt('当前PHP环境不支持 PDO 类，请联系系统管理员');
            try{
                $dbdns = 'mysql:host='.$dbconfig['db_host'].';dbname='.$dbconfig['db_name'];
                if(!empty($dbconfig['db_charset'])){
                    $this->options[\PDO::MYSQL_ATTR_INIT_COMMAND]    =   'SET NAMES '.$dbconfig['db_charset'];
                    $dbdns .= ';charset='.$dbconfig['db_charset'];
                }
                $this->options[\PDO::ATTR_ERRMODE] = \PDO::ERRMODE_EXCEPTION;
                if(isset($dbconfig['db_persistent']) && $dbconfig['db_persistent'] == true){
                        $this->options[\PDO::ATTR_PERSISTENT] = true;
                        $this->options[\PDO::ATTR_TIMEOUT] = $dbconfig['db_timeout'];
                }
                $this->dblink = new \PDO($dbdns, $dbconfig['db_user'], $dbconfig['db_pass'],$this->options);
                // $this->dblink->exec('set session wait_timeout=10000');
            }catch(\PDOException $e){
				\qingmi::halt('数据库连接失败:'.$e->getMessage());
            }
        }

        /**
         * 事务内容
         * @param type $sql
         * @return type
         */
        public function commit($sql){
            try{
                    ## 开启事务
                    $this->dblink->beginTransaction();
                    $resource = $this->dblink->query($sql);
                    $this->dblink->commit();
                    $resource->closeCursor();
                    // $stmt->closeCursor()
                    return $resource;
            }catch(\PDOException $e){
                    $this->dblink->rollBack(); 
                   $this->geterr($sql);
            }
        }

        private function _query($sql){
            try{
				\debug::addmsg($sql, 1);
                return $this->dblink->query($sql);
            }  catch (\PDOException $e){
				$this->geterr($sql);		
            }
        }
		
		private function _exec($sql,$cmd=false){
            try{
                    $result = $this->dblink->exec($sql);
                    if(false === $result){ 
                            return false; 
                    }else{ 
                        if($cmd=='update'){
                            return true;
                        }else{
                            return $result; 
                        }
                    } 
            }  catch (\PDOException $e){
                throw new \Exception('无效的[ SQL ]：'.$sql . $e->getMessage());
            }
        }

		/**
		 * 获取错误提示
		 */		
		private function geterr($msg = ''){
			$errorInfo = $this->dblink->errorInfo();
			if(DEBUG){
				echo '<div style="font-size:14px;text-align:left; border:1px solid #9cc9e0;line-height:25px; padding:5px 10px;color:#000;font-family:Arial, Helvetica,sans-serif;"><b> Error : </b>'. $msg .' <br /><b>Errno : </b>'. $errorInfo[1] .' <br /> <b>Error : </b> <span>'. $errorInfo[2] .'</span></div>';
				exit;
			}else{
				error_log('<?php exit;?> SQL Error: '.date('m-d H:i:s').' | Errno: '.$errorInfo[1].' | Error: '.$errorInfo[2].' | SQL: '.$msg."\r\n", 3, DIR_QINGMI.'error_log.php');
				\qingmi::halt('SQL Error!');
				exit;
			}
		}
        

        /**
        * 取得多条数据
        * @param $sql
        */
       public function getdata($sql){
            static $getdatasql = array();
            $sqlid = md5($sql);
            if(!isset($getdatasql[$sqlid])){
                $resource=$this->_query($sql);
                $data = $resource->fetchAll(\PDO::FETCH_ASSOC);
                $resource=null;
                $getdatasql[$sqlid] = $this->stripslashes_array($data);
            }
            return $getdatasql[$sqlid];
        }


        public function query($sql,$cache=true){
            static $getdatasql = array();
            $sqlid = md5($sql);
            if(!isset($getdatasql[$sqlid]) || $cache==false){
                $resource=$this->_query($sql);
                $data = $resource->fetchAll(\PDO::FETCH_ASSOC);
                $resource=null;
               
                $getdatasql[$sqlid] = $this->stripslashes_array($data);
                if($getdatasql[$sqlid] && count($getdatasql[$sqlid])==1){
                    $getdatasql[$sqlid] = $getdatasql[$sqlid][0];
                }
            }
            return $getdatasql[$sqlid];
        }
        
        /**
           * 取得单条数据
           * @param $sql
           * @param $limited
      */
        public function getone($sql, $limited = false){
            if ($limited == true){
                $sql = trim($sql . ' LIMIT 1');
            }
            $resource = $this->_query($sql);
            if ($resource !== false){
                    $row = $resource->fetchAll(\PDO::FETCH_ASSOC);
                    if (isset($row[0])){
                             return $this->stripslashes_array($row[0]);
                    }
                    return false;
            }
            return false;
        } 

        public function exec($sql){
                return $this->_exec($sql,'update');
        }
        
        /**
         * 更新
         * @param $sql
         */

        public function update($sql){
                return $this->_exec($sql,'update');
        }

        /**
         * 删除
         * @param $sql
         */
        public function delete($sql){
                return $this->_exec($sql,'update');
        }

        /**
         * 插入
         */
        public function insert($sql){
                $status = $this->_exec($sql);
				// var_dump(111);
                return $status?$this->dblink->lastInsertId():false;
        }

        /**
         * 数据格式化
         */
        private function stripslashes_array($value){
                $value = is_array($value) ?
                                array_map(array($this,'stripslashes_array'), $value) :
                                stripslashes($value);
                return $value;
        }
        
        
        
 }