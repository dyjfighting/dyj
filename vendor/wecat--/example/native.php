<?php
ini_set('date.timezone','Asia/Shanghai');
//error_reporting(E_ERROR);

require_once "../lib/WxPay.Api.php";
require_once "WxPay.NativePay.php";
require_once 'log.php';

//模式一
/**
 * 流程：
 * 1、组装包含支付信息的url，生成二维码
 * 2、用户扫描二维码，进行支付
 * 3、确定支付之后，微信服务器会回调预先配置的回调地址，在【微信开放平台-微信支付-支付配置】中进行配置
 * 4、在接到回调通知之后，用户进行统一下单支付，并返回支付信息以完成支付（见：native_notify.php）
 * 5、支付完成之后，微信服务器会通知支付成功
 * 6、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 */
$notify = new NativePay();


//模式二
/**
 * 流程：
 * 1、调用统一下单，取得code_url，生成二维码
 * 2、用户扫描二维码，进行支付
 * 3、支付完成之后，微信服务器会通知支付成功
 * 4、在支付成功通知中需要查单确认是否真正支付成功（见：notify.php）
 */
$input = new WxPayUnifiedOrder();
$input->SetBody($_GET['body']);
$input->SetAttach($_GET['order_log']);
$input->SetOut_trade_no('pc'.$_GET['order_sn']);
$input->SetTotal_fee($_GET['total_amount']);
// $input->SetTotal_fee(1);
$input->SetTime_start(date("YmdHis"));
$input->SetTime_expire(date("YmdHis", time() + 600));
$input->SetGoods_tag("test");
$input->SetNotify_url("http://www.xrrjdyf.com/wecat/notify");
$input->SetTrade_type("NATIVE");
$input->SetProduct_id($_GET['order_sn']);
$order = WxPayApi::unifiedOrder($input);
$result = $notify->GetPayUrl($input);
$url2 = $result["code_url"];
?>

<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1" /> 
    <title>微信支付-扫一扫</title>
	<link href="/static/shop/assets/imgs/favicon.ico" rel="shortcut icon" type="image/x-icon"/>
	<link rel="stylesheet" href="/static/shop/assets/styles/basic.css" type="text/css" media="screen" charset="utf-8">
	<link rel="stylesheet" href="/static/shop/assets/styles/app.css" type="text/css" media="screen" charset="utf-8">
	<script src="/static/shop/assets/javascripts/jquery.min.js"></script>
	<script src="/static/shop/assets/javascripts/layer.js"></script>
</head>
<body>
	<div class="container">		
		<div class="capacity">
			<div class="clearfix process_list pd10">
				<div class="incenter strong page_header_logo"><h1 class="color1 "><?php echo $_GET['body']?></h1></div>				
			</div>		
		    <div class=" incenter place_order pd30">
		    	<div class="place_order_and mgt20 pdl30 mgl20 ">
					<p class="f14 color3">请扫描下方微信二维码进行支付</p>
					<img alt="扫码支付" src="http://paysdk.weixin.qq.com/example/qrcode.php?data=<?php echo urlencode($url2);?>" style="width:150px;height:150px;margin:50px auto"/>
					<p class="f18 mgb30">支付金额：<b class="color2"><?php echo $_GET['total_amount']/100?></b></p>
		    		<button onclick="location.href='/'" class="place_order_andbutton color8 f16"><i class="iconfont icon-chevron-double-left f18"></i>返回商城</button>
		    	</div>
		    </div>		   
		</div>
	</div>
	<input type="hidden" name="out_trade_no" id="out_trade_no" value="<?php echo 'pc'.$_GET['order_sn'];?>" />
	<script>
		$(function(){
		   setInterval(function(){check()}, 1000);  //5秒查询一次支付是否成功
		})
		function check(){
			var url = "/wecat/notify2";　　//新建
			var out_trade_no = $("#out_trade_no").val();
			var param = {'out_trade_no':out_trade_no};
			$.post(url, param, function(data){
				if(data.status == "Success"){
					ljh_success("订单支付成功,即将跳转...",function (){
						window.location.href = data.url;
					});			
				}
			});
		}
	</script>
</body>
</html>