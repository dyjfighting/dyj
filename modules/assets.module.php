<?php

class ModuleAssets extends Module{

	// 获取积分
	public function getJifen($userid){
		return 5000;
	}

	// 获取余额
	public function getBalance($userid){
		return 100;
	}

	public function sign(){
		$skey = 'fd184d62755746ddac04972e7d37742e';
		//n5Z+X47Whob6V7cd/GFmkw==
		$str = '123';
		$en_data = base64_encode(openssl_encrypt($str, "aes-128-ecb", $skey, OPENSSL_RAW_DATA));
		echo 'AES ecb 128:加密结果:'.$en_data;
		// // echo '<hr>';
		// $de_data = openssl_decrypt(base64_decode($str), "aes-128-ecb", $key, 0,$iv);
		// echo 'AES ecb 128:解密结果:'.$de_data;
		// echo '<hr>';

	}

	// 扣除积分
	public function subJifen(){

	}
	// 扣除余额
	public function subBalance(){
		
	}



}