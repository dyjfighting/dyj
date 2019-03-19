<?php

class ModuleUserapi extends Module{

	private function getLoginUrl(){
		$storeid = $this->module_store->getStoreId();
		if(!$storeid){
			return false;
		}
        $categoryinfo = $this->module_shop_category->get_info($storeid);
        $wxpayconfig = json_decode($categoryinfo['wxpayconfig'],true);
        if(isset($wxpayconfig['loginurl']) && strlen($wxpayconfig['loginurl'])>5){
        	$tcp = IS_HTTPS?'https://':'http://';
        	// $backurl =  $tcp. $_SERVER['HTTP_HOST'].'/userapi/callbackinfo';
        	$backurl =  $tcp. $_SERVER['HTTP_HOST'].$this->base->geturl('userapi/callbackinfo',false);
        	$url = $wxpayconfig['loginurl'] . '/Oauth/WxLogin?backUrl=' .urlencode($backurl);
        	return $url;
        }else{
        	return false;
        }
		// // $tcp = IS_HTTPS?'https://':'http://';
		// $backurl =  $tcp. $_SERVER['HTTP_HOST'].'/userapi/callbackinfo';
		// $url = 'http://pxbl.vip.wuerp.com/Oauth/WxLogin?backUrl='.urlencode($backurl);
		// return $url;
	}

	private function getRegisterUrl(){
		$storeid = $this->module_store->getStoreId();
		if(!$storeid){
			return false;
		}
//		if($storeid==374){
//            $tcp = IS_HTTPS?'https://':'http://';
//            $backurl =  $tcp. $_SERVER['HTTP_HOST'].$this->base->geturl('userapi/callbakcregist',false);
//            echo $backurl;die;
//            $url =  'http://ghbllp.vip.wuerp.com/Member/Register?backUrl=' .urlencode($backurl);
//        }
        $categoryinfo = $this->module_shop_category->get_info($storeid);
        $wxpayconfig = json_decode($categoryinfo['wxpayconfig'],true);
        if(isset($wxpayconfig['register']) && strlen($wxpayconfig['register'])>5){
        	$tcp = IS_HTTPS?'https://':'http://';
            // $backurl =  $tcp. $_SERVER['HTTP_HOST'].'/userapi/callbakcregist';
            $backurl =  $tcp. $_SERVER['HTTP_HOST'].$this->base->geturl('userapi/callbakcregist',false);
        	$url = $wxpayconfig['register'] . '/Register/Index?backUrl=' .urlencode($backurl);
        	return $url;
        }else{
        	return false;
        }
		// $tcp = IS_HTTPS?'https://':'http://';
		// $backurl =  $tcp. $_SERVER['HTTP_HOST'].'/userapi/callbakcregist';
		// $url = 'http://pxbl.vip.wuerp.com/Register/Index?backUrl='.urlencode($backurl);
		// return $url;
	}

	public function register($isreturn=false){
		if(IS_POST){
			$uri = $_SERVER['HTTP_REFERER'];
		}else{
			$uri = $_SERVER['REQUEST_URI'];
		}
		$store_id = $this->module_store->getStoreId();
		$this->session->set('storeid',$store_id);
		$this->session->set('uri',$uri);
		$this->session->clear('openid');
		$this->session->clear('userinfo');
		$url = $this->getRegisterUrl();
		if(!$url){
			exit();
		}
		if($isreturn){
			return $url;
		}else{
			$this->response->url($url);
		}
	}
    public function login($isreturn=false){
        if(IS_POST){
            $uri = $_SERVER['HTTP_REFERER'];
        }else{
            $uri = $_SERVER['REQUEST_URI'];
        }
        $store_id = $this->module_store->getStoreId();
        $this->session->set('storeid',$store_id);
        $this->session->set('uri',$uri);
        $this->session->clear('openid');
        $this->session->clear('userinfo');
        $url = $this->getLoginUrl();
        if(!$url){
            exit();
        }
        if($isreturn){
            return $url;
        }else{
            $this->response->url($url);
        }
    }

