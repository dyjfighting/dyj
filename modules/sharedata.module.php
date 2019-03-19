<?php


class ModuleSharedata extends Module{

	const TABLE = 'sharedata';

	## 记录分享数据
	public function add($openid,$goodsid){
		if($this->isshare($openid,$goodsid)>0){
			return true;
		}
		$table = DB_PREFIX . self::TABLE;
		$bind = [
			'openid' => $openid,
			'goodsid' => $goodsid,
			'createtime' => TIME
		];
		$status = $this->db->table($table)->bind($bind)->insert();
		return $status?:false;
	}

	## 判断是否分享了
	public function isshare($openid,$goodsid){
		$table = DB_PREFIX . self::TABLE;
		$where = ' `openid` = "'.$openid.'" and `goodsid`="'.$goodsid.'" ';
		$field = ' count(*) as sharecount ';
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data['sharecount']?:0;
	}


}

