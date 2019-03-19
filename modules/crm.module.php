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
        $cardID = $userinfo['cardfaceid'];
        $sign = substr(md5(strtoupper(md5($appKey.$cardID))),0,-3);
        $data = [
          'appKey'=>$appKey,
          'cardID'=>$cardID,
          'Sign'=>$sign,
        ];
        $data = http_build_query($data);
        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return 0;
        $xml     = json_decode(json_encode($xmls), true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='True'){
            return $xmldata['data']['integral'];
        }else{
            return 0;
        }
    }

    // 获取余额
    public function getBalance($openid){
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/AccountQuery';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return 0;
        }
        $cardfaceid = $userinfo['cardfaceid'];
        // $cardfaceid = '9900052001667';
        $sign = substr(md5(strtoupper(md5($appKey.$cardfaceid))),0,-3);
        $data = [
          'appKey'=>$appKey,
          'cardID'=>$cardfaceid,
          'Sign'=>$sign,
        ];
        $data = http_build_query($data);
        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return 0;
        $jsondata = json_decode($xmls,true);
        if($jsondata['success'] == 'True'){
           $balancelist = $jsondata['data']['data'];
           if($balancelist){
                foreach($balancelist as $val){
                    if($val['accType']==4){
                        return $val['cashBala'];
                    }
                }
                return 0;
           }else{
                return 0;
           }
        }else{
            return 0;
        }

    }
    // 判断券是否不对
    public function isCouponQuery($orderno){
        // $url = 'http://118.114.241.104:9923/OpenCrm.asmx/GetIntegralAndAcc';
        // $url = 'http://118.114.241.104:9923/OpenCrm.asmx/CouponQuery';
        // $appKey = self::APPKEY;
        // $quaninfo = $this->module_quan->getOrdernoInfo($orderno);
        // $quaninfo = $quaninfo[0];
        // $userinfo = $this->module_member_user->getInfo($quaninfo['wx_openid']);
        // if(!$userinfo){
        //     return false;
        // }
        // $cardID = $userinfo['cardfaceid'];
        // $sign = substr(md5(strtoupper(md5($appKey.$cardID))),0,-3);
        // $data = [
        //   'appKey'=>$appKey,
        //   'cardID'=>$cardID,
        //   'Sign'=>$sign,
        // ];
        // $data = http_build_query($data);
        // $result = $this->_curl($url,$data);
        // libxml_disable_entity_loader(true);
        // $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        // if(!$xmls) return false;
        // $alldata = json_decode($xmls,true);
        // if($alldata['success']=='True'){
        //     $listdata = $alldata['data']['account'];
        //     if($listdata){
        //         foreach($listdata as $val){
        //             if($val['actID']==$quaninfo['actid'] && $val['tokenBala']==$quaninfo['tokenbala']){
        //                 return true;
        //             }
        //         }
        //     }else{
        //         return false;
        //     }
        //     return false;
        // }else{
        //     return false;
        // }
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/GetAccInfo';
        $appKey = self::APPKEY;
        $quaninfo = $this->module_quan->getOrdernoInfo($orderno);
        $quaninfo = $quaninfo[0];
        $cardID = $quaninfo['actid'];
        $sign = substr(md5(strtoupper(md5($appKey.$cardID))),0,-3);
        $data = [
          'appKey'=>$appKey,
          'accID'=>$cardID,
          'Sign'=>$sign,
        ];
        $data = http_build_query($data);
        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $alldata = json_decode($xmls,true);
        if($alldata['success']=='True'){
            if($alldata['data']['token_face']==$alldata['data']['token_bala']){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }

    // 通知券失败（退款用）
    public function unCouponGrant($actid,$cardfaceid){
        // return true;
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/CancellationCoupon';
        $appKey = self::APPKEY;
        $quaninfo = $this->module_quan->getInfo($actid);
        // $orderinfo = $this->module_shop_order->getInfo($quaninfo['orderno']);
        // $relorgan = $this->module_shop_category->getRelorgan($orderinfo['storeid']);
        $bind = [
            'billid'    => $quaninfo['orderno'],
            'AccId'    => $actid,
            'CardFace'    => $cardfaceid,
            'CouponMoney'      => $quaninfo['tokenbala'],
            // 'Organ' => $relorgan,// 新增机构编号
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
        //CouponMoney
        $data = http_build_query($postdata);

        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $xml     = json_decode(json_encode($xmls), true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='true'){
            if($xmldata['data']['result']){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }
    }


    // 增加积分
    public function addJifenLzsen($openid,$amount,$message=''){
        return true;
    }
    // 增加余额
    public function addBalanceLzsen($openid,$amount,$message=''){
        return true;
    }

    // 扣除积分
    public function subJifen(){
        return true;
    }
    // 扣除余额
    public function subBalance(){
        return true;
    }



    ## 余额返还
    public function returnBuyBalance($openid,$orderno){
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/ReturnMoney';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return 0;
        }
        $cardfaceid = $userinfo['cardfaceid'];
        $bind = [
            'billId'    => $orderno,
            'OldbillId'    => $orderno,
            'busdate'    => date('Y-m-d'),
            'cardFace'      => $cardfaceid,
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
        $data = http_build_query($postdata);
        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $jsondata = json_decode($xmls,true);
        if($jsondata['success']=='True'){
            return true;
        }else{
            return false;
        }
    }
    ## 购买商品扣除余额
    public function buySubBalance($openid,$orderno,$money){
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/AccountSpend';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return false;
        }
        $cardfaceid = $userinfo['cardfaceid'];
        $bind = [
            'Client_Id'    => md5($orderno),
            'cardID'    => base64_encode($cardfaceid),
            'payPwd'    => '',
            'money'      => $money,
            'msgCode'      => '',
            'billId'      => $orderno,
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
        //CouponMoney
        $data = http_build_query($postdata);
        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $jsondata = json_decode($xmls,true);
        if($jsondata['success']=='True'){
            return true;
        }else{
            return false;
        }
    }

    ## 购买商品扣除积分
    public function buySubJifen($openid,$orderno,$jifen){
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/IntegralSubtract';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return false;
        }
        $cardfaceid = $userinfo['cardfaceid'];
        $bind = [
            'Client_Id'    => md5($orderno),
            'cardID'    => $cardfaceid,
            'integral'    => $jifen,
            'billId'      => $orderno,
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
        //CouponMoney
        $data = http_build_query($postdata);
        $result = $this->_curl($url,$data);
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $jsondata = json_decode($xmls,true);
        if($jsondata['success']=='True'){
            return true;
        }else{
            return false;
        }
    }
    
    ## 返还积分 
    public function returnBuyjifen($openid,$orderno,$jifen){
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/IntegralAdd';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return false;
        }
        $Client_Id=$openid;
        $cardface=$userinfo['cardfaceid'];
        $integral=$jifen;
        $resume = '积分返还';
        $shoptype=(int)$appKey;
        $bind = [
            'Client_Id' => md5($userinfo['cardid']),
            'cardface'    => $userinfo['cardfaceid'],
            'integral'      => $jifen, // 数量
            'resume'     => $resume,
            'shoptype'    => $shoptype,
            'billid' => $orderno,
        ];
        $jsonData = json_encode($bind, JSON_UNESCAPED_UNICODE);
        $jsonData = $this->module_crmaes->encrypt($jsonData);
        $sign     = substr(md5(strtoupper(md5($appKey . $jsonData))), 0, -3);
        $postdata     = [
            'appKey'   => $appKey,
            'jsonData' => $jsonData,
            'Sign'     => $sign,
        ];
        $postdata    = http_build_query($postdata);
        $result = $this->_curl($url,$postdata);
        echo $result;
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $xml     = json_decode(json_encode($xmls), true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='True'){
            ## 生成成功，记录日志
            return true;
        }else{
            return false;
        }
    }

    ## 扣积分、余额、生成券等操作
    public function paysuccess($orderno,$isonlinepay=false){
        $orderinfo = $this->module_shop_order->getInfo($orderno);
        if(!$orderinfo){
            return false;
        }
        // 扣除余额
        if($orderinfo['subbalance']>0){
            $status = $this->buySubBalance($orderinfo['pay_wx_openid'],$orderno,$orderinfo['subbalance']);
            if(!$status){
                return false;
            }
        }
        // 扣除积分
        if($orderinfo['subjifen']>0){
            $status = $this->buySubJifen($orderinfo['pay_wx_openid'],$orderno,$orderinfo['subjifen']);
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
        if($orderinfo['buytype']==2){
            $orderinfos =  $this->module_shop_group->getGroupInfo($orderinfo['groupkey']);
            $goods = $this->module_shop_goods->getInfo($orderinfo['goodsid']);
            if(count($orderinfos) >= $goods['g_number']){
                $isgroupcreate = true;
                foreach($orderinfos as $val){
                    if($val['ispay']!=1){
                        $isgroupcreate = false;
                        break;
                    }
                }
                if($isgroupcreate){
                    foreach($orderinfos as $val){
                        // 生成电子券
                        $this->createCouponGrant($val['orderno']);
                    }
                }
            }
        }else{
            // 生成电子券
            $this->createCouponGrant($orderno);
        }
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
        if($goods && $goods['lpq_number']!="")
        {
            $status_lingquan=$this->model_lipinquan->lingqulipinquan(1,$goods['lpq_number'],$goods['id'],$goods['number'],$goods['buynumber'],$orderno);
            if($status_lingquan['status']){
                $this->module_shop_order->setIsCreateQuan($orderno,2,'');
                return true;
            }else{
                $this->module_shop_order->setIsCreateQuan($orderno,1,'');
                return false;
            }
//            //return $status_lingquan;
//
//            return $status_lingquan['status']?true:false;
//            $message = '正在开发中';
//            goto error;
        }
        if(!$goods || strlen($goods['erp_number']) < 1){
            return false;
        }

        $relorgan = $this->module_shop_category->getRelorgan($orderinfo['storeid']);
        $Paydetail = [];
        if (in_array($orderinfo['buytype'], [1, 2])) {
            if ($orderinfo['subbalance'] > 0) {
                $Paydetail[] = ['Paytype' => '余额', 'Paymoney' => $orderinfo['subbalance'], 'PayRemark' => '余额使用' . $orderinfo['subbalance'] . '元'];
            }
            if ($orderinfo['subjifen'] > 0) {
                $Paydetail[] = ['Paytype' => '积分', 'Paymoney' => $orderinfo['jifen'], 'PayRemark' => '积分' . $orderinfo['subjifen'] . '抵扣' . $orderinfo['jifen'] . '元'];
            }
            if ($orderinfo['payprice'] > 0) {
                $Paydetail[] = ['Paytype' => '微信', 'Paymoney' => $orderinfo['payprice'], 'PayRemark' => '微信在线支付' . $orderinfo['payprice'] . '元'];
            }
        } elseif (in_array($orderinfo['buytype'], [3])) {
            if ($orderinfo['subbalance'] > 0) {
                $Paydetail[] = ['Paytype' => '余额', 'Paymoney' => $orderinfo['subbalance'], 'PayRemark' => '余额使用' . $orderinfo['subbalance'] . '元'];
            }
            if ($orderinfo['subjifen'] > 0) {
                $Paydetail[] = ['Paytype' => '积分', 'Paymoney' => $orderinfo['jifen'], 'PayRemark' => '积分使用' . $orderinfo['subjifen'] . '积分'];
            }
            if ($orderinfo['payprice'] > 0) {
                $Paydetail[] = ['Paytype' => '微信', 'Paymoney' => $orderinfo['payprice'], 'PayRemark' => '微信在线支付' . $orderinfo['payprice'] . '元'];
            }
        } else{
           if ($orderinfo['subbalance'] > 0) {
                $Paydetail[] = ['Paytype' => '余额', 'Paymoney' => $orderinfo['subbalance'], 'PayRemark' => '余额使用' . $orderinfo['subbalance'] . '元'];
            }
            if ($orderinfo['subjifen'] > 0) {
                $Paydetail[] = ['Paytype' => '积分', 'Paymoney' => $orderinfo['jifen'], 'PayRemark' => '积分' . $orderinfo['subjifen'] . '抵扣' . $orderinfo['jifen'] . '元'];
            }
            if ($orderinfo['payprice'] > 0) {
                $Paydetail[] = ['Paytype' => '微信', 'Paymoney' => $orderinfo['payprice'], 'PayRemark' => '微信在线支付' . $orderinfo['payprice'] . '元'];
            }
        }
        $bind = [
            'Client_Id' => md5($userinfo['cardid']),
            'CardID'    => $userinfo['cardfaceid'],
            'Dmno'      => $goods['erp_number'], // erp模板编号
            'relorgan'  => $relorgan, // 机构编号
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
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $xml     = json_decode(json_encode($xmls), true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='True'){
            $quanstarttime = 0;
            $quanendtime = 0;
            if($goods['quanguoqi']>0){
                $quanstarttime = time();
                $quanendtime = time()+(intval($goods['quanguoqi']) * 86400);
            }
            $addData = [
                'wx_openid' => $orderinfo['wx_openid'],
                'actid' => $xmldata['data']['actID'],
                'orderno' => $orderno,
                'goodsid' => $orderinfo['goodsid'],
                'tokenbala' => $xmldata['data']['tokenBala'],
                'begindate' => $xmldata['data']['beginDate'],
                'enddata' => $xmldata['data']['endDate'],
                'tstate' => $xmldata['data']['tstate'],
                'quanstarttime' => $quanstarttime,
                'quanendtime' => $quanendtime,
                'status' => 0,
                'createtime' => TIME,
            ];
            $status = $this->module_quan->add($addData);
            $this->module_shop_order->setquantimes($orderno,$quanstarttime,$quanendtime);
            $this->module_shop_order->setIsCreateQuan($orderno,2,'');
            ## 生成成功，记录订单
            return $status;
        }else{
             ## 没有生成功，记录返回状态
            $this->module_shop_order->setIsCreateQuan($orderno,1,$result);
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
    
    
    //增加积分 
    public function addJifen($jifen=0,$jifentitle='赠送积分'){
        // $openid = $this->session->get('openid');
        $userapi = $this->module_userapi->isLogin();
        $openid = $userapi['openid'];
        $store_id = $this->module_store->getStoreId();
        $url = 'http://118.114.241.104:9923/OpenCrm.asmx/IntegralQuery';
        $appKey = self::APPKEY;
        $userinfo = $this->module_member_user->getInfo($openid);
        if(!$userinfo){
            return false;
        }
        // { ["OpenID"]=> string(28) "owwbzt8dOPJ0wDs9kalZv5LnHu4g" ["sub"]=> string(17) "1303471608@qq.com" ["cpn"]=> string(4) "0005" ["CrdID"]=> string(13) "9900052002126" ["CrdFaceID"]=> string(13) "9900052002126" ["CrmGuestId"]=> string(13) "9900052002126" ["MemberTypID"]=> string(2) "06" ["MemberTyp"]=> string(9) "生肖卡" ["MemberIngt"]=> float(0) ["IsMember"]=> bool(true) ["GstID"]=> int(28829) ["Avt"]=> string(0) "" ["Tel"]=> string(11) "18782140263" ["IsBind"]=> bool(true) ["LvlID"]=> int(1) ["Brth"]=> string(19) "0001-01-01 00:00:00" ["MemberName"]=> string(0) "" ["CrdRecordId"]=> int(0) ["OrgID"]=> string(4) "0009" } 
        $Client_Id=$openid;
        $cardface=$userinfo['cardfaceid'];
        $integral=$jifen;
        $resume=$jifentitle;
        $shoptype=(int)$appKey;
        $billid=$this->module_shop_order->createNo(); 
        
        $bind = [
            'Client_Id' => md5($userinfo['cardid']),
            'cardface'    => $userinfo['cardfaceid'],
            'integral'      => $jifen, // 数量
            'resume'     => $resume,
            'shoptype'    => (int)$appKey,
            'billid' => $this->module_shop_order->createNo(),
        ];
        $jsonData = json_encode($bind, JSON_UNESCAPED_UNICODE);
        $jsonData = $this->module_crmaes->encrypt($jsonData);
        $sign     = substr(md5(strtoupper(md5($appKey . $jsonData))), 0, -3);
        $postdata     = [
            'appKey'   => $appKey,
            'jsonData' => $jsonData,
            'Sign'     => $sign,
        ];
        $postdata    = http_build_query($postdata);
        $url     = self::URL . '/IntegralAdd';
        $result = $this->_curl($url,$postdata);
        return $result;
        libxml_disable_entity_loader(true);
        $xmls = simplexml_load_string($result, 'SimpleXMLElement', LIBXML_NOCDATA);
        if(!$xmls) return false;
        $xml     = json_decode(json_encode($xmls), true);
        $xmldata    = json_decode($xml[0], true);
        if($xmldata['success']=='True'){
            ## 生成成功，记录日志
            return true;
        }else{
            return false;
        }
        
    }
    

}
