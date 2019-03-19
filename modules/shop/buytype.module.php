<?php
/*
 *  [ 购买类型 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleShopBuytype extends Module
{

    const TABLE = 'goods_buytype';

    public function getList()
    {
        $table = DB_PREFIX . self::TABLE;
        $data = $this->db->table($table)->order('id asc')->select();
        return $data;
    }

    public function getName($id){
    	static $info = [];
    	if(!isset($info[$id])){
    		$data = $this->getList();
    		foreach($data as $val){
    			if($val['id']==$id){
    				$info[$id] = $val['name'];
    				break;
    			}
    		}
    	}
    	return $info[$id];
    }

}
