<?php
/* 
 *  [ 附件管理 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleSystemAttachment extends Module{
	
	const table = 'attachment';
	
	// 列表
    public function get_list($limit,$search = [],$sort = []){
		
		$table = DB_PREFIX . self::table;
		$field = "*";
		
		$where = ' 1 '; 
		
		if(isset($search['originname'])){
			$where.= " and originname like '%{$search['originname']}%'";
		}
		if(isset($search['username'])){
			$where.= " and username like '%{$search['username']}%'";
		}
		if(isset($search['fileext'])){
			$where.= " and fileext = '{$search['fileext']}'";
		}
		if(isset($search['date'])){
			$where.= " and uploadtime between {$search['date']['start']} and {$search['date']['end']}";
		}
		
		$order = "id desc";
		
        $datalist = $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->selectlist();

        return [
            'datacount' => $this->db->datacount,
            'datalist' => $datalist,
        ];
    }
    
	public function get_info($id){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$field = "*";
		$where = array('id'=>$id);
		
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data;
	}
	
	// 添加
	public function add($data){
		$table = DB_PREFIX . self::table;
        $bind = [
            'originname' => isset($data['originname']) ? $data['originname'] : '',
            'filename' => isset($data['filename']) ? $data['filename'] : '',
            'filepath' => isset($data['filepath']) ? $data['filepath'] : '',
            'filesize' => isset($data['filesize']) ? $data['filesize'] : 0, 
            'fileext' => isset($data['fileext']) ? $data['fileext'] : '', 
            'isimage' => isset($data['isimage']) ? $data['isimage'] : 1, 
            'userid' => isset($data['userid']) ? $data['userid'] : 0,
            'username' => isset($data['username']) ? $data['username'] : '',
            'uploadtime' => isset($data['uploadtime']) ? $data['uploadtime'] : 0,
            'uploadip' => isset($data['uploadip']) ? $data['uploadip'] : '',
            'appname' => isset($data['appname']) ? $data['appname'] : '',
        ];
        $insertid = $this->db->table($table)->bind($bind)->insert();
        if($insertid){
            return $insertid;
        }else{
            return false;
        }
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
	
	
}

