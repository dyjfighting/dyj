<?php
/* 
 *  [ 管理员列表 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleAdminAdmin extends Module{
	
	const table = 'admin';
	
	// 列表
    public function get_list($limit,$search = [],$sort = []){
		
		$table = DB_PREFIX . self::table;
		$field = "*";
		
		$where = ' 1 '; 
		
		if(isset($search['roleid'])){
			$where .= " and roleid = {$search['roleid']}";
		}	
		
		$order = "id asc";
        $datalist = $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->selectlist();

        return [
            'datacount' => $this->db->datacount,
            'datalist' => $datalist,
        ];
    }
	
	// 取得用户信息
	public function get_info($id,$username = false){
		
		$table = DB_PREFIX . self::table;
		$field = "*";
		if($username){
			$where = array('username'=>$username);
		}else{
			$id = $id ? intval($id) : 0;
			$where = array('id'=>$id);	
		}

		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data;
	}
	
	// 获取管理员名称
	public function get_username($id){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$field = "username";
		$where = "id = '{$id}'";
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data ? $data['username'] : false;
	}
		
	// 验证注册用户名是否存在
    public function check_username($username,$id = false){
        if(empty($username)) return false;
		$table = DB_PREFIX . self::table;
        $where['username'] = $username;
		if($id) {
			$where['id<>'] = intval($id); 
		}
        $data = $this->db->table($table)->where($where)->total();
        return $data;
    }
	
	// 生成secret
    private function createsecret($key = ''){
         return strtolower(md5($key.uniqid(true)));
    }
    
    // 生成密码
    public function getpasswd($passwd,$secret){
        return sha1( md5( $passwd ) . $secret);
    }
	
	// 添加
	public function add($data){
		$table = DB_PREFIX . self::table;
		
		// 密码加密
		$password = isset($data['password'])?$data['password']:'PW'.uniqid();
		$secret = $this->createsecret();
		
        $bind = [
            'username' => isset($data['username'])?$data['username']:'GL'.uniqid(),
            'password' => $this->getpasswd($password,$secret),
            'secret' => $secret,
            'avatar' => isset($data['avatar'])?$data['avatar']:0, 
            'realname' => isset($data['realname'])?$data['realname']:'', 
            'email' => isset($data['email'])?$data['email']:'', 
            'roleid' => isset($data['roleid'])?$data['roleid']:0, 
            'addpeople' => isset($data['addpeople'])?$data['addpeople']:'', 
            'rolename' => isset($data['rolename'])?$data['rolename']:'', 
            'createtime' => TIME,
        ];
		
		$insertid = $this->db->table($table)->bind($bind)->insert();
        if($insertid){
            return $insertid;
        }else{
            return false;
        }
	}
	
	// 修改
	public function edit($id,$data){
		$table = DB_PREFIX . self::table;
		
		$bind = [
            'avatar' => isset($data['avatar'])?$data['avatar']:0, 
            'realname' => isset($data['realname'])?$data['realname']:'', 
            'email' => isset($data['email'])?$data['email']:'', 
            'roleid' => isset($data['roleid'])?$data['roleid']:0,  
            'rolename' => isset($data['rolename'])?$data['rolename']:'', 
        ];
		
		$where = array('id'=>$id);
		$status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
	
	// 修改密码
	public function edit_password($id,$password){
		$id = $id ? intval($id) : 0;
		// 密码加密
		$secret = $this->createsecret();
		$password = $this->getpasswd($password,$secret);
		// 修改密码
		$table = DB_PREFIX . self::table;
		$bind = [
			'password'=>$password,
			'secret'=>$secret
		];
		$where = array('id'=>$id);
		$status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		return $status !== false ? true : false;
	}
	
	// 删除
    public function delete($id,$whereSql = false){
        $table = DB_PREFIX . self::table;
        if(is_array($id)){
			$idStr = join(',',$id);
			$where = ' id in ('.$idStr.') ';
		}else{
			$id = intval($id);
			$where = array('id'=>$id);		
		}
		if($whereSql){
			$where = $whereSql;
		}
        $status = $this->db->table($table)->where($where)->delete();
        return $status !== false ? true : false;
    }
	
	// 检查该角色下的是否有管理员存在
	public function total_roleid($roleid){
		$table = DB_PREFIX . self::table;

		$where = array('roleid'=>intval($roleid));
		
		$data = $this->db->table($table)->where($where)->total();
		return $data;
	}
	
	/**
	 *  @brief 更新登录ip 登录时间
	 *  
	 *  @param $id      管理员id
	 *  @param $loginip 登录ip
	 *  @return 
	 */
	public function set_loginip($id,$loginip){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$bind = [
			'loginip'=>$loginip,
			'logintime'=>TIME
		];
		$where = array('id'=>$id);
		$status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		return $status !== false ? true : false;
	}
	
}