	public function isLogin(){
		$user_agent = $_SERVER['HTTP_USER_AGENT'];
		if (strpos($user_agent, 'MicroMessenger') === false) {

		} else {
			$user = $this->getUserinfo();
			if($user['openid']){
				return $user;
			}else{
				if(IS_POST){
					$uri = $_SERVER['HTTP_REFERER'];
				}else{
					$uri = $_SERVER['REQUEST_URI'];
				}
				// $uri = $_SERVER['REQUEST_URI'];
				$store_id = $this->module_store->getStoreId();
				$this->session->set('storeid',$store_id);
				$this->session->set('uri',$uri);
				$this->session->clear('openid');
				$this->session->clear('userinfo');
				// if($this->base->getIP()=='110.184.183.158'){
				// 	print_r($_SESSION);
				// 	exit();
				// }
				$url = $this->getLoginUrl();
				if(!$url){
					exit();
				}
				$this->response->url($url);
			}
		}
	}

	// 解析用户信息
	public function descrypt($g,$wxinfo){
		$jsondata = $this->module_crmaes->decryptUserinfo($g);
		$userinfo = json_decode($jsondata,true);
		if(!isset($userinfo['CrdFaceID'])){
			return false;
		}
		if($wxinfo){
			$wxinfo = json_decode($wxinfo,true);
		}
		$status = $this->module_member_user->add($userinfo,$wxinfo);
		return $status?$userinfo:false;
	}

	/**
	 * 获取用户信息
	 */
	public function getUserinfo(){
		 // $store_id = $this->module_store->getStoreId();
		 // if(!$store_id){
			// return [
			// 	'openid'=>false,
			// 	'userinfo'=>false,
			// ];
		 // }
		 $openid = $this->session->get('openid');
		 if(!$openid){
		 	$openid = false;
		 }
		 if($openid){
		 	if($this->module_member_user->isopenid($openid)){
		 		$userinfo = $this->session->get('userinfo');
				 if(!$userinfo){
				 	$userinfo = true;
				 }
		 	}else{
		 		$userinfo = false;
		 	}
		 }else{
		 	$userinfo = false;
		 }
		 return [
		 	'openid'=>$openid,
		 	'userinfo'=>$userinfo,
		 ];
	}

	//增加用户积分
	public function addUserJifen($jifen=0,$_jifenstatus=0){
	    $status=false;
	    $data="";
	    $store_id = $this->module_store->getStoreId();
	    $userapi = $this->isLogin();
	    $openid = $userapi['openid'];
	    if(!$userapi['openid']){
	        $data = "请登录";
	        $result = array('status'=>$status,'data'=>$data);
	        return $result;
	    }
	    $userinfo = $userapi['userinfo'];
	    if(!$userinfo){
	       $data = "用户不存在";
	       $result = array('status'=>$status,'data'=>$data);
	       return $result;
	    }

	    //判断用户是否可以领取积分
	    $isadd = $this->db->table("blwx_log_addjifen")->where("openid='{$openid}' and merchant_id='{$store_id}' and status='{$_jifenstatus}'")->find();
	    if($isadd)
	    {
	        $data = "该活动已经领取，不能重复领取";
	        $result = array('status'=>$status,'data'=>$data);
	        return $result;
	    }
	    $jifentitle = $this->module_shop_category->getName($store_id).'赠送积分';
	    $addJifenStatus = $this->module_crm->addJifen($jifen,$jifentitle);
	    if($addJifenStatus)
	    {
	        //增加log记录
	        $addData = [
	            'openid' => $openid,
	            'merchant_id' => $store_id,
	            'status' => $_jifenstatus, // 类型
	        ];
	        $this->db->table("blwx_log_addjifen")->bind($addData)->insert();

	        $status=true;
	        $data = "领取成功";
	        $result = array('status'=>$status,'data'=>$data);
	    }else{
	        $data = "领取积分失败";
	       $result = array('status'=>$status,'data'=>$data);
	    }
	    return $result;

	}
    public function  logs($content){
        $data=[
            'text'=>$content,
            'time'=>date('Y-m-d H:i:s'),
        ];
        $this->db->table("blwx_logs")->bind($data)->insert();
    }



}