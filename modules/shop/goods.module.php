<?php
/*
 *  [ 商品分类 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleShopGoods extends Module
{

    const TABLE = 'goods';

    ##########  库存操作开始  ##########
    // private $handler = null;
    // protected function init(){
    //     if ($this->handler == null) {
    //         $this->handler = new Redis();
    //         $redisconfig = $this->config->get('redis');
    //         $this->handler->connect($redisconfig['host'], $redisconfig['port']);
    //         $this->handler->auth($redisconfig['password']); //密码验证
    //         $this->handler->select($redisconfig['select']);//选择数据库
    //     }
    // }
    // ## 设置一个库存记录
    // public function setStock($key, $value){
    //     $this->init();
    //     return $this->handler->set($key, $value);
    // }
    // ## 删除记录库存
    // public function delStock($key){
    //     $this->init();
    //     return $this->handler->del($key);
    // }
    // ## 获取库存
    // public function getStock($key){
    //     $this->init();
    //     return $this->handler->get($key);    
    // }
    // ## 库存减少
    // public function subStock($key,$num=false){
    //     $this->init();
    //     if($num){
    //         $status = $this->handler->decrby($key,$num);
    //     }else{
    //         $status = $this->handler->decr($key);
    //     }
    //     return $status;    
    // }
    // ## 库存增加
    // public function addStock($key,$num=false){
    //     $this->init();
    //     if($num){
    //         $status = $this->handler->incrby($key,$num);
    //     }else{
    //         $status = $this->handler->incr($key);
    //     }
    //     return $status;
    // }
    ##########  库存操作结束  ##########


    // 添加
    public function add($bind)
    {
        $table  = DB_PREFIX . self::TABLE;
        $status = $this->db->table($table)->bind($bind)->insert();
        return $status ?: false;
    }

    // 修改
    public function edit($id, $bind)
    {
        $table  = DB_PREFIX . self::TABLE;
        $where  = ' `id` = "' . $id . '"';
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ? true : false;
    }

    // 商品列表
    public function getHomeTopicList($limit=false,$search=[],$order='orders desc,id desc'){
        $table = DB_PREFIX . self::TABLE;
        $where = [];
        // 搜索 
        if (isset($search['keyword'])) {
            $where[] = ' (locate("' . $search['keyword'] . '",title) > 0 or locate("' . $search['keyword'] . '",desctitle) > 0 ) ';
        }
        // 是否上架
        if(isset($search['status'])){
            $where[] = ' status = "'.$search['status'].'" ';
        }
        // 首页所属版块
        if(isset($search['homeblock'])){
            $where[] = ' homeblock = "'.$search['homeblock'].'" ';
        }

        // 首页所属版块
        if(isset($search['hometopic'])){
            $where[] = ' hometopic = "'.$search['hometopic'].'" ';
        }

        // 推荐推荐
        if(isset($search['isjingxuan'])){
            $where[] = ' isjingxuan = "'.$search['isjingxuan'].'" ';
        }
        // category
        if(isset($search['category_id'])){
            if(is_array($search['category_id'])){
                $where[] = '  category_id in ('.implode(',', $search['category_id']).') ';
            }else{
                $where[] = '  category_id = '.$search['category_id'].' ';
            }
        }
        // 不包括自己
        if(isset($search['no_goods_id'])){
            $where[] = 'id <> '.$search['no_goods_id'];
        }
        // category
        if(isset($search['istime'])){
            // $where[] = ' (istime=0 or (istime=1 and time_start <= "'.TIME.'" and time_stop >= "'.TIME.'")) ';
            $where[] = ' (istime=0 or (istime=1 and time_stop >= "'.TIME.'")) ';
        }
        if(isset($search['store'])){
            $where[] = ' store > 0 ';
        }
        if($limit){
            $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        }else{
            $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->select();
        }
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    // 商品列表
    public function getHomeBlockList($limit=false,$search=[],$order='orders desc,id desc'){
        $table = DB_PREFIX . self::TABLE;
        $where = [];
        // 搜索 
        if (isset($search['keyword'])) {
            $where[] = ' (locate("' . $search['keyword'] . '",title) > 0 or locate("' . $search['keyword'] . '",desctitle) > 0 ) ';
        }
        // 是否上架
        if(isset($search['status'])){
            $where[] = ' status = "'.$search['status'].'" ';
        }
        // 首页所属版块
        if(isset($search['homeblock'])){
            $where[] = ' homeblock = "'.$search['homeblock'].'" ';
        }

        // 推荐推荐
        if(isset($search['isjingxuan'])){
            $where[] = ' isjingxuan = "'.$search['isjingxuan'].'" ';
        }
        // category
        if(isset($search['category_id'])){
            if(is_array($search['category_id'])){
                $where[] = '  category_id in ('.implode(',', $search['category_id']).') ';
            }else{
                $where[] = '  category_id = '.$search['category_id'].' ';
            }
        }
		// 不包括自己
		if(isset($search['no_goods_id'])){
			$where[] = 'id <> '.$search['no_goods_id'];
		}
        // category
//        if(isset($search['istime'])){
//            // $where[] = ' (istime=0 or (istime=1 and time_start <= "'.TIME.'" and time_stop >= "'.TIME.'")) ';
//            $where[] = ' (istime=0 or (istime=1 and time_stop >= "'.TIME.'")) ';
//        }
        $where[] = ' (istime=0 or (istime=1 and time_stop >= "'.TIME.'")) ';
//        if(isset($search['store'])){
//            $where[] = ' store > 0 ';
//        }

        if($limit){
            $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        }else{
            $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->select();
        }
        // echo $this->db->sql;
        // exit();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    // 库存返还
    public function refundStock($goodsid){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$goodsid.'" ';
        $this->db->_connect();
        $this->db->dblink->query('update '.$table.' set `store`=`store`+1 where '.$where.' limit 1');
        return true;
    }

    // 商品列表
    public function getList($limit,$search=[],$order='orders desc,id desc'){
    	$table = DB_PREFIX . self::TABLE;
        $where = [];
        // 搜索 
        if (isset($search['keyword'])) {
            $where[] = ' (locate("' . $search['keyword'] . '",title) > 0 or locate("' . $search['keyword'] . '",desctitle) > 0 ) ';
        }
        // 是否上架
        if(isset($search['status'])){
            $where[] = ' status = "'.$search['status'].'" ';
        }
        
        // 按erp_number搜索
        if(isset($search['erp_number'])){
            $where[] = ' erp_number = "'.$search['erp_number'].'" ';
        }

        // 按购买方式搜索
        if(isset($search['buytype'])){
            if(is_array($search['buytype'])){
                $where[] = '  buytype in ('.implode(',', $search['buytype']).') ';
            }else{
                $where[] = '  buytype = '.$search['buytype'].' ';
            }
        }

        // 推荐推荐
        if(isset($search['isjingxuan'])){
            $where[] = ' isjingxuan = "'.$search['isjingxuan'].'" ';
        }
        // category
        if(isset($search['category_id'])){
            if(is_array($search['category_id'])){
                $where[] = '  category_id in ('.implode(',', $search['category_id']).') ';
            }else{
                $where[] = '  category_id = '.$search['category_id'].' ';
            }
        }
        // category
        if(isset($search['istime'])){
            // $where[] = ' (istime=0 or (istime=1 and time_start <= "'.TIME.'" and time_stop >= "'.TIME.'")) ';
            $where[] = ' (istime=0 or (istime=1 and time_stop >= "'.TIME.'")) ';
        }
        if(isset($search['store'])){
            $where[] = ' store > 0 ';
        }

        $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    // 获取库存
    public function getStock(){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"';
        $data  = $this->db->table($table)->where($where)->find();
        return $data ?$data['store']: false;
    }
    // 减少库存
    public function subStock($id,$stock=1){
        ## 更新库存
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"';
        $updatesql = ' update '.$table.' set `store`=`store`-"'.$stock.'" where `id`="'.$id.'" limit 1 ';
        $status = $this->db->exec($updatesql);
        return $status?true:false;
    }
    // 增加库存
    public function addStock($id,$stock=1){
        ## 更新库存
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"';
        $updatesql = ' update '.$table.' set `store`=`store`+"'.$stock.'" where `id`="'.$id.'" limit 1 ';
        $status = $this->db->exec($updatesql);
        return $status?true:false;
    }

    // 修改排序
    public function editorders($id,$orders){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"';
        $bind = [
            'orders' => $orders
        ];
        $is = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
        return $is ? true : false;
    }

    // 获取Info
    public function getInfo($id)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"';
        $data  = $this->db->table($table)->where($where)->find();
        return $data ?: false;
    }


    //获取订单管理功能模块的info
    public  function getInfoByWhere($id,$where){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id` = "' . $id . '"'.$where;
        $data  = $this->db->table($table)->where($where)->find();
        return $data ?: false;

    }


    // 判断是否为erpnumber
    public function isErpNumber($erp_number,$goodsid=false)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' `erp_number` = "' . $erp_number . '" ';
        if($goodsid){
            $where .= ' and id!="'.$goodsid.'" ';
        }
        $is    = $this->db->table($table)->where($where)->find();
        return $is ? true : false;
    }

    // 更新展现量
    public function updateviewnumber($goodsid){
        $table = DB_PREFIX . self::TABLE;
        $sql = 'update '.$table.' set viewnumber=viewnumber+1 where id='.$goodsid.' limit 1';
        $status = $this->db->dblink->query($sql);
        return true;
    }

}
