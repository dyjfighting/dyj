<?php

class ModuleQuan extends Module
{
    const TABLE = 'coupon_grant';

    public function add($bind){
        if($this->isorderno($bind['orderno'])){
            return true;
        }
    	$table  = DB_PREFIX . self::TABLE;
        $status = $this->db->table($table)->bind($bind)->insert();
        return $status ?: false;
    }

    // 获取列表
	public function getList($limit,$search=[],$order = 'id desc'){
		$table = DB_PREFIX . self::TABLE;
        $where = [];
        if(isset($search['orderno'])){
            $where[] = ' `orderno` = "'.$search['orderno'].'" ';
        }
        if(isset($search['actid'])){
            $where[] = ' `actid` = "'.$search['actid'].'" ';
        }
        if(isset($search['status'])){
            $where[] = ' `status` = "'.$search['status'].'" ';
        }
        $datalist = $this->db->table($table)->where(implode(' and ',$where))->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
	}

    public function getInfo($actid){
        $table = DB_PREFIX . self::TABLE;
        $where =  ' `actid`="'.$actid.'" ';
        $info = $this->db->table($table)->where($where)->find();
        return $info?$info:false;
    }

    public function getOrdernoInfo($orderno){
        $table = DB_PREFIX . self::TABLE;
        $where =  ' `orderno`="'.$orderno.'" ';
        $info = $this->db->table($table)->where($where)->select();
        return $info?$info:false;
    }

    public function getstatusname($statusid){
        $data = [  
            0=>'未使用',
            1=>'已使用',
            2=>'已过期',
        ];
        return isset($data[$statusid])?$data[$statusid]:'';
    }

    public function isactid($actid){
        $table = DB_PREFIX . self::TABLE;
        $where =  ' `actid`="'.$actid.'" ';
        $status = $this->db->table($table)->where($where)->find();
        return $status?$status:false;
    }
    public function isorderno($orderno){
        $table = DB_PREFIX . self::TABLE;
        $where =  ' `orderno`="'.$orderno.'" ';
        $status = $this->db->table($table)->where($where)->find();
        return $status?true:false;
    }

    /**
     //修改劵状态
     * @param $actId    卷账号
     * @param $status   卷状态   0未使用  1已使用  2已过期  3退款
     */
    public function setQuanstatus($orderno,$status){
        if(empty($orderno) || !$this->isorderno($orderno)){
            return false;
        }
        $table = DB_PREFIX . self::TABLE;
        $where =  ' `orderno`="'.$orderno.'" ';
        $bind = [
            'status'=>$status
        ];
        $status = $this->db->table($table)->where($where)->bind($bind)->update();
        if(!$status){
            return false;
        }
        return true;
    }
    
     /**
     //修改劵状态
     * @param $actId    卷账号
     * @param $status   卷状态   0未使用  1已使用  2已过期  3退款
     */
    public function setstatus($actid,$status){
        if(empty($actid)){
            $message = '无效的券账号';
            goto error;
        }
        $quaninfo = $this->isactid($actid);
        if(!$quaninfo){
            $message = '无效的券账号';
            goto error;
        }

        $orderinfo = $this->module_shop_order->getInfo($quaninfo['orderno']);
        
        ## 更新库存
        if(in_array($status,[2,3])){
            $this->module_shop_goods->refundStock($orderinfo['goodsid']);
        }

        // 如果是微信支付 那么就可以随变改状态
        if($orderinfo['payprice'] == 0){
            // 如果券是不是未使用那么就不更新
            if($quaninfo['status'] >0){
                return ['status'=>'Success','message'=>'OK'];
            }
        }

        
        $table = DB_PREFIX . self::TABLE;
        $where =  ' `actid`="'.$actid.'" ';
        $bind = [
            'status'=>$status
        ];
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
        if(!$status){
            $message = '无效的券账号';
            goto error;
        }
        return ['status'=>'Success','message'=>'OK'];
        error:
        return ['status'=>'Failed','message'=>$message];
    }

    public function setlipinquan($status,$openid,$orderno){
        $table = "blwx_giftcoupon_grant";
        $where=" orderno = {$orderno}  and  wx_openid = '{$openid}'";
        $bind = [
            'status'=>$status
        ];
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
        return $status?1:0;
    }


}
