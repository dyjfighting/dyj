<?php

class base{
    private $di;
    public function __construct($di) {
        $this->di = $di;
    }
    public function __get($key) {
            return $this->di->get($key);
    }

    public function __set($key, $value) {
            $this->di->set($key, $value);
    }
    
    /**
    * 判断是否为序列化数组
    * @param $data
    */
    public function is_serialized($data) {
        $data = trim( $data );
        if ( 'N;' == $data )
            return true;
        if ( !preg_match( '/^([adObis]):/', $data, $badions ) )
            return false;
        switch ( $badions[1] ) {
            case 'a' :
            case 'O' :
            case 's' :
                if ( preg_match( "/^{$badions[1]}:[0-9]+:.*[;}]\$/s", $data ) )
                    return true;
                break;
            case 'b' :
            case 'i' :
            case 'd' :
                if ( preg_match( "/^{$badions[1]}:[0-9.E-]+;\$/", $data ) )
                    return true;
                break;
        }
        return false;
    }
    /**
     * 加密
     */
    private function authcode($string, $operation = 'DECODE', $key = 'QingMiPrivateKey1.0', $expiry = 0) { 
            $ckey_length = 4; 
            $key = md5($key); 
            $keya = md5(substr($key, 0, 16)); 
            $keyb = md5(substr($key, 16, 16)); 
            $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length): substr(md5(microtime()), -$ckey_length)) : ''; 
            $cryptkey = $keya.md5($keya.$keyc); 
            $key_length = strlen($cryptkey); 
            $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0).substr(md5($string.$keyb), 0, 16).$string; 
            $string_length = strlen($string); 
            $result = ''; 
            $box = range(0, 255); 
            $rndkey = array(); 
            for($i = 0; $i <= 255; $i++) { 
                $rndkey[$i] = ord($cryptkey[$i % $key_length]); 
            } 
            for($j = $i = 0; $i < 256; $i++) { 
                $j = ($j + $box[$i] + $rndkey[$i]) % 256; 
                $tmp = $box[$i]; 
                $box[$i] = $box[$j]; 
                $box[$j] = $tmp; 
            } 
            for($a = $j = $i = 0; $i < $string_length; $i++) { 
                $a = ($a + 1) % 256; 
                $j = ($j + $box[$a]) % 256; 
                $tmp = $box[$a]; 
                $box[$a] = $box[$j]; 
                $box[$j] = $tmp;
                $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256])); 
            }
            if($operation == 'DECODE') { 
                if((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26).$keyb), 0, 16)) { 
                    return substr($result, 26); 
                } else { 
                    return false; 
                } 
            } else { 
                return $keyc.str_replace('=', '', base64_encode($result)); 
            } 
    }
     /**
     * 数据解密
     */
    public function decrypt($data){
            if(empty($data)) return false;
            $data = strtr($data,['-'=>'+','_'=>'/']);
            $authkey = 'QingMiPrivateKey1.0';
            $newdata = $this->authcode($data,'DECODE',$authkey);
            if(!$newdata) return false;
            if($this->is_serialized($newdata)){
                 return unserialize($newdata);
            }

            return false;
    }
    
    /**
     * 数据加密
     * @param type $data
     */
    public function encrypt($data,$expiry=0){
            if(empty($data)) return false;
            $olddata = serialize($data);
            $authkey = 'QingMiPrivateKey1.0';
            $newdata = $this->authcode($olddata,'ENCODE',$authkey,$expiry);
            return strtr($newdata,['+'=>'-','/'=>'_']);
    }
    
    

    
     /**
     * 获取当前是星期几
     */
    public function getweeks($unixtime=false){
            if(!$unixtime) $unixtime = TIME;
            $weeks = '';
            switch (date('w',$unixtime)){
                case 1 : $weeks = '一';break; 
                case 2 : $weeks = "二";break; 
                case 3 : $weeks = "三";break; 
                case 4 : $weeks = "四";break; 
                case 5 : $weeks =  "五";break; 
                case 6 : $weeks =  "六";break; 
                case 0 : $weeks =  "日";break; 
            }
            return '星期' . $weeks;
    }
    
    /**
     * 验证邮箱
     */
    public function checkemail($email){
            if(preg_match('/^([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+@([a-zA-Z0-9]+[_|\_|\.]?)*[a-zA-Z0-9]+\.[a-zA-Z]{2,3}$/',$email)){
                return true;
            }
            return false;
    }
    
    /**
     * 验证手机号
     */
    public function checkphone($phone){
            if(preg_match('/^(1[3,4,5,6,7,8])+\d{9}$/', $phone)){
                return true;
            }else{
                return false;
            }
    }
    
    /**
     * 验证身份证号码是否正确18位
     * @param type $idnumber
     */
    public function checkidnumber($idnumber){
            if(!$idnumber || strlen($idnumber)!=18){
                return false;
            }
            ## 地区
            $area = [
                11=>'北京',12=>'天津',13=>'河北',14=>'山西',15=>'内蒙古',
                21=>'辽宁',22=>'吉林',23=>'黑龙江',
                31=>'上海',32=>'江苏',33=>'浙江',34=>'安徽',35=>'福建',36=>'江西',37=>'山东',
                41=>'河南',42=>'湖北',43=>'湖南',44=>'广东',45=>'广西',46=>'海南',
                50=>'重庆',51=>'四川',52=>'贵州',53=>'云南',54=>'西藏',
                61=>'陕西',62=>'甘肃',63=>'青海',64=>'宁夏',65=>'新疆',
                71=>'台湾',81=>'香港',82=>'澳门',91=>'国外'
            ];
            if(!isset($area[substr($idnumber,0,2)])){
                return false;
            }
            ##校验码
            $verify_code = substr($idnumber, 17, 1);
            ##加权因子
            $factor = [7,9,10,5,8,4,2,1,6,3,7,9,10,5,8,4,2,1];
            ##校验码对应值
            $verify_data = ['1','0','X','9','8','7','6','5','4','3','2'];
            $total = 0;
            for($i=0;$i<17;$i++){
                $j = substr($idnumber, $i, 1);
                if(!is_numeric($j)){
                    return false;
                }else{
                    $total += (int)$j * $factor[$i];
                }
            }
            $mod = $total % 11;
            if($verify_code == $verify_data[$mod]){  
                return true;  
            }else{  
                return false;  
            }
    }
    
    /**
     * 格式文本框的内容
     * @param type $string
     * @return type
     */
    public function formattextarea($string){
            return strtr(htmlspecialchars($string),array("\r\n"=>'<br />',"\n"=>'<br />',"\t"=>'<br />',' '=>'&nbsp;'));    
    }
    
    /**
     * CODE
     * @param type $verifycode
     */
    public function checkverifycode($code){
            $verifycode = $this->session->get('verifycode');
            if(!$verifycode) return false;
            return (strtolower($verifycode)==strtolower($code))?true:false;
    }
    
    /**
     * 获取config
     */
    public function getconfig($key,$stroagekey='global'){
            static $_data = [];
            if(!isset($_data[$stroagekey])){
                $table = DB_PREFIX . 'setting';
                $where = ' `key` = "'.$stroagekey.'" ';
                $data = $this->db->table($table)->where($where)->find();
                if(!$data) return false;
                $stroagekey_config = $this->decrypt($data['value']);
                if($stroagekey_config){
                    $_data[$stroagekey] = $stroagekey_config;
                }else{
                    return false;
                }
            }
            if(isset($_data[$stroagekey][$key])){
                return $_data[$stroagekey][$key];
            }else{
                return false;
            }
    }
    
    
    /**
     * 设置config
     */
    public function setconfig($data,$stroagekey='global'){
            $table = DB_PREFIX . 'setting';
            $where = ' `key` = "'.$stroagekey.'" ';
            $_data = $this->db->table($table)->where($where)->find();
            if($_data){
                $stroagekey_config = $this->decrypt($_data['value']);
            }else{
                $stroagekey_config = [];
            }
            foreach($data as $key=>$val){
                $stroagekey_config[$key] = $val;
            }
            $configdata = [
                'value'=>$this->encrypt($stroagekey_config)
            ];
            $status = $this->db->table($table)->where($where)->bind($configdata)->update();
            return $status?true:false;
    }
    
    
    /**
     * 汉字转拼音
     * @return string
     */
    function getpinyin($s, $isfirst = false) {
            $s = trim($s);
            $len = strlen($s);
            if($len < 3) return $s;
            static $pinyinstatic = array();
            if(isset($pinyinstatic['dat'])){
                $pinyins = $pinyinstatic['dat'];
            }else{
                    $io = new IO();
                    $pyserialize = DIR_QINGMI . 'library' . DS .'dat'. DS .'pinyin.serialize';
                    if(file_exists($pyserialize)){
                       $pinyins = unserialize($io->readfile($pyserialize));
                       $pinyinstatic['dat'] = $pinyins;
                    }else{
                       $datpath = DIR_QINGMI . 'library' . DS .'dat'. DS .'pinyin.dat';
                       $data = $io->readfile($datpath);
                       $data = explode('|', $data);
                       $pinyins = array();
                       foreach($data as $val) {
                            $t = explode(':', $val);
                            $pinyins[$t[0]] = $t[1];
                       }
                       $io->writefile($pyserialize, serialize($pinyins));
                       $pinyinstatic['dat'] = $pinyins;
                    }  
            }
            $rs = '';
            for($i = 0; $i < $len; $i++) {
                    $o = ord($s[$i]);
                    if($o < 0x80) {
                            if(($o >= 48 && $o <= 57) || ($o >= 97 && $o <= 122)) {
                                    $rs .= $s[$i]; // 0-9 a-z
                            }elseif($o >= 65 && $o <= 90) {
                                    $rs .= strtolower($s[$i]); // A-Z
                            }else{
                                    $rs .= '_';
                            }
                    }else{
                            $z = $s[$i].$s[++$i].$s[++$i];
                            if(isset($pinyins[$z])) {
                                    $rs .= $isfirst ? $pinyins[$z][0] : $pinyins[$z];
                            }else{
                                    $rs .= '_';
                            }
                    }
            }
            return $rs;
    }
    
	/**
	 * 获取url 
	 */
	public function geturl($route,$args=null,$isappname=false,$istoken=true){
			if($args!==false){
				if($istoken==true){
					if(!isset($args['token']) && $this->module_token->get('token')){
						$args['token'] = $this->module_token->get('token');
					}
				}
			}
			$url = WEBDIRNAME;
			if(APP_REWRITE){
				if(!$isappname){ ## 没有指定appname
					$url .= (APPNAME==DEFAULT_APP) ? '/':'/'.APPNAME.'/';
				}else{
					$url .= '/';
				}
			}else{
				$url .= '/index.php?route=';
				if(!$isappname){ ## 没有指定appname
					$url .= APPNAME .'/';
				}
			}
			$url.= $route;
			
			if($args){
				$url .= APP_REWRITE ? '?' : '&';
				if(is_array($args)){
					$url.= http_build_query($args);
				}else{
					$url.= $args;
				}
			}
			return $url;
	}
    
    public function getsuccessurl($message,$token,$url = false){
       $data['message'] = $message;
       if($url) $data['url'] = $url;
       $info = $this->encrypt($data);
       $successurl = $this->geturl('tips/success',['token'=>$token,'message'=>$info]);
       return $successurl;
    }
    
    public function getfailedurl($message,$token,$url = false){
       $data['message'] = $message;
       if($url) $data['url'] = $url;
       $info = $this->encrypt($data);
       $successurl = $this->geturl('tips/failed',['token'=>$token,'message'=>$info]);
       return $successurl;
    }
    
	/**
	 *  提示信息页面跳转
	 *
	 * @param     string  $msg      消息提示信息
	 * @param     string  $gourl    跳转地址,stop为停止
	 * @param     int     $limittime  限制时间
	 * @return    void
	 */
	public function show_msg($msg, $gourl = '', $limittime = 3){
		qingmi::showmsg($msg, $gourl, $limittime);
		if(DEBUG){
			debug::stop();
			debug::message();
		}
		exit;
	}	
	
    
    /**
     * 提示信息
     */
	public function showmsg($message,$token,$url=false,$lefttime=3){
		$data['message'] = $message;
		if($url) $data['url'] = $url;
		$info = $this->encrypt($data);
		
		$url = $this->geturl('message/msg',['token'=>$token,'message'=>$info,'lefttime'=>$lefttime]);
		return $url;
	}
	
	
	
	/**
	 * 获取请求地区
	 * @param $ip
	 * @return 所在位置
	 */
	public function get_address($ip){
		if($ip == '127.0.0.1') return '本地地址';
        return '未知';
		$content = @file_get_contents('http://int.dpool.sina.com.cn/iplookup/iplookup.php?format=json&ip='.$ip);
		$arr = json_decode($content, true);
		if(is_array($arr)){
			return $arr['country'].'-'.$arr['province'].'-'.$arr['city'];
		}else{
			return '未知';
		}
	}
	
    public function left_substr($string, $length = 80, $etc = '...', $code = 'UTF-8'){
        if ($length == 0)
            return '';
        if ($code == 'UTF-8') {
            $pa = "/[\x01-\x7f]|[\xc2-\xdf][\x80-\xbf]|\xe0[\xa0-\xbf][\x80-\xbf]|[\xe1-\xef][\x80-\xbf][\x80-\xbf]|\xf0[\x90-\xbf][\x80-\xbf][\x80-\xbf]|[\xf1-\xf7][\x80-\xbf][\x80-\xbf][\x80-\xbf]/";
        }
        else {
            $pa = "/[\x01-\x7f]|[\xa1-\xff][\xa1-\xff]/";
        }
        preg_match_all($pa, $string, $t_string);
        if (count($t_string[0]) > $length)
            return join('', array_slice($t_string[0], 0, $length)) . $etc;
        return join('', array_slice($t_string[0], 0, $length));
    }
	
	/**
	 * 安全过滤函数
	 *
	 * @param $string
	 * @return string
	 */
	public function safe_replace($string) {
		$string = str_replace('%20','',$string);
		$string = str_replace('%27','',$string);
		$string = str_replace('%2527','',$string);
		$string = str_replace('*','',$string);
		$string = str_replace("'",'',$string);
		$string = str_replace('"','',$string);
		$string = str_replace(';','',$string);
		$string = str_replace('<','&lt;',$string);
		$string = str_replace('>','&gt;',$string);
		$string = str_replace("{",'',$string);
		$string = str_replace('}','',$string);
		$string = str_replace('\\','',$string);
		return $string;
	}	
	
	
	/**
	 * 获取请求ip
	 * @return ip地址
	 */
	public function getip(){
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$ip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$ip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return preg_match ( '/[\d\.]{7,15}/', $ip, $matches ) ? $matches [0] : '127.0.0.1';
	}
	
	/**
	 * 格式化商品价格
	 *
	 * @access  public
	 * @param   float   $price  商品价格
	 * @return  string
	 */
	public function price_format($price, $change_price = true){
		if($price==='')
		{
			$price=0;
		}
		if ($change_price)
		{
			/* 从商店配置中取值 */
			$system_price_format = 0;
			/* 从商店配置中取值 */
			switch ($system_price_format)
			{
				case 0:
					$price = number_format($price, 2, '.', '');
					break;
				case 1: // 保留不为 0 的尾数
					$price = preg_replace('/(.*)(\\.)([0-9]*?)0+$/', '\1\2\3', number_format($price, 2, '.', ''));

					if (substr($price, -1) == '.')
					{
						$price = substr($price, 0, -1);
					}
					break;
				case 2: // 不四舍五入，保留1位
					$price = substr(number_format($price, 2, '.', ''), 0, -1);
					break;
				case 3: // 直接取整
					$price = intval($price);
					break;
				case 4: // 四舍五入，保留 1 位
					$price = number_format($price, 1, '.', '');
					break;
				case 5: // 先四舍五入，不保留小数
					$price = round($price);
					break;
			}
		}
		else
		{
			$price = number_format($price, 2, '.', '');
		}

		return sprintf('￥%s元', $price);
	}
	
	/**
	* 转换字节数为其他单位
	* @param	string	$filesize	字节大小
	* @return	string	返回大小
	*/
	public function sizecount($filesize) {
		if ($filesize >= 1073741824) {
			$filesize = round($filesize / 1073741824 * 100) / 100 .' GB';
		} elseif ($filesize >= 1048576) {
			$filesize = round($filesize / 1048576 * 100) / 100 .' MB';
		} elseif($filesize >= 1024) {
			$filesize = round($filesize / 1024 * 100) / 100 . ' KB';
		} else {
			$filesize = $filesize.' Bytes';
		}
		return $filesize;
	}
	
	
		
	// $key 没值返回全部配置信息, $key 可以是字符串或者数组
	public function get_config($key = false){
		$table = DB_PREFIX . 'shop_config';
		$config = $this->db->table($table)->field("code,value")->select();
		$arr = [];
		foreach($config as $k=>$v){
			$arr[$v['code']] = $v['value'];
		}
		// 对数值型设置处理
		$arr['email_port'] = !empty($arr['email_port']) ? intval($arr['email_port']) :'';
		$arr['comment_audit'] = !empty($arr['comment_audit']) ? intval($arr['comment_audit']) :0;
		$arr['order_is_email'] = !empty($arr['order_is_email']) ? intval($arr['order_is_email']) :0;
		$arr['list_order'] = !empty($arr['list_order']) ? intval($arr['list_order']) :0;
		$arr['inv_tax'] = !empty($arr['inv_tax']) ? floatval($arr['inv_tax']) :0;
	
		if($key){
			
			if(is_array($key)){
				$temp = [];
				foreach($key as $v){
					$temp[$v] = isset($arr[$v]) ? $arr[$v] : false;
				}
				return $temp;
			}else{
				return isset($arr[$key]) ? $arr[$key] : false;
			}
			
		}else{
			return $arr;
		}
	}
	
	/**
	 * 判断是否为手机访问
	 * @return bool
	 */
	public function ismobile(){ 
		// 如果有HTTP_X_WAP_PROFILE则一定是移动设备
		if (isset ($_SERVER['HTTP_X_WAP_PROFILE'])){
			return true;
		} 
		// 如果via信息含有wap则一定是移动设备,部分服务商会屏蔽该信息
		if (isset ($_SERVER['HTTP_VIA'])){ 
			// 找不到为flase,否则为true
			return stristr($_SERVER['HTTP_VIA'], 'wap') ? true : false;
		} 
		// 脑残法，判断手机发送的客户端标志,兼容性有待提高
		if (isset ($_SERVER['HTTP_USER_AGENT'])){
			$clientkeywords = array ('nokia','sony','ericsson','mot','samsung','htc','sgh','lg','sharp','sie-','philips','panasonic','alcatel','lenovo','iphone','ipod','blackberry','meizu','android','netfront','symbian','ucweb','windowsce','palm','operamini','operamobi','openwave','nexusone','cldc','midp','wap','mobile'); 
			// 从HTTP_USER_AGENT中查找手机浏览器的关键字
			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))){
				return true;
			} 
		} 
		// 协议法，因为有可能不准确，放到最后判断
		if (isset ($_SERVER['HTTP_ACCEPT'])){ 
			// 如果只支持wml并且不支持html那一定是移动设备
			// 如果支持wml和html但是wml在html之前则是移动设备
			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))){
				return true;
			} 
		} 
		return false;
	} 
	
	public function upload_url($pid,$t = 1,$n = 1,$callbackname=false){
		if($this->session->get('admin_id')){
			$param['userid'] = intval($this->session->get('admin_id'));
		}elseif($this->session->get('user_id')){
			$param['userid'] = intval($this->session->get('user_id'));
		}
		
		if($this->session->get('admin_username')){
			$param['username'] = trim($this->session->get('admin_username'));
		}elseif($this->session->get('user_username')){
			$param['username'] = trim($this->session->get('user_username'));
		}
		$param['n'] = $n;
		$param['t'] = $t;
		$param['appname'] = APPNAME;
		if($pid){
			$param['pid'] = $pid;
		}
        if($callbackname){
            $param['_callback'] = $callbackname;
        }
		return $this->base->geturl('api/upload_box',$param,'api');
	}
	
	/**
	 * 编辑器
	 * 
	 * @param $name name
	 * @param $val 默认值
	 * @param $style 样式
	 */
	public function editor($name = 'content', $val = '', $style='',$initialFrameWidth='100%',$initialFrameHeight=500) {	
		if($this->session->get('admin_id')){
			$param['userid'] = intval($this->session->get('admin_id'));
		}elseif($this->session->get('user_id')){
			$param['userid'] = intval($this->session->get('user_id'));
		}
		
		if($this->session->get('admin_username')){
			$param['username'] = trim($this->session->get('admin_username'));
		}elseif($this->session->get('user_username')){
			$param['username'] = trim($this->session->get('user_username'));
		}
		$param['appname'] = APPNAME;
		
		$string = '';
		if(!defined('EDITOR')) {
			define('EDITOR', 1);
			$string .= '<link href="'.STATIC_PATH.'plugin/editor/themes/default/css/umeditor.css" type="text/css" rel="stylesheet">';
			$string .= '<script>window.UMEDITOR_HOME_URL = "'.STATIC_PATH.'plugin/editor/"</script>';
			$string .= '<script type="text/javascript" charset="utf-8" src="'.STATIC_PATH.'plugin/editor/editor.config.js"></script>';
			$string .= '<script type="text/javascript" charset="utf-8" src="'.STATIC_PATH.'plugin/editor/editor.min.js"></script>';
		}
		$imageUrl = $this->base->geturl('api/upload_editor',$param,'api');
		$string .= '<script id="'.$name.'" type="text/plain" style="'.$style.'" name="'.$name.'">'.$val.'</script>';
		$string .= '<script type="text/javascript">var um = UM.getEditor("'.$name.'",{imageUrl:"'.$imageUrl.'",imagePath:"",textarea:"'.$name.'",initialFrameWidth:"'.$initialFrameWidth.'",initialFrameHeight:"'.$initialFrameHeight.'"});</script>';
		
		return $string;
	}
	
}

