<?php

class ModuleWechatPay extends Module{

    // 支付成功回掉
    public function paynotify($storeid){
        $wxConfig = $this->module_store->getWxconfig($storeid);
        defined('WXPAYCONFIG_APPID') || define('WXPAYCONFIG_APPID',$wxConfig['appid']);
        defined('WXPAYCONFIG_MCHID') || define('WXPAYCONFIG_MCHID',$wxConfig['mchid']);
        defined('WXPAYCONFIG_KEY') || define('WXPAYCONFIG_KEY',$wxConfig['key']);
        defined('WXPAYCONFIG_APPSECRET') || define('WXPAYCONFIG_APPSECRET',$wxConfig['appsecret']);
        defined('WXPAYCONFIG_SSLCERT_PATH') || define('WXPAYCONFIG_SSLCERT_PATH',$wxConfig['sslpath_cert']);
        defined('WXPAYCONFIG_SSLKEY_PATH') || define('WXPAYCONFIG_SSLKEY_PATH',$wxConfig['sslpath_key']);
        require_once __DIR__ . "/pay/example/notify.php";
        $notify = new PayNotifyCallBack();
        $notify->Handle(false);
        $data = $notify->return_data;
        return $data;
    }

    /**
     * 退款 
     * $data[
        'transaction_id' => '' //微信订单号(分)
        'out_trade_no' => '' //商户订单号(分)
        'total_fee' => '' //订单总金额(分)
        'refund_fee' => '' //退款金额(分)
     ]
     */
    public function doRefund($data = [],$storeid){
        $wxConfig = $this->module_store->getWxconfig($storeid);
        defined('WXPAYCONFIG_APPID') || define('WXPAYCONFIG_APPID',$wxConfig['appid']);
        defined('WXPAYCONFIG_MCHID') || define('WXPAYCONFIG_MCHID',$wxConfig['mchid']);
        defined('WXPAYCONFIG_KEY') || define('WXPAYCONFIG_KEY',$wxConfig['key']);
        defined('WXPAYCONFIG_APPSECRET') || define('WXPAYCONFIG_APPSECRET',$wxConfig['appsecret']);
        defined('WXPAYCONFIG_SSLCERT_PATH') || define('WXPAYCONFIG_SSLCERT_PATH',$wxConfig['sslpath_cert']);
        defined('WXPAYCONFIG_SSLKEY_PATH') || define('WXPAYCONFIG_SSLKEY_PATH',$wxConfig['sslpath_key']);
        
        require_once __DIR__ . "/pay/lib/WxPay.Api.php";
        $input = new WxPayRefund();
        $total_fee = $data['total_fee'];
        $refund_fee = $data['refund_fee'];
        if(isset($data['transaction_id']) && $data['transaction_id']!=''){
            $transaction_id = $data['transaction_id'];
            $input->SetTransaction_id($transaction_id);
        }elseif(isset($data['out_trade_no']) && $data['out_trade_no']!=''){
            $out_trade_no = $data['out_trade_no'];
            $input->SetOut_trade_no($out_trade_no);
        }else{
            return false;
        }
        $input->SetTotal_fee($total_fee);
        $input->SetRefund_fee($refund_fee);
        $input->SetOut_refund_no(WxPayConfig::MCHID.date("YmdHis"));
        $input->SetOp_user_id(WxPayConfig::MCHID);
        $result = WxPayApi::refund($input);
        return $result;
        // if ($result['return_code'] == 'SUCCESS' && $result['result_code'] == 'SUCCESS') {
        //     return $data;
        // }else{
        //     return false;
        // }

    }

    /**
     */
	public function getJsApiParam($data = [],$storeid){

        $wxConfig = $this->module_store->getWxconfig($storeid);
        defined('WXPAYCONFIG_APPID') || define('WXPAYCONFIG_APPID',$wxConfig['appid']);
        defined('WXPAYCONFIG_MCHID') || define('WXPAYCONFIG_MCHID',$wxConfig['mchid']);
        defined('WXPAYCONFIG_KEY') || define('WXPAYCONFIG_KEY',$wxConfig['key']);
        defined('WXPAYCONFIG_APPSECRET') || define('WXPAYCONFIG_APPSECRET',$wxConfig['appsecret']);
        defined('WXPAYCONFIG_SSLCERT_PATH') || define('WXPAYCONFIG_SSLCERT_PATH',$wxConfig['sslpath_cert']);
        defined('WXPAYCONFIG_SSLKEY_PATH') || define('WXPAYCONFIG_SSLKEY_PATH',$wxConfig['sslpath_key']);
        
		require_once __DIR__ . "/pay/lib/WxPay.Api.php";
        require_once __DIR__ . "/pay/example/WxPay.JsApiPay.php";

        ## openid
        if(!isset($data['wx_openid'])) return false;
        $openId = $data['wx_openid'];

        ## 回调
        if(!isset($data['notify_url'])) return false;
        $notify_url = $data['notify_url'];

        ## 商品标题
        if(!isset($data['goodstitle'])) return false;
        $goodstitle = $data['goodstitle'];

        ## 商品订单号
        if(!isset($data['orderno'])) return false;
        $orderno = $data['orderno'];

        ## 商品金额
        if(!isset($data['total_amount'])) return false;
        $total_amount = $data['total_amount'];


        //①、获取用户openid
        $tools = new JsApiPay();
        $tools->curl_timeout = 3;

        //②、统一下单
        $input = new WxPayUnifiedOrder();
        $input->SetBody($goodstitle);
        $input->SetAttach($orderno);
        $input->SetOut_trade_no($orderno);
        $input->SetTotal_fee($total_amount);

        // $input->SetTotal_fee(1);
        // $input->SetTime_start(date("YmdHis"));
        // $input->SetTime_expire(date("YmdHis", time() + 600));

        $input->SetNotify_url($notify_url);
        $input->SetTrade_type("JSAPI");
        $input->SetOpenid($openId);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);

        //获取共享收货地址js函数参数
        $editAddress = $tools->GetEditAddressParameters();

        return [
            'jsApiParameters'=>$jsApiParameters,
            'editAddress'=>$editAddress,
        ];
	}

}