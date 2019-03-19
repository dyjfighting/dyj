<?php
/*
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleAccesslog extends Module
{

    const TABLE = 'access_log';
    // 类型
    public $type = [
        1 => '主页',
        2 => '商品详细页',
        3 => '点击领取积分',
        4 => '分享获得积分',
        5 => '点击购买',
        6 => '免费领取券',
        7 => '特价分享',
    ];

    public $module = [
        1 => '超市',
        2 => '百货',
        3 => '活动',
    ];

    /**
     * 分析
     */
    public function parse($module,$type,$relateid = 0,$openid=false)
    {
        if(!$openid){
            $userapi = $this->module_userapi->isLogin();
            $openid = isset($userapi['openid'])?$userapi['openid']:'';
        }
        // 访问方式
        $method = IS_POST ? 'POST' : 'GET';
        // 上一个页面地址
        $sourceurl = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
        // 当前访问的地址
        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        ## 门店id
        $storeid  = $this->module_store->getStoreId();
        $binddata = [
            'openid'    => $openid, // 用户openid
            'module'    => $module, // 模块：1超市，2百货
            'type'      => $type, // 页面类型
            'storeid'   => $storeid, // 门店
            'url'       => $url, //访问地址
            'sourceurl' => $sourceurl, // 来源地址
            'method'    => $method, // 访问类型 post get
            'relateid'  => $relateid, // 关联id
        ];
        return $this->savelogs($binddata);
    }

    // 记录
    private function savelogs($bindata)
    {
        $table                 = DB_PREFIX . self::TABLE;
        $bindata['createtime'] = TIME;
        $bindata['ip']         = $this->base->getIp();
        $this->db->table($table)->bind($bindata)->insert();
        return true;
    }

}
