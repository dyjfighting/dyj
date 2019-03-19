<?php
/* 
 *  [ 菜单管理 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleSystemMenu extends Module{
	
	const table = 'menu';
	
	public function get_list(){
		$table = DB_PREFIX . self::table;
		$field = " * ";
		$where = " 1 ";
		$order = " listorder ASC,id DESC ";
		$data = $this->db->table($table)->field($field)->where($where)->order($order)->select();
		
		return $data;
	}
	
	public function get_info($id){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$field = "*";
		$where = array('id'=>$id);
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data;
	}
		
	// 下拉列表
	public function get_select($name="", $value="0", $root="",$class=""){
		$tree = $this->module_tree;
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$table = DB_PREFIX . self::table;
		$data = $this->db->table($table)->field('id,name,parentid')->order('listorder ASC,id DESC')->select();
		if($root == '') $root = "≡ 作为一级分类 ≡";
		
		$html = '<select id="'.$name.'" name="'.$name.'" class="'.$class.'">';
		$html.= '<option value="">'.$root.'</option>';
		$array = array();
		foreach($data as $v) {
			$v['selected'] = $v['id'] == $value ? 'selected' : '';
			$array[] = $v;
		}
		$str  = "<option value='\$id' \$selected>\$spacer \$name</option>";
		$tree->init($array);
		$html.= $tree->get_tree(0, $str);
		$html.= '</select>';

		return $html;
	}
	
	// 添加
	public function add($data){
		$table = DB_PREFIX . self::table;
        $bind = [
            'parentid' => isset($data['parentid'])?$data['parentid']:0, 
            'name' => isset($data['name'])?$data['name']:'', 
            'd' => isset($data['d'])?$data['d']:'', 
            'c' => isset($data['c'])?$data['c']:'', 
            'a' => isset($data['a'])?$data['a']:'', 
            'param' => isset($data['param'])?$data['param']:'', 
            'icon' => isset($data['icon'])?$data['icon']:'', 
            'target' => isset($data['target'])?$data['target']:'', 
            'display' => isset($data['display'])?$data['display']:0, 
            'spread' => isset($data['spread'])?$data['spread']:0, 
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
            'parentid' => isset($data['parentid'])?$data['parentid']:0, 
            'name' => isset($data['name'])?$data['name']:'', 
            'd' => isset($data['d'])?$data['d']:'', 
            'c' => isset($data['c'])?$data['c']:'', 
            'a' => isset($data['a'])?$data['a']:'', 
            'param' => isset($data['param'])?$data['param']:'', 
            'icon' => isset($data['icon'])?$data['icon']:'', 
            'target' => isset($data['target'])?$data['target']:'', 
            'display' => isset($data['display'])?$data['display']:0, 
            'spread' => isset($data['spread'])?$data['spread']:0, 
        ];
		
        $where = array('id'=>$id);
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
	
	/**
	 * @brief 获取子分类可以无限递归获取子分类
	 * @param int $catId 分类ID
	 * @param int $level 层级数
	 * @return string 所有分类的ID拼接字符串
	 */
	public function catChild($catId,$level = 1)
	{
		if($level == 0)
		{
			return $catId;
		}

		$temp   = array();
		$result = array($catId);

		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$table = DB_PREFIX . self::table;
			$temp = $this->db->table($table)->where(['parentid'=>$id])->select();
			foreach($temp as $key => $val)
			{
				if(!in_array($val['id'],$result))
				{
					$result[] = $val['id'];
				}
			}
			next($result);
		}
		return join(',',$result);
	}
	
	// 删除
    public function delete($id,$whereSql=false){
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
	
	// 查询是否有子分类
	public function total_parentid($parentid){
		$table = DB_PREFIX . self::table;
		
		$where = array();
		if($parentid){
			$parentid = intval($parentid);
			$where = array('parentid'=>$parentid);
		}
				
		$data = $this->db->table($table)->where($where)->total();
		return $data;
	}
	
	// 排序
	public function order($id,$sort){
		$table = DB_PREFIX . self::table;
        $bind = [
            'listorder' => intval($sort)
        ];
		
        $where = array('id'=>$id);
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
}






