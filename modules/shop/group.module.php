<?php

class ModuleShopGroup extends Module{

	const TABLE = 'orders';

	// 获取正在拼团的内容
	public function getCurrentList($goodsid){
		$table = DB_PREFIX . self::TABLE;
		$where = ' `goodsid`= "'.$goodsid.'" and `buytype`=2 and `ispay`=1  group by `groupkey`';
		$field = ' count(id) as counts,id,wx_openid,groupkey,grouptime ';
		$order = ' id asc ';
		$data = $this->db->table($table)->where($where)->field($field)->order($order)->select();
		if($data){
			foreach($data as $key=>$val){
				$uinfo = $this->module_member_user->getInfo($val['wx_openid']);
				$data[$key]['avatar'] = $uinfo['avatar'];
				$data[$key]['nickname'] = $uinfo['nickname'];
			}
			return $data;
		}else{
			return false;
		}
	}
	
	// 创建groupkey
	public function createGroupKey($goodsid){
		$ext = explode('.',sprintf('%.10f',microtime(true)));
		return md5($goodsid.date('ymdHi').$ext[1]);
	}

	// 判断是否加入了该组
	public function isJoinGruop($openid,$groupkey){
		$table = DB_PREFIX . self::TABLE;
		$where = ' `buytype`=2 and `wx_openid`="'.$openid.'" and `ispay` <= 1 and `groupkey`="'.$groupkey.'" ';
		$onedata = $this->db->table($table)->where($where)->order('id asc')->find();
		return $onedata?true:false;
	}

	// 获取某个团的人员
	public function getGroupInfo($groupkey){
		$table = DB_PREFIX . self::TABLE;
		$where = ' `buytype`=2 and `ispay`=1 and `groupkey`="'.$groupkey.'" ';
		$data = $this->db->table($table)->where($where)->order('id asc')->select();
		if($data){
			foreach($data as $key=>$val){
				$uinfo = $this->module_member_user->getInfo($val['wx_openid']);
				$data[$key]['avatar'] = $uinfo['avatar'];
				$data[$key]['nickname'] = $uinfo['nickname'];
			}
			return $data;
		}else{
			return false;
		}
	}

}