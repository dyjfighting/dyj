<?php
/*
 *  [ 商户管理 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */


class ModuleMemberMerchant extends Module{

	const TABLE = 'member_merchant';

	// 获取客服列表
    public function getList($limit, $search)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' 1=1 ';
        // 搜索 第三方ID/商户名称
        if (isset($search['keyword'])) {
            $where .= ' and ( locate("'.$search['keyword'].'",name) > 0 or `mid`="' . $search['keyword'] . '" ) ';
        }
        $order    = "id desc";
        $datalist = $this->db->table($table)->where($where)->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    public function getOptionsData($search=[],$orders='id desc'){
        $table = DB_PREFIX . self::TABLE;
        $where = ' 1=1 ';
        // 搜索 第三方ID/商户名称
        if (isset($search['location'])) {
            $where .= ' and location = "'.$search['location'].'" ';
        }
        $datalist = $this->db->table($table)->field('id,name')->where($where)->order($orders)->select();
        return $datalist?:false;
    }

    // 添加商户
    public function add($data){
    	$bind = [
    		'name' => isset($data['name'])?$data['name']:'',  // 商户名称
            'mid' => isset($data['mid'])?$data['mid']:'',   // 第三方商户ID
            'location' => isset($data['location'])?$data['location']:'',   // 第三方商户ID
            'account' => isset($data['account'])?$data['account']:'',   // 第三方商户ID
    		'passwd' => isset($data['passwd'])?sha1($data['passwd']):'',   // 第三方商户ID
    		'createtime' => TIME,
    	];
    	$table = DB_PREFIX . self::TABLE;
    	$status = $this->db->table($table)->bind($bind)->insert();
    	return $status?:false;
    }

    // 修改商户
    public function edit($merchantId,$data){
        $bind = [
            'name' => isset($data['name'])?$data['name']:'',  // 商户名称
            'mid' => isset($data['mid'])?$data['mid']:'',   // 第三方商户ID
            'location' => isset($data['location'])?$data['location']:'',   // 第三方商户ID
            'account' => isset($data['account'])?$data['account']:'',   // 第三方商户ID
        ];
        if(isset($data['passwd'])){
            $bind['passwd'] = sha1($data['passwd']);
        }
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "'.$merchantId.'" ';
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status?true:false;
    }

    public function isAccount($account,$id=false){
        $table = DB_PREFIX.self::TABLE;
        $where = ' `account` = "'.$account.'" ';
        if($id){
            $where .= ' and `id`!="'.$id.'" ';
        }
        $status = $this->db->table($table)->where($where)->find();
        return $status?true:false;
    }

    // 获取指定商户信息
    public function getInfo($merchantId){
        static $merchants = [];
        if(!isset($merchants[$merchantId])){
            $table = DB_PREFIX . self::TABLE;
            $where = ' `id` = "'.$merchantId.'" ';
            $data = $this->db->table($table)->where($where)->find();
            if($data){
                $merchants[$merchantId] = $data;
            }else{
                $merchants[$merchantId] = false;
            }
        }
        return $merchants[$merchantId];
    }

    // 判断商户ID是否存在
    public function isMerchanId($id){
        return $this->getInfo($id)?true:false;
    }

    public function getName($id){
        $info = $this->getInfo($id);
        if($info){
            return $info['name'];
        }
        return false;
    }

}
