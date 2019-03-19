<?php 
ini_set('date.timezone','Asia/Shanghai');
// error_reporting(E_ERROR);
// ini_set('display_errors',1);            //错误信息  
// ini_set('display_startup_errors',1);    //php启动错误信息  
// error_reporting(-1);                    //打印出所有的 错误信息  

require_once "../lib/WxPay.Api.php";
require_once "WxPay.JsApiPay.php";
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

//打印输出数组信息
function printf_info($data)
{
    foreach($data as $key=>$value){
        echo "<font color='#00ff55;'>$key</font> : $value <br/>";
    }
}

//①、获取用户openid
$tools = new JsApiPay();
$tools->curl_timeout = 3;
$openId = $tools->GetOpenid();

//②、统一下单
$input = new WxPayUnifiedOrder();
$input->SetBody($_GET['body']);
$input->SetAttach($_GET['order_log']);
$input->SetOut_trade_no('wx'.$_GET['order_sn']);
$input->SetTotal_fee($_GET['total_amount']);
// $input->SetTotal_fee(1);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetNotify_url("http://www.xrrjdyf.com/weixin/wecat/notify");
$input->SetTrade_type("JSAPI");
$input->SetOpenid($openId);
$order = WxPayApi::unifiedOrder($input);


$jsApiParameters = $tools->GetJsApiParameters($order);

//获取共享收货地址js函数参数
$editAddress = $tools->GetEditAddressParameters();

//③、在支持成功回调通知中处理成功之后的事宜，见 notify.php
/**
 * 注意：
 * 1、当你的回调地址不可访问的时候，回调通知会失败，可以通过查询订单来确认支付是否成功
 * 2、jsapi支付时需要填入用户openid，WxPay.JsApiPay.php中有获取openid流程 （文档可以参考微信公众平台“网页授权接口”，
 * 参考http://mp.weixin.qq.com/wiki/17/c0f37d5704f0b64713d5d2c37b468d75.html）
 */
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/> 
	<link rel="stylesheet" href="/static/weixin/assets/styles/basic.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/static/weixin/assets/styles/app.css" type="text/css" media="screen" charset="utf-8">
    <link rel="stylesheet" href="/static/weixin/assets/styles/vendor.css" type="text/css" media="screen" charset="utf-8">
    <title>微信支付-支付</title>
    <script type="text/javascript">
	//调用微信JS api 支付
	function jsApiCall()
	{
		WeixinJSBridge.invoke(
			'getBrandWCPayRequest',
			<?php echo $jsApiParameters; ?>,
			function(res){
				WeixinJSBridge.log(res.err_msg);
				if (res.err_msg == "get_brand_wcpay_request:ok"){
					window.location.href="/weixin/wecat/return_url";
				}		
			}
		);
	}

	function callpay()
	{
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', jsApiCall); 
		        document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
		    }
		}else{
		    jsApiCall();
		}
	}
	</script>
	<script type="text/javascript">
	//获取共享地址
	function editAddress()
	{
		WeixinJSBridge.invoke(
			'editAddress',
			<?php echo $editAddress; ?>,
			function(res){
				var value1 = res.proviceFirstStageName;
				var value2 = res.addressCitySecondStageName;
				var value3 = res.addressCountiesThirdStageName;
				var value4 = res.addressDetailInfo;
				var tel = res.telNumber;
				

			}
		);
	}
	
	window.onload = function(){
		if (typeof WeixinJSBridge == "undefined"){
		    if( document.addEventListener ){
		        document.addEventListener('WeixinJSBridgeReady', editAddress, false);
		    }else if (document.attachEvent){
		        document.attachEvent('WeixinJSBridgeReady', editAddress); 
		        document.attachEvent('onWeixinJSBridgeReady', editAddress);
		    }
		}else{
			editAddress();
		}
	};
	
	</script>
</head>
<body>
    <header class="container hdfor_default isbdbm">
		<div class="row">
			<div class="text">		 
			 <font color="#f00"><b>订单支付</b></font>
			</div>
		</div>
	</header>
	<section class="container" style="padding: 5.6rem 0rem 6rem 0rem;">
		<ul class="isbdtp Set_up_ul">
			<li class="bg8 isbdbm item">
				<div class="Lose_div incenter"> 
					<h3><font color="#9ACD32"><b>支付金额<span style="color:#f00;font-size:50px"><?php echo $_GET['total_amount']/100?></span></b>元</font></h3>
					<div align="center">
						<button style="width:210px; height:50px; border-radius: 15px;background-color:#01c0c8; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
					</div>
				</div>		
			</li>	
		</ul>
	</section>
</body>
</html>