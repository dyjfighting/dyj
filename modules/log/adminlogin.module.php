<?php
/* 
 *  [ 后台登录日志 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
 
class ModuleLogAdminlogin extends Module{
	
	const table = 'admin_loginlogs';
	
	// 获取列表
    public function get_list($limit,$search = [],$sort = []){
		$table = DB_PREFIX . self::table;
		$field = " * ";
		
		$where = ' 1 '; 
		if(isset($search['date'])){		
			$where .= " and logintime between {$search['date']['start']} and {$search['date']['end']}";
		}
		$order = "id desc";
        $datalist = $this->db->table($table)->field($field)->where($where)->order($order)->limit($limit)->selectlist();

        return [
            'datacount' => $this->db->datacount,
            'datalist' => $datalist,
        ];
    }
	

	// 删除
    public function delete($month){
        $table = DB_PREFIX . self::table;
        $where = 'logintime < '.strtotime('-'.intval($month).' month');
        $status = $this->db->table($table)->where($where)->delete();
        return $status;
    }
	
	/**
	 *  @brief 写入日志
	 *  
	 *  @param $adminname   用户名
	 *  @param $password    登录密码
	 *  @param $loginresult 登录结果 0登录失败 1登录成功
	 *  @param $cause       类型
	 *  @return 
	 */
	public function write($adminname,$password,$loginresult,$cause) {
		
		$table = DB_PREFIX . self::table;
		
		$loginip = $this->base->getip();
		$address = $this->base->get_address($loginip);
		
        $bind = [
            'adminname' => $adminname, 
            'logintime' => TIME, 
            'loginip' => $loginip, 
            'address' => $address, 
            'password' => $password, 
            'loginresult' => $loginresult, 
            'cause' => $cause, 
        ];

        $insertid = $this->db->table($table)->bind($bind)->insert();
        if($insertid){
            return $insertid;
        }else{
            return false;
        }
	}
}






