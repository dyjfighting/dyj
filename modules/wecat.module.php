<?php


class ModuleWecat extends Module{
	
	public function module(){
		// 配置信息
		$module = [
			'pay_name' => '微信支付',
			'pay_desc' => '',
			'pay_code' => 'wecat',
			'pay_config' => '',
			'enabled' => 1,
		];
		$module['pay_module'] = [
			'app_id' 		=>	[
								'name' => '开发者id',
								'desc' => '绑定支付的APPID（必须配置，开户邮件中可查看）',
								'type' => 'text',
							],
			'MCHID' 	=>	[
								'name' => '商户号',
								'desc' => '商户号（必须配置，开户邮件中可查看）',
								'type' => 'text',
							],
			'KEY' 	=>	[
								'name' => '商户支付密钥',
								'desc' => '商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）',
								'type' => 'textarea',
							],
			'APPSECRET' 	=>	[
								'name' => '公众帐号',
								'desc' => '公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置）',
								'type' => 'textarea',
							],
			
		];
		
		return $module;
	}
	
	public function save_config(){
		$data =  $this->db->table(DB_PREFIX."payment")->field("pay_config")->where("pay_code = 'wecat'")->find();
		$pay_config = unserialize($data['pay_config']);
		
		$appid              = $pay_config['app_id'];  
		$mchid              = $pay_config['MCHID'];  
		$key                = $pay_config['KEY'];  
		$appsecret          = $pay_config['APPSECRET'];  
		$sslcert_path       = DIR_VENDOR.'wecat/cert/apiclient_cert.pem';  
		$sslkey_path        = DIR_VENDOR.'wecat/cert/apiclient_key.pem';   
		$curl_proxy_host    = '0.0.0.0';  
		$curl_proxy_port    = 0;  
		$report_levenl      = 1;   
		
		$content = "<?php
		class WxPayConfig  
		{  
				const APPID         = '$appid';  
				const MCHID         = '$mchid';  
				const KEY           = '$key';  
				const APPSECRET     = '$appsecret';  
				const SSLCERT_PATH  = '$sslcert_path';  
				const SSLKEY_PATH   = '$sslkey_path';  
				const CURL_PROXY_HOST = '$curl_proxy_host';//'10.152.18.220';  
				const CURL_PROXY_PORT = $curl_proxy_port;//8080;  
				const REPORT_LEVENL = $report_levenl;  
		}  ";
		
		$datpath = DIR_VENDOR . 'wecat' . DS .'lib'. DS .'WxPay.Config.php';
		if(file_exists($datpath)){
			$io = new IO();
			$io->writefile($datpath, $content);
			return true;
		}else{
			return false;
		}
	}

	// 生成支付页面url
	public function get_pay_url($order){
		$param['order_sn'] = $order['order_sn'];
		$param['order_log'] = $order['order_sn'] . $order['log_id'];
		$param['total_amount'] = $order['order_amount'] * 100;
		$param['body'] = $order['body'];
		$url = $this->base->geturl('wecat/pay',$param);
		return $url;
	}

	public function doRefund($refund){
		require_once DIR_VENDOR."wecat/lib/WxPay.Api.php";
		require_once DIR_VENDOR."wecat/example/log.php";
	
		$logHandler= new CLogFileHandler(DIR_VENDOR."wecat/logs/".date('Y-m-d').'.log');
		$log = Log::Init($logHandler, 15);
		$out_trade_no = $refund["M_OrderNO"];
		$total_fee = $refund["M_Amount"]*100;
		$refund_fee = $refund["M_Refundfee"]*100;
		$out_refund_no = $refund['M_RefundNo'];

		$input = new WxPayRefund();
		
		$input->SetOut_trade_no('wx'.$out_trade_no);
		$input->SetTotal_fee($total_fee);
		$input->SetRefund_fee($refund_fee);
		$input->SetOut_refund_no($out_refund_no);
		$input->SetOp_user_id(WxPayConfig::MCHID);
		$status = WxPayApi::refund($input);
		Log::DEBUG("refund_wx:" . json_encode($status));
		if($status['return_code'] == "SUCCESS" && $status['result_code'] == "SUCCESS"){
			return true;
		}else{
			$input->SetOut_trade_no('pc'.$out_trade_no);
			$status = WxPayApi::refund($input);
			Log::DEBUG("refund_pc:" . json_encode($status));
			return ($status['return_code'] == "SUCCESS" && $status['result_code'] == "SUCCESS")? true : false;
		}
		
	}
}
