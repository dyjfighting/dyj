<?php

class ModuleCrm extends Module
{

    ## crm请求地址
    const URL = 'http://118.114.241.104:9923/OpenCrm.asmx';
    ## crm appkey
    const APPKEY = '0001';

    ## 券模板编号
    const DMNO = '0000171234';

    // 获取积分
    public function getJifen($openid){
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/IntegralQuery';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return 0;
        }
        $cardID = $userinfo['cardid'];
        $sign = substr(md5(strtoupper(md5($appKey.$cardID))),0,-3);
        $data = [
          'appKey'=>$appKey,
          'cardID'=>$cardID,
          'Sign'=>$sign,
        ];
        $data = http_build_query($data);
        $result = $this->_curl($url,$data);
        $xmls = simplexml_load_string($result);
        if(!$xmls) return 0;
        $xmljson = json_encode($xmls);
        $xml     = json_decode($xmljson, true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='True'){
            return $xmldata['data']['integral'];
        }else{
            return 0;
        }
    }

    // 获取余额
    public function getBalance($openid){
        return 0;
        // $url = 'http://118.114.241.104:9923/OpenCrm.asmx/AccountQuery';
        // $appKey = self::APPKEY;
        // $userinfo = $this->module_member_user->getInfo($openid);
        // if(!$userinfo){
        //     return false;
        // }
        // $cardID = $userinfo['cardid'];
        // $cardID = $userinfo['cardid'];
        // $sign = substr(md5(strtoupper(md5($appKey.$cardID))),0,-3);
        // $data = [
        //   'appKey'=>$appKey,
        //   'cardID'=>$cardID,
        //   'Sign'=>$sign,
        // ];
        // $data = http_build_query($data);
        // $result = $this->_curl($url,$data);
        // echo $result;
        // exit();
        // $xmls = simplexml_load_string($result);
        // if(!$xmls) return false;
        // $xmljson = json_encode($xmls);
        // $xml     = json_decode($xmljson, true);
        // $xmldata    = json_decode($xml[0], true);
        // if($xmldata['success']=='True'){
        //     return $xmldata['data']['integral'];
        // }
    }

    // 扣除积分
    public function subJifen(){
        return true;
    }

    // 增加积分 
    public function addJifen($openid,$balance){
        
    }

    // 增加余额
    public function addBalance($openid,$balance){
        
    }

    // 扣除余额
    public function subBalance(){
        return true;
    }

    ## 扣积分、余额、生成券等操作
    public function paysuccess($orderno,$isonlinepay=false){
        $orderinfo = $this->module_shop_order->getInfo($orderno);
        if(!$orderinfo){
            return false;
        }
        // 扣除余额
        if($orderinfo['subbalance']>0){
            $status = $this->subBalance();
            if(!$status){
                return false;
            }
        }
        // 扣除积分
        if($orderinfo['subjifen']>0){
            $status = $this->subJifen();
            if(!$status){
                return false;
            }
        }
        // 更新订单状态
        if(!$isonlinepay){
            $status = $this->module_shop_order->setStatus($orderno,1);
            if(!$status){
                return false;
            }
        }
        // 生成电子券
        $this->createCouponGrant($orderno);
        return true;
    }

    // 生成电子券
    public function createCouponGrant($orderno)
    {
        $orderinfo = $this->module_shop_order->getInfo($orderno);
        if (!$orderinfo) {
            return false;
        }
        $userinfo  = $this->module_member_user->getInfo($orderinfo['wx_openid']);
        $goods  = $this->module_shop_goods->getInfo($orderinfo['goodsid']);
        if(!$goods || strlen($goods['erp_number']) < 1){
            return false;
        }
        $Paydetail = [];
        if (in_array($orderinfo['buytype'], [1, 2])) {
            if ($orderinfo['subbalance'] > 0) {
                $Paydetail[] = ['Paytype' => '余额', 'Paymoney' => $orderinfo['subbalance'], 'PayRemark' => '余额使用' . $orderinfo['subbalance'] . '元'];
            }
            if ($orderinfo['subjifen'] > 0) {
                $Paydetail[] = ['Paytype' => '积分', 'Paymoney' => $orderinfo['subjifen'], 'PayRemark' => '积分' . $orderinfo['subjifen'] . '抵扣' . $orderinfo['jifen'] . '元'];
            }
            if ($orderinfo['payprice'] > 0) {
                $Paydetail[] = ['Paytype' => '微信', 'Paymoney' => $orderinfo['payprice'], 'PayRemark' => '微信在线支付' . $orderinfo['payprice'] . '元'];
            }
        } elseif (in_array($orderinfo['buytype'], [3])) {
            if ($orderinfo['subbalance'] > 0) {
                $Paydetail[] = ['Paytype' => '余额', 'Paymoney' => $orderinfo['subbalance'], 'PayRemark' => '余额使用' . $orderinfo['subbalance'] . '元'];
            }
            if ($orderinfo['subjifen'] > 0) {
                $Paydetail[] = ['Paytype' => '积分', 'Paymoney' => $orderinfo['subjifen'], 'PayRemark' => '积分使用' . $orderinfo['subjifen'] . '积分'];
            }
            if ($orderinfo['payprice'] > 0) {
                $Paydetail[] = ['Paytype' => '微信', 'Paymoney' => $orderinfo['payprice'], 'PayRemark' => '微信在线支付' . $orderinfo['payprice'] . '元'];
            }
        } elseif (in_array($orderinfo['buytype'], [4])) {
            $Paydetail = [];
        }
        $bind = [
            'Client_Id' => md5($userinfo['cardid']),
            'CardID'    => $userinfo['cardid'],
            'Dmno'      => strlen($goods['erp_number']), // erp模板编号
            'Price'     => $orderinfo['q_price'],
            'billid'    => $orderno,
            'Paydetail' => $Paydetail,
        ];
        $jsonData = json_encode($bind, JSON_UNESCAPED_UNICODE);
        $jsonData = $this->module_crmaes->encrypt($jsonData);
        $appKey   = self::APPKEY;
        $sign     = substr(md5(strtoupper(md5($appKey . $jsonData))), 0, -3);
        $postdata     = [
            'appKey'   => $appKey,
            'jsonData' => $jsonData,
            'Sign'     => $sign,
        ];
        $postdata    = http_build_query($postdata);
        $url     = self::URL . '/CouponGrant';
        $result = $this->_curl($url,$postdata);
        $xmls = simplexml_load_string($result);
        if(!$xmls) return false;
        $xmljson = json_encode($xmls);
        $xml     = json_decode($xmljson, true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='True'){
            $addData = [
                'wx_openid' => $orderinfo['wx_openid'],
                'actid' => $xmldata['data']['actID'],
                'orderno' => $orderno,
                'goodsid' => $orderinfo['goodsid'],
                'tokenbala' => $xmldata['data']['tokenBala'],
                'enddata' => $xmldata['data']['endDate'],
                'tstate' => $xmldata['data']['tstate'],
                'status' => 0,
                'createtime' => TIME,
            ];
            $status = $this->module_quan->add($addData);
            $this->module_shop_order->setIsCreateQuan(2,'');
            ## 生成成功，记录订单
            return $status;
            
        }else{
             ## 没有生成功，记录返回状态
            var_dump($result);
            $this->module_shop_order->setIsCreateQuan(1,$result);
            return false;
        }
    }

    private function _curl($url, $data = null, $heraders = null, $time = 120)
    {
        $curl = curl_init();
        if (is_array($url)) {
            $url = $url[0];
        }
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        if (!empty($data)) {
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }
        if ($heraders) {
            curl_setopt($curl, CURLOPT_HEADER, 0);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $heraders);
        }
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, $time);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $output = curl_exec($curl);
        // $httpCode = curl_getinfo($curl,CURLINFO_HTTP_CODE);
        // if($httpCode!=200){
        //     $output=json_encode(['status'=>'Failed','errorno'=>$httpCode]);
        // }
        curl_close($curl);
        return $output;
    }

}
