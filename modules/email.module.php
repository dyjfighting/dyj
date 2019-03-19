<?php
/* 
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleEmail extends Module{
    
	// 发送邮件 $files 二维数组 array(['path'=>'1.jpg','name'=>'第一张'])
	public function send_email($email,$subject,$body,$files=array()){
		$shop_config = $this->base->get_config();
		
		if($shop_config['mail_ssl']){
			if($shop_config['mail_ssl'] == 1){
				$mail_ssl = 'ssl';
			}elseif($shop_config['mail_ssl'] == 2){
				$mail_ssl = 'tls';
			}else{
				$mail_ssl = 'ssl';
			}
		}else{
			$mail_ssl = 'ssl';
		}
		
		spl_autoload_functions();
		$phpmailer = new phpmailer();
		$phpmailer->IsSMTP();                 // 启用SMTP      
		$phpmailer->SMTPSecure = $mail_ssl; 	// 安全协议 
		$phpmailer->Port = $shop_config['mail_port'] ? intval($shop_config['mail_port']) : 25; // SMTP服务器的端口号 
		$phpmailer->Host = trim($shop_config['mail_server']);      //smtp服务器的名称（这里以126邮箱为例）       
		$phpmailer->SMTPAuth = true;         //启用smtp认证       
		$phpmailer->Username = trim($shop_config['mail_username']);   //你的邮箱名       
		$phpmailer->Password = trim($shop_config['mail_password']);      //邮箱密码      
		$phpmailer->From = trim($shop_config['mail_from']);            //发件人地址（也就是你的邮箱地址）       
		$phpmailer->FromName = trim($shop_config['web_name']);              //发件人姓名     
		$phpmailer->AddAddress($email,"name"); 
		//$mail->AddReplyTo("service@taiji2016.com", "name");    //回复地址(可填可不填)       
		//$phpmailer->WordWrap = 50;                    //设置每行字符长度      
		if(!empty($files)){
			foreach($files as $k=>$v){
				$phpmailer->AddAttachment($v['path'],$v['name']);   // 添加附件,并指定名称 
			}
		}      
		$phpmailer->IsHTML(true);                 // 是否HTML格式邮件      
		$phpmailer->CharSet="utf-8";    //设置邮件编码      
		$phpmailer->Subject = $subject;          //邮件主题      
		$phpmailer->Body    = $body;      //邮件内容      
		// $phpmailer->AltBody = "请将该网址复制并粘贴至新的浏览器窗口中访问激活账号。　".$url; //邮件正文不支持HTML的备用显示      
				
		$result = $phpmailer->Send();
		if($result) {
			$jsondata = [
				'status'=>'Success',
				'message'=>'邮件已发送',
			];
		}else{     
			$jsondata = [
				'status'=>'error',
				'message'=>'邮件发送失败',
				'erroinfo' => $phpmailer->ErrorInfo
			];
		}
		return $jsondata;
	}	
	

	
	// 邮件html内容
	public function html_body($username,$url){
		$time = date("Y年m月d日 H时i分",TIME);
		$logo = $this->base->get_config('logo');
		$web_name = $this->base->get_config('web_name');
		return  <<<EOF
			<table width="620" cellpadding="0" cellspacing="0">
				<tr>
					<td style="border-bottom:3px #EA5504 solid">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td style="padding:10px 0 5px 20px;"><img src="{$logo}" alt="{$web_name}" /></td>
								<td align="right" valign="bottom" style="padding-right:20px; padding-bottom:5px; font-size:12px;"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="padding:20px; line-height:24px; font-size:12px;">
						<strong>亲爱的<span style="color:#004098">{$username}</span>：</strong><br  />	
						&nbsp;&nbsp;&nbsp; 您好！感谢您于{$time}申请了验证邮箱，您可以点击以下链接来完成验证邮箱：
						<br />
						<a href="{$url}" target="_blank">{$url}</a>
						<br />
						为保障您的帐号安全，请在24小时内点击该链接。<br />
						如果点击链接遇到问题，请复制链接到浏览器地址栏访问。
					</td>
				</tr>
			</table>
EOF;
	}

		// 邮件html内容
	public function html_body_order($order_sn){
		$logo = $this->base->get_config('logo');
		$web_name = $this->base->get_config('web_name');
		return  <<<EOF
			<table width="620" cellpadding="0" cellspacing="0">
				<tr>
					<td style="border-bottom:3px #EA5504 solid">
						<table width="100%" cellpadding="0" cellspacing="0">
							<tr>
								<td style="padding:10px 0 5px 20px;"><img src="{$logo}" alt="{$web_name}" /></td>
								<td align="right" valign="bottom" style="padding-right:20px; padding-bottom:5px; font-size:12px;"></td>
							</tr>
						</table>
					</td>
				</tr>
				<tr>
					<td style="padding:20px; line-height:24px; font-size:12px;">
						<strong>新的订单！</strong><br  />	
						&nbsp;&nbsp;&nbsp; 订单号 ：{$order_sn}
					</td>
				</tr>
			</table>
EOF;
	}
}






