<?php

class ModuleShopHomeblock extends Module{

	const TABLE = 'homeblock';

	public function getList($limit,$search = []){
        $table = DB_PREFIX . self::TABLE;
        $where = [];
        // category
        if(isset($search['storeid'])){
            $where[] = ' storeid = "'.$search['storeid'].'"  and status in(1,0) ';
        }
        $order = 'orders desc,id asc';
		//echo $order;
		//exit;
        $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }
    public function getListall($storeid,$type=false){
		$table = DB_PREFIX . self::TABLE;
        $where[] = ' storeid = "'.$storeid.'" ';
        if($type){
            $where[] = '`leixing`="'.$type.'"  and status=1';
        }
        $order = 'orders desc';
        $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->select();
        return $datalist;
	}
    // 获取info
    public function getInfo($id){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id`= "'.$id.'" ';
        $info = $this->db->table($table)->where($where)->find();
        return $info?:false;
    }

    // 添加
    public function add($data){
        $table = DB_PREFIX . self::TABLE;
        $status = $this->db->table($table)->bind($data)->insert();
        return $status?:false;
    }

    // 修改
    public function edit($id,$data){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id`= "'.$id.'" ';
        $status = $this->db->table($table)->where($where)->bind($data)->limit(1)->update();
        return $status?:false;
    }

    // 删除
    public function delete($id){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id`= "'.$id.'" ';
        $status = $this->db->table($table)->where($where)->limit(1)->delete();
        return $status?:false;
    }



}