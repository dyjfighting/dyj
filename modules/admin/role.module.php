<?php
/* 
 *  [ 角色列表 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleAdminRole extends Module{
	
	const table = 'admin_role';
	const table_priv = 'admin_role_priv';
	
	// 列表
    public function get_list($limit,$search = [],$sort = []){
		
		$table = DB_PREFIX . self::table;
		$field = "*";
		
		$where = ' 1 '; 
		
		$order = "roleid asc";
        $datalist = $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->selectlist();

        return [
            'datacount' => $this->db->datacount,
            'datalist' => $datalist,
        ];
    }
	
	// 取得用户信息
	public function get_info($id){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$field = "*";
		$where = "roleid = '{$id}'";
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data;
	}
	
	// 获取角色名称
	public function get_rolename($id){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$field = "rolename";
		$where = "roleid = '{$id}'";
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data ? $data['rolename'] : false;
	}
	
	// 下拉等级列表
	public function get_select($name="", $value="0", $root="",$class=""){
		$table = DB_PREFIX . self::table;
		if($root == '') $root = "≡ 作为一级分类 ≡";
		$data = $this->db->table($table)->field('roleid,rolename')->where(['disabled'=>0])->select();
		$html = '<select id="'.$name.'" name="'.$name.'" class="'.$class.'">';
		$html.= '<option value="">'.$root.'</option>';
		foreach($data as $val){			
			$str = $value != $val['roleid'] ? '' : ' selected="selected" ';
			$html .= '<option '.$str.'value="'.$val['roleid'].'">'.$val['rolename'].'</option>';
		}
		$html.= '</select>';

		return $html;
	}
	
	// 验证角色名是否存在
    public function check_rolename($rolename,$id = false){
        if(empty($rolename)) return false;
		$table = DB_PREFIX . self::table;
        $where['rolename'] = $rolename;
		if($id) {
			$where['roleid<>'] = intval($id); 
		}
        $data = $this->db->table($table)->where($where)->total();
        return $data;
    }
	
	// 添加
	public function add($data){
		$table = DB_PREFIX . self::table;
			
        $bind = [
            'rolename' => isset($data['rolename'])?$data['rolename']:'', 
            'disabled' => isset($data['disabled'])?$data['disabled']:0, 
            'description' => isset($data['description'])?$data['description']:'', 
            'system' => isset($data['system'])?$data['system']:0, 
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
            'rolename' => isset($data['rolename'])?$data['rolename']:'', 
            'disabled' => isset($data['disabled'])?$data['disabled']:0, 
            'description' => isset($data['description'])?$data['description']:'', 
        ];
		
		$where = array('roleid'=>$id);
		$status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
	
	// 删除
    public function delete($id,$whereSql = false){
        $table = DB_PREFIX . self::table;
        if(is_array($id)){
			$idStr = join(',',$id);
			$where = ' roleid in ('.$idStr.') ';
		}else{
			$id = intval($id);
			$where = array('roleid'=>$id);		
		}
		if($whereSql){
			$where = $whereSql;
		}
        $status = $this->db->table($table)->where($where)->delete();
        return $status !== false ? true : false;
    }
	
	// 获取权限列表
	public function get_list_priv($roleid){
		$table = DB_PREFIX . self::table_priv;
		
		$where = array('roleid'=>$roleid);

        $data = $this->db->table($table)->where($where)->select();
		
		return $data;
	}
	

	// 
    public function delete_priv($id,$whereSql = false){
        $table = DB_PREFIX . self::table_priv;
        if(is_array($id)){
			$idStr = join(',',$id);
			$where = ' roleid in ('.$idStr.') ';
		}else{
			$id = intval($id);
			$where = array('roleid'=>$id);		
		}
		if($whereSql){
			$where = $whereSql;
		}
        $status = $this->db->table($table)->where($where)->delete();
        return $status !== false ? true : false;
    }
	
	public function add_priv($data){
		$table = DB_PREFIX . self::table_priv;
			
        $bind = [
            'roleid' => isset($data['roleid'])?$data['roleid']:0, 
            'd' => isset($data['d'])?$data['d']:'', 
            'c' => isset($data['c'])?$data['c']:'', 
            'a' => isset($data['a'])?$data['a']:'', 
            'param' => isset($data['param'])?$data['param']:'', 
        ];
		
		/* 此表主键为非自增, 所以设置false不过滤主键,其返回值也不会返回自增id,所以执行结果判断用全等 */
        $status = $this->db->table($table)->bind($bind)->insert(false); 
        return $status !== false ? true : false;
	}
	
}






