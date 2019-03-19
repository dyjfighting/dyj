<?php
/* 
 *  [ 系统设置 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleConfig extends Module{
    const table = 'shop_config';
  
	
    public function get_list($groups=null){
		$where = ' disabled = 0 ';
		if (!empty($groups)) {
			foreach ($groups AS $key=>$val) {
				$where .= " AND (id='$val' OR parent_id='$val')";
			}
		}
		$table = DB_PREFIX . self::table;	
        $item_list = $this->db->table($table)->where($where)->order("parent_id,listorder,id asc")->select();
		
		$group_list = array();
		if(count($item_list) > 0){
			// 语言项
			$lang = $this->lang();
			
			foreach($item_list AS $key => $item){
				$pid = $item['parent_id'];
				if ($pid == 0){
					/* 分组 */
					$item['name'] = isset($lang['name'][$item['code']]) ?$lang['name'][$item['code']] : '';
					if ($item['type'] == 'group')
					{
						$group_list[$item['id']] = $item;
					}					
				}else{
					/* 变量 */
					if (isset($group_list[$pid])) {
						
						if ($item['store_range']) {
							$item['store_options'] = explode(',', $item['store_range']);
							
							foreach ($item['store_options'] AS $k => $v) {
								
								$item['display_options'][$k] = isset($lang['store_range'][$item['code']][$v]) ? $lang['store_range'][$item['code']][$v] : $v;
								
							}
						}
						$item['name'] = isset($lang['name'][$item['code']]) ?$lang['name'][$item['code']] : '';
						$item['desc'] = isset($lang['desc'][$item['code']]) ?$lang['desc'][$item['code']] : '';
						$group_list[$pid]['vars'][] = $item;
					}
				}
			}
		}
		// print_r($group_list);
        return $group_list;
    }
	
	public function save(){
		$post = $this->request->post('value');
		
		$arr = [];
		$table = DB_PREFIX . self::table;
		$where = 'disabled = 0';
		$config = $this->db->table($table)->where($where)->select();
		foreach($config as $k=>$v){
			$arr[$v['id']] = $v['value'];
		}
		$res = false;
		foreach ($post as $key => $val) {
			if($arr[$key] != $val) {
				$where = " id = '{$key}' ";
				if($key == 104){
					$value = trim($val,'/').'/';
				}else{
					$value = trim($val);
				}
				$bind = ['value' => $value];
				if($this->db->table($table)->bind($bind)->where($where)->update()){
					$res = true;
				}else{
					$res = false;
					break;
				}
			}else{
				$res = true;
			}
		}

		if($res){
			return [
				'status' => 'Success',
				'message' => '保存成功',
			];
		}else{
			return [
				'status' => 'Success',
				'message' => '保存失败',
			];
		}
	}
	
	
	
	public function lang(){
		/* name */
		// 导航栏
		$lang['name']['menu_base'] 						= "基本设置";
		$lang['name']['menu_email'] 					= "邮箱设置";
		$lang['name']['menu_seo'] 						= "搜索引擎优化";
		$lang['name']['menu_qny'] 						= "七牛云";
		$lang['name']['menu_sms'] 						= "短信设置";
		$lang['name']['menu_other'] 					= "其他设置";
		// 基本设置
		$lang['name']['logo'] 							= "网站logo";
		$lang['name']['web_name'] 						= "网站名称";
		$lang['name']['web_url'] 						= "网站网址";
		$lang['name']['qq'] 							= "QQ";
		$lang['name']['email'] 							= "Email";
		$lang['name']['phone'] 							= "手机";
		$lang['name']['tel'] 							= "电话";
		$lang['name']['company_address'] 				= "公司地址";
		$lang['name']['copyright'] 						= "网站版权信息";
		$lang['name']['icp_number'] 					= "ICP备案号";
		// 邮箱设置
		$lang['name']['mail_server'] 					= "SMTP服务器";
		$lang['name']['mail_ssl'] 						= "安全协议";
		$lang['name']['mail_port'] 						= "SMTP 端口";
		$lang['name']['mail_from'] 						= "发件人地址";
		$lang['name']['mail_username'] 					= "验证用户名";
		$lang['name']['mail_password'] 					= "验证密码";
		$lang['name']['mail_admin'] 					= "管理员邮箱地址";
		// 搜索引擎优化
		$lang['name']['seo_title'] 						= "网页标题";
		$lang['name']['seo_descript'] 					= "网页描述";
		$lang['name']['seo_keywords'] 					= "网页关键词";
		// 七牛云
		$lang['name']['qny_is_open'] 					= "是否开启";
		$lang['name']['qny_host'] 						= "访问域名";
		$lang['name']['qny_accesskey'] 					= "AccessKey";
		$lang['name']['qny_secretkey'] 					= "SecretKey";
		$lang['name']['qny_bucket'] 					= "存储空间名称";
		// 短信设置
		$lang['name']['sms_appkey'] 					= "Appkey";
		$lang['name']['sms_appsecret'] 					= "appSecret";
		$lang['name']['sms_templateid'] 				= "短信模板id";
		// 其他设置
		$lang['name']['upload_maxsize'] 				= "允许上传附件大小（KB）";
		$lang['name']['list_order'] 					= "默认商品列表排序";
		$lang['name']['goods_no_pre'] 					= "商品货号前缀";
		$lang['name']['inv_tax'] 						= "发票税率";
		$lang['name']['inv_content'] 					= "发票内容";
		$lang['name']['order_is_email'] 				= "下订单时是否给客服发邮件";
		$lang['name']['comment_audit'] 					= "商品评价是否需要审核";
		$lang['name']['session_lefttime'] 				= "SESSION过期时间";
		
		
		
		/* desc 帮助说明 */
		$lang['desc']['mail_port'] 						= "SMTP端口号(默认:25)";
		$lang['desc']['mail_username'] 					= "SMTP用户名";
		$lang['desc']['mail_password'] 					= "SMTP密码";
		$lang['desc']['mail_from'] 						= "发送邮件所使用的email地址，邮件内容中的收件人信息就是显示此信息";
		$lang['desc']['qny_host'] 						= "请填写如http://www.baidu.com的域名";
		$lang['desc']['inv_content'] 					= "客户要求开发票时可以选择的内容。例如：办公用品,药品   请用英文逗号(,)分隔";
		$lang['desc']['inv_tax'] 						= "当买家需要发票的时候就要增加<商品金额/优惠后总金额>*<税率>的费用 如输入88 就是88%的税率";
		$lang['desc']['order_is_email'] 				= "邮箱设置中管理员邮箱地址不为空时，该选项有效";
		$lang['desc']['mail_admin'] 					= "用于接收网店相关信息,为空时其他相关设置无效";
		$lang['desc']['upload_maxsize'] 				= "[最大限制不能超过服务器“upload_max_filesize”配置]";
		$lang['desc']['list_order'] 					= "在商品列表页中商品的排序依据条件";

		/* store_range */
		$lang['store_range']['mail_ssl'][0] 			= "默认";
		$lang['store_range']['mail_ssl'][1] 			= "SSL";
		$lang['store_range']['mail_ssl'][2]				= "TLS";
		$lang['store_range']['list_order'][0] 			= "销量";
		$lang['store_range']['list_order'][1] 			= "评价";
		$lang['store_range']['list_order'][2] 			= "价格";
		$lang['store_range']['list_order'][3] 			= "最新上架";
		$lang['store_range']['order_is_email'][0] 		= "否";
		$lang['store_range']['order_is_email'][1] 		= "是";
		$lang['store_range']['comment_audit'][0] 		= "否";
		$lang['store_range']['comment_audit'][1] 		= "是";
		$lang['store_range']['qny_is_open'][0] 			= "不开启";
		$lang['store_range']['qny_is_open'][1] 			= "开启";
		
		return $lang;
	}
	
}






