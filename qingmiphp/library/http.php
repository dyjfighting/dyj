<?php
/* 
 *  [ Core.http ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class http{
    
//    public function get(){
//        
//    }
//    
//    public function post(){
//        
//    }
    public static function curl($url,$data = null,$heraders=null,$time=120){
        $curl = curl_init();
        if(is_array($url)){
            $url=$url[0];
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if($heraders){
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $heraders);
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $time);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        if($httpCode!=200){
            $output=json_encode(['status'=>'Failed','errorno'=>$httpCode]);
        }
        curl_close($curl);
        return $output;
    }

    
    
}
