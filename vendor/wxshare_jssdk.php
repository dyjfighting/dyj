<?php

/**

 * Created by PhpStorm.

 * User: DELL

 * Date: 2018/2/5

 * Time: 16:39

 */

class wxshare_jssdk
{

    private $appId;

    private $appSecret;

    public $url;

    public $debugs;

    public function __construct($appId, $appSecret,$url="")
    {

        $this->appId = $appId;

        $this->appSecret = $appSecret;
        $this->url = $url;
    }

    public function getSignPackage()
    {


        $jsapiTicket = $this->getJsApiTicket();

        //var_dump($jsapiTicket);die();
        // 注意 URL 一定要动态获取，不能 hardcode.

        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";

        //$url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $url = $this->url;

        $timestamp = time();

        $nonceStr = $this->createNonceStr();

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序

        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(

            "appId"     => $this->appId,

            "nonceStr"  => $nonceStr,

            "timestamp" => $timestamp,

            "url"       => $url,

            "signature" => $signature,

            "rawString" => $string,

        );

        //var_dump($signPackage);die();
        return $signPackage;

    }

    private function createNonceStr($length = 16)
    {

        $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";

        $str = "";

        for ($i = 0; $i < $length; $i++) {

            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);

        }

        return $str;

    }

    private function getJsApiTicket()
    {

        $accessToken = $this->getAccessToken();

       // var_dump($accessToken);die();
        // 如果是企业号用以下 URL 获取 ticket

        // $url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?access_token=$accessToken";
        $data = json_decode(file_get_contents(__DIR__."/access_Ticket_".$this->appId.".json"));

        if (empty($data) || $data->expire_time < time()) {
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";

            $res = json_decode($this->httpGet($url));

            $ticket = $res->ticket;
            if ($ticket) {
                // $data->expire_time = time() + ($res->expires_in - 100);
                $data->expire_time = time() + 250;

                $data->ticket = $ticket;

                $fp = fopen(__DIR__."/access_Ticket_".$this->appId.".json", "w");

                fwrite($fp, json_encode($data));

                fclose($fp);

            }
        }else{
            $ticket = $data->ticket;
        }
        
        return $ticket;

        // $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$accessToken";

        // $res = json_decode($this->httpGet($url));

        // $ticket = $res->ticket;

        // return $ticket;

    }

    private function getAccessToken()
    {

        // access_token 应该全局存储与更新，以下代码以写入到文件中做示例


        // 如果是企业号用以下URL获取access_token
        $data = json_decode(file_get_contents(__DIR__."/access_token_".$this->appId.".json")); //建立一个文件把第一次请求到的token对象放进去

        if (empty($data) || $data->expire_time < time()) {

            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $this->appId . '&secret=' . $this->appSecret;

            $res = json_decode($this->httpGet($url));
           

            $access_token = $res->access_token;
            if ($access_token) {

                // $data->expire_time = time() + ($res->expires_in - 100);
                $data->expire_time = time() + 250;

                $data->access_token = $access_token;

                $fp = fopen(__DIR__."/access_token_".$this->appId.".json", "w");

                fwrite($fp, json_encode($data));

                fclose($fp);

            }

        } else {


            $access_token = $data->access_token;

        }

        return $access_token;

    }

    private function httpGet($url)
    {

        $curl = curl_init();

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        curl_setopt($curl, CURLOPT_TIMEOUT, 500);

        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);

        curl_setopt($curl, CURLOPT_URL, $url);

        $res = curl_exec($curl);

        curl_close($curl);

        return $res;

    }

}
