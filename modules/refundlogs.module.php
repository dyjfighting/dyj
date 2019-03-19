<?php

class ModuleRefundlogs extends Module
{
    const TABLE = 'orders_refund_errorlogs';

    // 获取列表
	public function getList($limit,$search=[],$order = 'id desc'){
		$table = DB_PREFIX . self::TABLE;
        $where = [];
        if(isset($search['orderno'])){
            $where[] = ' `orderno` = "'.$search['orderno'].'" ';
        }
        $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
	}

}
