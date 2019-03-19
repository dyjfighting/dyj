<?php
/*
 *  [ 客服模块 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */

class ModuleMemberKefu extends Module
{

    const TABLE = 'member_kefu';

    const DEFAULTPASSWD = '666666';

    ## 客服状态
    const STATUS = [
        0 => '停用',
        1 => '启用',
    ];

    // 获取客服列表
    public function getList($limit, $search)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' 1=1 ';
        // 搜索 会员卡/手机
        if (isset($search['keyword'])) {
            $where .= ' and (`phone`="' . $search['keyword'] . '" or `username`="' . $search['keyword'] . '" or locate("' . $search['keyword'] . '",`nickname`) ) ';
        }
        if (isset($search['istuijian'])) {
            if($search['istuijian']==1){
                $where .= ' and `istuijian`=1 ';
            }else{
                $where .= ' and `istuijian`=0 ';
            }
        }
        
        $order    = "id desc";
        $datalist = $this->db->table($table)->where($where)->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    // 获取指定信息
    public function getInfo($kefuId)
    {
        static $merchants = [];
        if (!isset($merchants[$kefuId])) {
            $table = DB_PREFIX . self::TABLE;
            $where = ' `id` = "' . $kefuId . '" ';
            $data  = $this->db->table($table)->where($where)->find();
            if ($data) {
                $merchants[$kefuId] = $data;
            }
        }
        return $merchants[$kefuId];
    }

    // 验证用户名
    public function checkUsername($username)
    {
        if (preg_match('/^[a-zA-Z0-9]{6,20}$/', $username)) {
            return true;
        }
        return false;
    }

    // 验证密码格式
    public function checkPasswd($passwd)
    {
        if (preg_match('/^[\S]{6,20}$/', $passwd)) {
            return true;
        }
        return false;
    }

    // 账户是否存在
    public function isUsername($username)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = '  `username` = "' . $username . '" ';
        $data  = $this->db->table($table)->where($where)->find();
        return $data;
    }

    // 生成唯一chatid
    private function createChatuserid($username, $time)
    {
        return md5('kefu_' . sha1($username) . sha1($time));
    }

    // 密码加密
    public function createPasswd($passwd)
    {
        return sha1('kefu_' . $passwd . md5($passwd) . $passwd);
    }

    // 客服手机号
    public function isPhone($phone){
        $table = DB_PREFIX . self::TABLE;
        $where = '  `phone` = "' . $phone . '" ';
        $data  = $this->db->table($table)->where($where)->find();
        return $data;
    }

    // 添加客服
    public function add($data)
    {
        $chatid   = $this->createChatuserid($data['username'], TIME);
        $password = isset($data['password']) ? $data['password'] : self::DEFAULTPASSWD; // 登录密码
        $bind     = [
            'chatid'        => $chatid, // 聊天id
            'phone'      => isset($data['phone']) ? $data['phone'] : '', // 登录用户名
            'username'      => isset($data['username']) ? $data['username'] : '', // 登录用户名
            'nickname'      => isset($data['nickname']) ? $data['nickname'] : '',
            'password'      => $this->createPasswd($password), // 登录密码
            'merchanid'     => isset($data['merchanid']) ? $data['merchanid'] : '',
            'avatar'        => isset($data['avatar']) ? $data['avatar'] : '',
            'workday_open'  => isset($data['workday_open']) ? $data['workday_open'] : '',
            'workday_close' => isset($data['workday_close']) ? $data['workday_close'] : '',
            'description'   => isset($data['description']) ? $data['description'] : '',
            'score'         => isset($data['score']) ? $data['score'] : '',
            'status'        => isset($data['status']) ? $data['status'] : 1,
            'istuijian'        => isset($data['istuijian']) ? $data['istuijian'] : 0,
            'createtime'    => TIME,
        ];
        $table  = DB_PREFIX . self::TABLE;
        $status = $this->db->table($table)->bind($bind)->insert();
        return $status ?: false;
    }

    // 修改
    public function edit($kefuid,$data)
    {
        $bind = [
            'merchanid'     => isset($data['merchanid']) ? $data['merchanid'] : '',
            'avatar'        => isset($data['avatar']) ? $data['avatar'] : '',
            'nickname'      => isset($data['nickname']) ? $data['nickname'] : '',
            'workday_open'  => isset($data['workday_open']) ? $data['workday_open'] : '',
            'workday_close' => isset($data['workday_close']) ? $data['workday_close'] : '',
            'description'   => isset($data['description']) ? $data['description'] : '',
            'score'         => isset($data['score']) ? $data['score'] : '',
            'status'        => isset($data['status']) ? $data['status'] : 1,
            'istuijian'        => isset($data['istuijian']) ? $data['istuijian'] : 0,
        ];
        $table  = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$kefuid.'" ';
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ?: false;
    }

    // 修改密码
    public function setPasswd($kefuid,$passwd){
        $table  = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$kefuid.'" ';
        $passwd = $this->createPasswd($passwd);
        $bind = ['password'=>$passwd];
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ?: false;
    }

    // 修改头像
    public function setAvatar($kefuid,$avatar){
        $table  = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$kefuid.'" ';
        $bind = ['avatar'=>$avatar];
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ?: false;
    }

    // 修改昵称
    public function setNickname($kefuid,$nickname){
        $table  = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$kefuid.'" ';
        $bind = ['nickname'=>$nickname];
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ?: false;
    }

    // 修改工作时间
    public function setWorkday($kefuid,$workday_open,$workday_close){
        $table  = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$kefuid.'" ';
        $bind = [
             'workday_open'  => $workday_open,
             'workday_close' => $workday_close,
        ];
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ?: false;
    }

    // 删除客服
    public function delete($kefuid){
        $table  = DB_PREFIX . self::TABLE;
        $where = ' `id`="'.$kefuid.'" ';
        $status = $this->db->table($table)->where($where)->limit(1)->delete();
        return $status ?: false;
    }


    // 获取商铺的一个客服
    public function getChatidByMerchant_id($merchant_id)
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' `merchanid` = "' . $merchant_id . '" ';
        $data  = $this->db->table($table)->where($where)->find();
         
        if ($data) {
            return $data["chatid"];
        }else{
            return "";
        }
        
    }




}
