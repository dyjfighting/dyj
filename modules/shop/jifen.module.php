<?php
/*
 *  [ 商户管理 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */

class ModuleShopJifen extends Module
{

    const TABLE = 'goods_jifen';

    // 获取列表
    public function getList($limit, $search=[])
    {
        $table = DB_PREFIX . self::TABLE;
        $where = ' 1=1 ';
        // 搜索 第三方ID/商户名称
        if (isset($search['keyword'])) {
            $where .= ' and ( `jifen`="' . $search['keyword'] . '" or `price`="' . $search['keyword'] . '" ) ';
        }
        $order    = "id asc";
        $datalist = $this->db->table($table)->where($where)->order($order)->limit($limit)->selectlist();
        return [
            'datacount' => $this->db->datacount,
            'datalist'  => $datalist,
        ];
    }

    // 添加
    public function add($data)
    {
        $bind = [
            'jifen'      => isset($data['jifen']) ? $data['jifen'] : '', //
            'price'      => isset($data['price']) ? $data['price'] : '', //
            'createtime' => TIME,
        ];
        $table  = DB_PREFIX . self::TABLE;
        $status = $this->db->table($table)->bind($bind)->insert();
        return $status ?: false;
    }

    // 修改
    public function edit($merchantId, $data)
    {
        $bind = [
            'jifen'     => isset($data['jifen']) ? $data['jifen'] : '', // 商户名称
            'price'      => isset($data['price']) ? $data['price'] : '', // 第三方商户ID
        ];
        $table  = DB_PREFIX . self::TABLE;
        $where  = ' `id` = "' . $merchantId . '" ';
        $status = $this->db->table($table)->bind($bind)->where($where)->limit(1)->update();
        return $status ? true : false;
    }

    // 获取指定信息
    public function getInfo($jifenid)
    {
        static $jifens = [];
        if (!isset($jifens[$jifenid])) {
            $table = DB_PREFIX . self::TABLE;
            $where = ' `id` = "' . $jifenid . '" ';
            $data  = $this->db->table($table)->where($where)->find();
            if ($data) {
                $jifens[$jifenid] = $data;
            } else {
                $jifens[$jifenid] = false;
            }
        }
        return $jifens[$jifenid];
    }

}
