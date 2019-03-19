<?php

class ModuleStore extends Module{


	/**
	 * 获取门店
	 */
	public function getStore(){
		$store = $this->request->get('store');
		$storeid = $this->base->decrypt($store);
		$s_storeid = $this->session->get('storeid');

		if(!$storeid && !$s_storeid){
			return false;
		}

		if($storeid == $s_storeid){
			return $store;
		}

		if(!$storeid && $s_storeid){
			return $this->base->encrypt($s_storeid);
		}

		if($storeid != $s_storeid){
			$this->setStore($storeid);
			$this->session->set('openid',false);
			$this->session->set('userinfo',false);
			return $store;
		}

		return false;
	}
	
	/**
	 * 内容管理
	 */
	public function getStoreId(){
		$this->session->sessioninit();
		$store = $this->getStore();
		if(!$store) return false;
		$storeid = $this->base->decrypt($store);
		return $storeid?:false;
	}

	/**
	 * 获取微信支付配置
	 */
	public function getWxconfig($storeid){
		if(!$storeid){
			return false;
		}
        $categoryinfo = $this->module_shop_category->get_info($storeid);
        $wxpayconfig = json_decode($categoryinfo['wxpayconfig'],true);
        return $wxpayconfig;
	}

	/**
	 * 
	 */
	public function getCuXiaoId($storeid){
		$data = $this->module_shop_category->getsublist($storeid);
		$ids = [];
		foreach($data as $val){
			if($val['storetype']==1){
				$ids[] = $val['id'];
			}
		}
		if($ids){
			return $ids;
		}else{
			return false;
		}
	}
	
	/**
	 * 设置门店
	 */
	public function setStore($storeid){
		$this->session->set('storeid',$storeid);
		return true;
		// static $isset;
		// if(is_null($isset)){
		// 	$storeid = $this->base->encrypt($storeid);
		// 	$this->cookie->set('store',$storeid,604800);
		// 	$isset = true;
		// }
		// return true;
	}
	
	public function getStoreCategory($storetype){
		$store = $this->getStore();
		$storeid = $this->base->decrypt($store);
		if(!$storeid){
			return false;
		}
		$data = $this->module_shop_category->getsublist($storeid);
		$ids = [];
		foreach($data as $val){
			if($val['storetype']==$storetype){
				$temp = $this->module_shop_category->getsubid($val['id']);
				$ids[] = $val['id'];
				foreach($temp as $vals){
					$ids[] = $vals['id'];
				}
			}
		}
		if($ids){
			return $ids;
		}else{
			return false;
		}
	}

}