<?php

class ModuleShopOrder extends Module{

	const TABLE = 'orders';

	// 创建订单号
	public function createNo(){
		$ext = explode('.',sprintf('%.10f',microtime(true)));
		return date('ymdHi').$ext[1];
	}

	// 获取列表
	public function getList($limit,$search=[],$order = 'id desc'){
		$table = DB_PREFIX . self::TABLE;
        $where = [];
        if(isset($search['orderno'])){
            $where[] = ' locate("'.$search['orderno'].'",`orderno`) > 0 ';
        }
        if(isset($search['storeid'])){
            $where[] = ' `storeid` = "'.$search['storeid'].'" ';
        }
        if(isset($search['ordercreatetime'])){
            $where[] = ' `createtime` >= "'.$search['ordercreatetime'][0].'" and `createtime`<= "'.$search['ordercreatetime'][1].'" ';
        }
        if(isset($search['iscreatequan'])){
            $where[] = ' `iscreatequan` = "'.($search['iscreatequan']-1).'" ';
        }
        if(isset($search['wx_openid'])){
        	$where[] = ' `wx_openid` in ("'.implode(',',$search['wx_openid']).'") ';
        }
        if($search['ispay']!=""){
    	    $where[] = ' ispay= "'.$search['ispay'].'" ' ;
    	}
    	$where[] = ' laiyuan= "'.$search['laiyuan'].'" ' ;
        $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
	}
	
	// 创建订单
	public function createOrder($bind){
        $this->module_userapi->logs(json_encode($bind)."bind");
		$table  = DB_PREFIX . self::TABLE;
		$status = $this->db->table($table)->bind($bind)->insert();
		return $status?:false;
	}

	// 获取已购买张数
	public function getBuyNumber($openid,$goodsid){
		$table  = DB_PREFIX . self::TABLE;
		$where = ' `wx_openid`="'.$openid.'" and `goodsid`="'.$goodsid.'" and `ispay`<=1 ';
		$field = ' sum(number) as allnumber ';
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data['allnumber']?:0;
	}

	// 获取订单信息
	public function getInfo($orderno){
		$table  = DB_PREFIX . self::TABLE;
		$where = ' `orderno`="'.$orderno.'" ';
		$orderinfo = $this->db->table($table)->where($where)->find();
		return $orderinfo?:false;
	}

	// 设置订单状态
	public function setStatus($orderno,$status){
		$table  = DB_PREFIX . self::TABLE;
		$where = ' `orderno`="'.$orderno.'" ';
		$bind = ['ispay'=>$status];
		$orderinfo = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
		return $orderinfo?true:false;
	}

	// 设置生成状态
	public function setIsCreateQuan($orderno,$iscreatequan,$iscreatequanmark){
		$table  = DB_PREFIX . self::TABLE;
		$where = ' `orderno`="'.$orderno.'" ';
		$bind = [
			'iscreatequan'=>$iscreatequan,
			'iscreatequanmark'=>$iscreatequanmark,
		];
		$orderinfo = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
		return $orderinfo?true:false;
	}
	// 设置券时间
	public function setquantimes($orderno,$quanstarttime,$quanendtime){
		$table  = DB_PREFIX . self::TABLE;
		$where = ' `orderno`="'.$orderno.'" ';
		$bind = [
			'quanstarttime' => $quanstarttime,
            'quanendtime' => $quanendtime,
		];
		$orderinfo = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
		return $orderinfo?true:false;
	}

	// 支付成功更新状态
	public function paysuccess($orderno,$pay_no,$pay_info){
		$table  = DB_PREFIX . self::TABLE;
		$where = ' `orderno`="'.$orderno.'" ';
		$bind = [
			'ispay' => 1,
			'pay_no' => $pay_no,
			'pay_info' => json_encode($pay_info,JSON_UNESCAPED_UNICODE),
			'pay_time' =>TIME
		];
		$orderinfo = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
		return $orderinfo?true:false;
	}
	
	public function getOrderCountByGroupkey($groupkey) {
	    $table  = DB_PREFIX . self::TABLE;
		$where = ' `groupkey`="'.$groupkey.'" and ispay=1 ';
		$orderlist = $this->db->table($table)->where($where)->selectlist();
		return count($orderlist);
	}
	
	public function getNoPayOrderCountByGroupkey($groupkey) {
	    $table  = DB_PREFIX . self::TABLE;
	    $where = ' `groupkey`="'.$groupkey.'" and ispay=0 ';
	    $orderlist = $this->db->table($table)->where($where)->selectlist();
	    return count($orderlist);
	}
	
	
	//取消拼团未支付的订单
	function cancelOrderByGroupkey($groupkey) {
	    
	    $table  = DB_PREFIX . self::TABLE;
	    
	    //恢复库存
	    $kucunNum = $this->getNoPayOrderCountByGroupkey($groupkey);
	    $goodsdata = $this->getGoodsByGroupkey($groupkey);
	    $goodsid = $goodsdata["id"];
	    $goodswhere = ' id='.$goodsid;
	    $goodsbind = [
	        'store' => $goodsdata['store']+$kucunNum,
	    ];
	    $goodsinfo = $this->db->table(DB_PREFIX . "goods")->bind($goodsbind)->where($goodswhere)->update();
	    
	    

		$where = ' `groupkey`="'.$groupkey.'" and  `ispay`=0  ';
		$bind = [
			'ispay' => 2,
		];
		$orderinfo = $this->db->table($table)->bind($bind)->where($where)->update();
		 
	}
	
	function getGoodsByGroupkey($groupkey){
	    
	    $orderwhere = ' `groupkey`="'.$groupkey.'" ';
	    $orderdata = $this->db->table(DB_PREFIX . "orders")->where($orderwhere)->find();
	    $goodsId = $orderdata["goodsid"];
	    $goodswhere = "id=".$goodsId;
	    $goodsdata = $this->db->table(DB_PREFIX . "goods")->where($goodswhere)->find();
	    return $goodsdata;
	    
	}





}