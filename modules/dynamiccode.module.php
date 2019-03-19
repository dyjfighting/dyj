<?php

// 获取动态验证码
class ModuleDynamiccode extends Module
{

    private $url = 'http://api.shopoauthapi.weixin.wuerp.com/oauth/BillID/CreateDynamic';

    // 签名
    private function getSign($code, $expires)
    {
        $signmd5 = strtoupper(md5($code . $expires));
        $signmd5 = strtoupper(md5($signmd5));
        return substr($signmd5, 0, 29);
    }

    /**
     * 获取到态验证码
     * code：券编号
     * tp：帐号类型 0-会员卡、1-优惠券
     * expires：动态码过期时间【分钟】 默认为5分钟
     */
    public function getCode($code, $tp, $expires=5)
    {
        $quaninfo = $this->module_quan->getInfo($code);
        if(!$quaninfo){
            return $code;
        }
        $orderno = $quaninfo['orderno'];
        $orderinfo = $this->module_shop_order->getInfo($orderno);
        if (!$orderinfo) {
            return $code;
        }
        $cpnid = $this->module_shop_category->getRelorgan($orderinfo['storeid']);
        if(!$cpnid){
            return $code;
        }
        $sign     = $this->getSign($code, $expires);
        $curl     = curl_init();
        $url      = $this->url;
        $postdata = [
            'CpnID'   => $cpnid,
            'Code'    => $code,
            'Tp'      => $tp,
            'Expires' => $expires,
            'Sign'    => $sign,
        ];
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => "POST",
            CURLOPT_POSTFIELDS     => http_build_query($postdata),
            CURLOPT_HTTPHEADER     => [
                'Content-Type: application/x-www-form-urlencoded',
            ],
        ]);
        $response = curl_exec($curl);
        $err      = curl_error($curl);
        curl_close($curl);
        if (!$err) {
            $data = json_decode($response, true);
            if ($data['Success'] === true && $data['ErrorCode'] == 0) {
                return $data['Data']['Data'];
            }
        }
        return $code;
    }

}
