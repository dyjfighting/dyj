<?php
/*
 *  [ 会员管理操作 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */

class ModuleMemberUser extends Module
{

    const TABLE = 'member_user';
    
    ## 性别类型
    const SEXS = [
        1=>'男',
        2=>'女',
        3=>'保密'
    ];

    // 获取会员列表
    public function getList($limit, $search = [], $sort = [])
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' 1=1 ';
        // 搜索 会员卡/手机
        if (isset($search['keyword'])) {
            $where .= ' and ( `phone`="'.$search['keyword'].'" or `cardfaceid`="'.$search['keyword'].'" ) ';
        }
        $order    = "id desc";
        $datalist = $this->db->table($table)->where($where)->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    // 
    public function isSubscribe($openid){
        $userinfo = $this->getInfo($openid);
        if(!$userinfo) return false;
        $wxinfo = json_decode($userinfo['wxinfo'],true);
        // if(isset($wxinfo['subscribe']) && $wxinfo['subscribe']==0){
        //     return false;
        // }else{
        //     return true;
        // }
        if(isset($wxinfo['subscribe']) && $wxinfo['subscribe']==1){
            return true;
        }else{
            return false;
        }
    }

    public function getInfo($id){
        $table = DB_PREFIX . self::TABLE;
        if(is_numeric($id)){
            $where = ' `id` = "'.$id.'" ';
        }else{
            $where = ' `wx_openid` = "'.$id.'" ';
        }
        $data = $this->db->table($table)->where($where)->find();
        return $data?:false;
    }
    // isopenid
    public function isopenid($openid){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `wx_openid` = "'.$openid.'" ';
        $status = $this->db->table($table)->where($where)->find();
        return $status?true:false;
    }

    public function getopenids($openid){
        $userinfo = $this->getInfo($openid);
        if(!$userinfo) return [$openid];
        $table = DB_PREFIX . self::TABLE;
        $where = ' `cardfaceid` = "'.$userinfo['cardfaceid'].'" ';
        $field = 'wx_openid';
        $data = $this->db->table($table)->field($field)->where($where)->select();
        if($data){
            $openids = [];
            foreach($data as $val){
                $openids[] = $val['wx_openid'];
            }
        }
        return [$openid];

    }

    // 删除会员
    public function delete($userid){
        $table = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$userid.'" ';
        $status = $this->db->table($table)->where($where)->limit(1)->delete();
        return $status?true:false;
    }

    public function add($userinfo,$wxinfo=false){

        if($this->isopenid($userinfo['OpenID'])){
            //  if($userinfo['OpenID']=='o2qtn05zZYnC1eRPCVWiVPsH0L5M'){
            //     print_r($userinfo);
            //     print_r($wxinfo);
            //     exit();
            // }
            // return true;
            $table = DB_PREFIX . self::TABLE;
            $bind = [
                'wx_openid' => $userinfo['OpenID'],
                'cardid' => $userinfo['CrmGuestId'],
                'cardfaceid' => $userinfo['CrdFaceID'],
                'phone' => $userinfo['Tel'],
                'fullname' => $userinfo['MemberName']
            ];
            if($wxinfo){
                $bind['avatar'] = $wxinfo['headimgurl'];
                $bind['nickname'] = $wxinfo['nickname'];
                $bind['area'] = $wxinfo['country'].'-'.$wxinfo['province'].'-'.$wxinfo['city'];
                $bind['sex'] = $wxinfo['sex'];
                $bind['wxinfo'] = json_encode($wxinfo,JSON_UNESCAPED_UNICODE);
            }
            $where = ' `wx_openid` = "'.$userinfo['OpenID'].'" ';
            $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
            return true;
        }else{
            $table = DB_PREFIX . self::TABLE;
            $bind = [
                'wx_openid' => $userinfo['OpenID'],
                'cardid' => $userinfo['CrmGuestId'],
                'cardfaceid' => $userinfo['CrdFaceID'],
                'phone' => $userinfo['Tel'],
                'fullname' => $userinfo['MemberName'],
                'createtime' => TIME
            ];
            if($wxinfo){
                $bind['avatar'] = $wxinfo['headimgurl'];
                $bind['nickname'] = $wxinfo['nickname'];
                $bind['area'] = $wxinfo['country'].'-'.$wxinfo['province'].'-'.$wxinfo['city'];
                $bind['sex'] = $wxinfo['sex'];
                $bind['wxinfo'] = json_encode($wxinfo,JSON_UNESCAPED_UNICODE);
            }
            if(substr($userinfo['CrmGuestId'], 0, 2) == 99){
                $store_id=$this->module_store->getStoreId();
                $storeinfo=$this->module_shop_category->getInfo($store_id);
                $shareconfig=$storeinfo['shareconfig'];
                $newgoodsid=$shareconfig && json_decode($shareconfig,true)['newgoodsid']?json_decode($shareconfig,true)['newgoodsid']:"";
                if($newgoodsid!=""){
                    $newgoodsid=explode(',',$newgoodsid);
                    foreach ($newgoodsid as $key=>$val){
                        $c=$this->base->encrypt(['buy'=>4,'id'=>$val]);
                        $status  = $this->model_order->create($userinfo['OpenID'],1, 0, 0, '', $c);
                    }
                }


            }
            $store_id = $this->module_store->getStoreId();

            $id=$this->db->table('blwx_answer')->where(['phone'=>$userinfo['Tel']])->find();
            $ids=$this->db->table('blwx_answer')->where(['phone'=>$userinfo['Tel']])->select();
            $num= $this->db->datacount;
            if($store_id ==374 && $num<10000 && empty($id)){
                $blinds=[
                    'user_name'=>$wxinfo['nickname'],
                    'wx_openid'=>$userinfo['OpenID'],
                    'answer'=>'锦鲤是什么耿',
                    'time'=>date('Y-m-d'),
                    'phone'=>$userinfo['Tel'],
                ];
                $num=$this->db->table('blwx_answer')->bind($blinds)->insert();
            }

            $status = $this->db->table($table)->bind($bind)->insert();
            return $status?:false;
        }
    }
    
    
    public function getPintuanJiage($groupkey,$goodId)
    {
        $goods                 = $this->module_shop_goods->getInfo($goodId);
        //查询order 表里 拼购组的人数
        $orderCount = $this->module_shop_order->getOrderCountByGroupkey($groupkey);
       
       
        if($orderCount==0)
        {
            //人数为0 则是拼住价
            return $goods["g_p_price"];
        }elseif ($orderCount>0)
        {
            //人数 >0 则是拼友价
            return $goods["g_price"];
        }
       
        
        
    }

    //修改会员列表

    public  function updateUser($where=[]){
        if(!isset($where['is_customer'])){
            return false;
        }
        if(!$where['phone']){
            return false;
        }
        $sql="UPDATE blwx_member_user SET is_customer={$where['is_customer']} WHERE phone in('{$where['phone']}')";
        $result=$this->db->exec($sql);
        return $result;



    }

    //获取能否购买
    public function  getCustomerByWhere($data){
        $table = DB_PREFIX . self::TABLE;
        if(isset($data['Tel'])){
            $where['phone']=$data['Tel'];
        }
        if(isset($data['wx_openid'])){
            $where['wx_openid']=$data['OpenID'];
        }
//        $where=[
//            'phone'=>$data['Tel'],
//            'wx_openid'=>$data['OpenID'],
//        ];
        $customer=$this->db->table($table)->where($where)->field('is_customer')->find();
        return $customer['is_customer'];
    }

}
