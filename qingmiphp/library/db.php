<?php
/* 
 *  [ Core.db ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class db{
	private $_table = null;##表名
	private $_limit = null;##获取记录条数
	private $_where = null;##查询条件
	private $_idname = null;##主建
	private $_order = null;##排序方式
	private $_group = null;##分组
	private $_page = 'page';##分页标实
	private $_on = null;##连接查询条件
	private $_field = ' * ';##查询所需的字段
	private $_bind = array();##数据绑定
	private $_pagesize = 10;##分页条数
	public $_db = null;##数据库连接
	public $datacount = null;##获取查询条件的总条数
	public $sql = null;##执行 sql 语句
	public $exectime = 0;##sql查询时间
	public $exectimeall = 0;##sql查询总时间
	public $dbconfig = [];## 配置文件
	public $dblink = null;##数据库连接标实

	public function __construct($dbconfig) {
		$this -> dbconfig = $dbconfig;
	}

	public function _connect() {
		if (is_object($this -> _db)) return true;
		if (empty($this -> dbconfig)) qingmi::halt('配置文件不存在');
		$class = 'Driver\\Db\\'.$this -> dbconfig['db_driver'];
		if (class_exists($class)) {
			$this -> _db = new $class($this -> dbconfig);
			$this -> dblink = $this -> _db -> dblink;
		}else{
			qingmi::halt('无法加载 '.$this -> dbconfig['db_driver'].' 数据库驱动 !');
		}
	}

	/**
	* 事务
	*/
	public function beginTransaction() {
		$this -> _connect();
		$this -> dblink -> setAttribute(\PDO::ATTR_AUTOCOMMIT, false);
		$this -> dblink -> beginTransaction();
	}

	/**
	* 回滚
	*/
	public function rollback() {
		$this -> dblink -> rollBack();
	}

	/**
	* 执行
	*/
	public function commit() {
		$this -> dblink -> commit();
		$this -> dblink -> setAttribute(\PDO::ATTR_AUTOCOMMIT, true);
	}
        
	/**
	* 设置表名
	* @param$table
	*/
	public function table($table) {
		$this -> _connect();
		if (stripos($table, ',')) {
			$tables = explode(',', $table);
			$this -> table = '';
			foreach($tables as $val) {
				if ($this -> _table != '') $this -> _table .= ',';
				$this -> _table .= trim($val);
			}
		}else{
			#单表处理
			$this -> _table = $table;
		}

		#初始化
		$this -> initset();
		return $this;
	}

          /**
	* SQL 参数初始化
	*/
	public function initset() {
		$this -> _field = ' * ';
		$this -> _bind = [];
		$this -> _page = 'page';
		$this -> _pagesize = 10;
		$this -> datacount = $this -> _limit = $this -> _where = $this -> _idname = $this -> _order = $this -> _on = null;
	}

	/**
	 * 执行更新记录操作
	 * @param $data 		要更新的数据内容，参数可以为数组也可以为字符串，建议数组。
	 * 						为数组时数组key为字段值，数组值为数据取值
	 * 						为字符串时[例：`name`='myname',`hits`=`hits`+1]。
	 *						为数组时[例: array('name'=>'php','password'=>'123456')]	
	 * @param $primary 		是否过滤主键
	 * @param $filter 		选填 如果为真值[1为真] 则开启实体转义
	 * @return int          返回影响行数
	 */	
	public function update($primary = true ,$filter = false) {
		$data = $this -> _bind;
		if(is_array($data)){
			$data = $this->del_arr($data, $primary);				
			$value = '';
			if(!$filter){
				foreach($data as $k => $v){
					$value .= $k." = "."'".addslashes($v)."'".",";
				}
			}else{
				foreach($data as $k => $v){
					$value .= $k." = "."'".htmlspecialchars(addslashes($v))."'".",";
				}		
			}		
			$value=rtrim($value,',');				
		}else{
			$value=$data;		
		}
		$limit = ($this -> _limit == null) ? '' : $this -> _limit;
		$sql = 'UPDATE `'.$this->_table.'` SET '.$value. $this -> _where . $limit;
		$this -> sql = $sql;
		return $this -> _db -> update($sql);
	}

	/**
	* 删除数据
	*/
	public function delete($isrun = true) {
		$limit = ($this -> _limit == null) ? '' : $this -> _limit;
		$sql = 'delete from '.$this -> _table.$this -> _where.$limit;
		$this -> sql = $sql;
		if (!$isrun) return $sql;
		return $this -> _db -> delete($sql);
	}


	/**
	 * 执行添加记录操作     
	 * @param $primary 		是否过滤主键
	 * @param $filter       选填 如果为真值[1为真] 则开启实体转义
	 * @return int/boolean  成功：返回自动增长的ID，失败：false
	 */
	public function insert($primary = true ,$filter = false) {
		$data = $this -> _bind;
		if(!is_array($data)) {
			// 传入的数据必须是以数组形式！
			return false;
		}
		$data = $this->del_arr($data, $primary);
		$clo = '';
		$vs = '';
		if(!$filter){
			foreach($data as $k => $v){
				$clo .= $k.',';
				$vs .= "'".addslashes($v)."'".",";
			}
		}else{
			foreach($data as $k => $v){
				$clo .= $k.',';
				$vs .= "'".htmlspecialchars(addslashes($v))."'".",";
			}		
		}
		$clo = rtrim($clo,',');
		$vs = rtrim($vs,',');
		$sql = 'INSERT INTO `'.$this->_table.'`('.$clo.') VALUES ('.$vs.')';
		$this -> sql = $sql;
		return $this -> _db -> insert($sql);
	}

	/**
	* 直接执行 SQL
	*/
	public function exec($sql) {
		$this -> _connect();
		return $this -> _db -> exec($sql);
	}

	public function query($sql, $cache = true) {
		$this -> _connect();
		return $this -> _db -> query($sql, $cache);
	}

	/**
	* 单行查询
	*/
	public function onerows($isrun = true) {
		$limit = ($this -> _limit == null) ? ' limit 1 ' : $this -> _limit;
		$sql = 'select '.$this -> _field.' from '.$this -> _table.$this -> _on.$this -> _where.$this -> _group.$this -> _order.$limit;
		$this -> sql = $sql;
		if (!$isrun) return $sql;
		return $this -> _db -> getone($sql);
	}

	public function find($isrun = true) {
		return $this -> onerows($isrun);
	}

	/**
	* 多行查询
	*/
	public function select($isrun = true) {
		$sql = 'select '.$this -> _field.' from '.$this -> _table.$this -> _on.$this -> _where.$this -> _group.$this -> _order.$this -> _limit;
		$this -> sql = $sql;
		if (!$isrun) return $sql;
		return $this -> _db -> getdata($sql);
	}
	
	/**
	 * 返回记录行数。
	 * @return int 
	 */	
	public function total(){		
		$sql = 'SELECT COUNT(*) AS total FROM `'.$this->_table.'`'.$this->_where;
		$total = $this -> _db -> getone($sql);
        return intval($total['total']);		
	}
	
	
	/**
	* 分页列表查询
	*/
	public function selectlist($isrun = true) {
		#先取得记录总条数
        $sqlcountsql = 'select '.$this->_field.' from '.$this->_table.$this->_on.$this->_where;
        $zj = ($this ->_idname == null) ? $this->_field : $this->_idname;
        $sqlcountdata = $this ->_db->getone('select count(*) as countnum from ( select '.$zj.' from '.$this->_table.' '.$this->_on.' '.$this->_where.') as tmptable ');
        $sqlcount = $sqlcountdata['countnum'];
        $this->datacount = $sqlcount;
        //echo $sqlcount;
        $pagecount = ceil($sqlcount / $this->_pagesize);
        $page = (isset($_GET[$this->_page]) && is_numeric($_GET[$this->_page]) && intval($_GET[$this->_page]) > 0) ? $_GET[$this->_page] : 1;
        $limit = ' limit '.($page - 1) * $this->_pagesize.','.$this->_pagesize.' ';
        $sql = 'select '.$this->_field.' from '.$this->_table.$this->_on.$this->_where.$this->_order.$limit;
        $this->sql = $sql;
        if ($sqlcount > 0) {
                if (!$isrun) return $sql;
                return $this->_db->getdata($sql);
        }else{
                return null;
        }
	}
          
	/**
	* 执行条数
	* @param $limit
	*/
	public function limit($limit) {
		if (!empty($limit)) {
			$this -> _pagesize = $limit;
			$this -> _limit = ' limit '.$limit;
		} else {
			$this -> _pagesize = null;
			$this -> _limit = null;
		}
		return $this;
	}

	/**
	 * 内部方法：过滤数组，把不是表单的元素过滤掉
	 * @param $arr
	 * @param $primary 是否过滤主键
	 * @return array
	 */
	private function del_arr($arr, $primary = true){
        $re = array();		
		if(!is_array($arr)) return false;		
		$fields = $this->get_fields();		
		foreach ($arr as $k => $v){
			if(in_array($k,$fields)){
				$re[$k] = $v;
			}
		}
		if($primary){
			$p = $this->get_primary();
			if(isset($re[$p])) unset($re[$p]);
		}
		return $re;
	}
	
	/**
	 * 获取表字段
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_fields($table = '') {
		$table = empty($table) ? $this->_table : $table;
		$fields = array();
		$sql = "SHOW COLUMNS FROM `$table`";
		$data = $this -> _db -> getdata($sql);
		foreach($data as $value){
			$fields[] = $value['Field'];
		}
		return $fields;
	}
	
	/**
	 * 获取数据表主键
	 * @param $table 		数据表 可选
	 * @return array
	 */
	public function get_primary($table = '') {
		$table = empty($table) ? $this->_table : $table;
		$sql = "SHOW COLUMNS FROM `$table`";
		$data = $this -> _db -> getdata($sql);
		$re = '';
		foreach($data as $value){
			if($value['Key'] == 'PRI') {
				$re = $value['Field'];
				break;
			}
		}	
		return $re;
	}
	
	/**
	 * 组装where条件，将数组转换为SQL语句
	 * @param array $where  要生成的数组,参数可以为数组也可以为字符串，建议数组。
	 * return string
	 */
	public function where($arr = ''){
		if(empty($arr)) {
			$this -> _where = null;
			return $this;
		}		
		if(is_array($arr)) {
			$args = func_get_args();
			$str = '(';
			foreach ($args as $v){
				foreach($v as $k => $value){
					$value = addslashes($value);
					if(!strpos($k,'>') && !strpos($k,'<') && !strpos($k,'=') && substr($value, 0, 1) != '%' && substr($value, -1) != '%'){    //where(array('age'=>'22'))
						$str .= $k." = '".$value."' AND ";
					}else if(substr($value, 0, 1) == '%' || substr($value, -1) == '%'){	//where(array('name'=>'%php%'))
						$str .= $k." LIKE '".$value."' AND "; 
					}else{
						$str .= $k."'".$value."' AND ";      //where(array('age>'=>'22'))
					}
				}
				$str = rtrim($str,' AND ').')';
				$str .= ' OR (';
			}
			$str = rtrim($str,' OR (');
			$this -> _where = ' where '.$str;
			return $this;
		}else{
			$this -> _where = ' where '.$arr;	
			return $this;
		}
	}

	/**
	* 排序
	* @param $order
	*/
	public function order($order) {
		$this -> _order = (!empty($order)) ? ' order by '.$order : null;
		return $this;
	}
	
	/**
	* 分组
	* @param $group
	*/
	public function group($group) {
		$this -> _group = (!empty($group)) ? ' GROUP BY '.$group : null;
		return $this;
	}
	

	/**
	* 获取当前分页的页码
	* @param $page
	*/
	public function page($page) {
		$this -> _page = (!empty($page)) ? $page : 'page';
		return $this;
	}

	/**
	* 查询
	* @param $field
	*/
	public function field($field) {
		$this -> _field = (!empty($field)) ? $field : ' * ';
		return $this;
	}

	/**
	* 数据组用与数据的添加与修改
	* @param $fieldvalue
	*/
	public function bind($array) {
		$this -> _bind = (!empty($array)) ? $array : array();
		return $this;
	}
	
	/**
	* 连接查询条件
	* @param $on
	*/
	public function on($on) {
		$this -> _on = (!empty($on)) ? ' on '.$on : null;
		return $this;
	}
	

	/**
	* 主健名称(用于分页查询)
	* @param $idname
	*/
	public function idname($idname) {
		$this -> _idname = (!empty($idname)) ? $idname : null;
		return $this;
	}

	public function escape($value) {
		$search = array("\\", "\0", "\n", "\r", "\x1a", "'", '"');
		$replace = array("\\\\", "\\0", "\\n", "\\r", "\Z", "\'", '\"');
		return str_replace($search, $replace, $value);
	}

	public function close() {
		$this -> dblink = null;
	}

	/**
	* 析构方法
	* @access public
	*/
	public function __destruct() {
		$this -> close();
	}
	
}