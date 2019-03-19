<?php
/* 
 *  [ 商品分类 ]
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModuleShopCategory extends Module{
	
	const table = 'goods_category';
	const table_extend = 'goods_category_extend';
	
	// 列表
	public function get_list(){
		$table = DB_PREFIX . self::table;
		$field = " * ";
		$where = " 1 ";
		$order = " sort asc ";
		$data = $this->db->table($table)->field($field)->where($where)->order($order)->select();
		
		return $data;
	}

	public function getRelorgan($storeid){
		$cateinfo = $this->getInfo($storeid);
		return $cateinfo['seller_id'];
	}


	
	/**
	 *  @brief 获取商品所属分类
	 *  
	 *  @param $goods_id 商品id
	 *  @return 分类名称数组
	 */
	public function get_gce_name($goods_id){
		$table = DB_PREFIX . self::table_extend .' as gce left join '.DB_PREFIX . self::table .' as gc on gc.id = gce.category_id ';
		$field = " gc.name,gc.id ";
		$where = " gce.goods_id = {$goods_id} ";
		$order = " gc.id asc ";
		$data = $this->db->table($table)->field($field)->where($where)->order($order)->select();
		
		return $data;
	}
	
	/**
	 *  @brief 获取某个分类的所有父分类 
	 *  
	 *  @param $cid 分类id
	 *  @return description
	 */
	public function get_parents($cid){
		$tree=array();  
		$categorys = $this->get_list();
		foreach($categorys as $item){  
			if($item['id']==$cid){  
				if($item['parentid']>0)  
					$tree=array_merge($tree,$this->get_parents($item['parentid']));  
				$tree[]=$item;    
				break;    
			}  
		}  
		return $tree;  
	}
	/**
	 *  @brief 获取某个分类的所有父分类 路径
	 *  
	 *  @param $cid 分类id
	 *  @return description
	 */
	public function get_category_path($cid,$iscount = false,$jiangefu=' > '){
		$cat_path = $this->get_parents($cid);
		$str = '';
		if(count($cat_path) > 0){
			$arr = [];
			foreach($cat_path as $val){
				$arr[] = $val['name'];
			}
			$str = join($jiangefu,$arr);
		}
		if($iscount==true){
			return count($cat_path);
		}else{
			return $str;
		}
		
	}

	public function getName($id){
		$info = $this->get_info($id);
		if($info){
			return $info['name'];
		}else{
			return false;
		}
	}

	public function getInfo($id){
		return $this->get_info($id);
	}

	public function get_info($id){
		$id = $id ? intval($id) : 0;
		$table = DB_PREFIX . self::table;
		$field = "*";
		$where = array('id'=>$id);
		$data = $this->db->table($table)->field($field)->where($where)->find();
		return $data;
	}
	
	// 排序
	public function order($id,$sort){
		$table = DB_PREFIX . self::table;
        $bind = [
            'sort' => intval($sort)
        ];
		
        $where = array('id'=>$id);
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
	
	// 是否首页显示
	public function set_visibility($id,$visibility){
		$table = DB_PREFIX . self::table;
        $bind = [
            'visibility' => $visibility
        ];
		
        $where = array('id'=>$id);
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
	
	// 添加
	public function add($data){
		$table = DB_PREFIX . self::table;
        $bind = [
            'name' => isset($data['name'])?$data['name']:'', 
            'parentid' => isset($data['parentid'])?$data['parentid']:0, 
            'visibility' => isset($data['visibility'])?$data['visibility']:0, 
            'storetype' => isset($data['storetype'])?$data['storetype']:0, 
            'sort' => isset($data['sort'])?$data['sort']:0, 
            'img' => isset($data['img'])?$data['img']:'', 
            'smsconfig' => isset($data['smsconfig'])?$data['smsconfig']:'', 
            'wxpayconfig' => isset($data['wxpayconfig'])?$data['wxpayconfig']:'', 
            'csconfig' => isset($data['csconfig'])?$data['csconfig']:'', 
            'bhconfig' => isset($data['bhconfig'])?$data['bhconfig']:'',
            'seller_id' => isset($data['seller_id'])?$data['seller_id']:0,
            'shareconfig' => isset($data['shareconfig'])?$data['shareconfig']:'',
            'latitude' => isset($data['latitude'])?$data['latitude']:'',
            'longitude' => isset($data['longitude'])?$data['longitude']:'',
        ];
        $insertid = $this->db->table($table)->bind($bind)->insert();
        if($insertid){
            return $insertid;
        }else{
            return false;
        }
	}
	
	// 修改
	public function edit($id,$data){
		$table = DB_PREFIX . self::table;
        $bind = [
            'name' => isset($data['name'])?$data['name']:'', 
            'parentid' => isset($data['parentid'])?$data['parentid']:0, 
            'visibility' => isset($data['visibility'])?$data['visibility']:0, 
            'storetype' => isset($data['storetype'])?$data['storetype']:0, 
            'sort' => isset($data['sort'])?$data['sort']:0, 
            'img' => isset($data['img'])?$data['img']:'',
            'smsconfig' => isset($data['smsconfig'])?$data['smsconfig']:'', 
            'wxpayconfig' => isset($data['wxpayconfig'])?$data['wxpayconfig']:'', 
            'csconfig' => isset($data['csconfig'])?$data['csconfig']:'', 
            'bhconfig' => isset($data['bhconfig'])?$data['bhconfig']:'', 
            'seller_id' => isset($data['seller_id'])?$data['seller_id']:0,
            'shareconfig' => isset($data['shareconfig'])?$data['shareconfig']:'',
            'latitude' => isset($data['latitude'])?$data['latitude']:'',
            'longitude' => isset($data['longitude'])?$data['longitude']:'',
        ];
		
        $where = array('id'=>$id);
        $status = $this->db->table($table)->where($where)->bind($bind)->limit(1)->update();
		
        return $status !== false ? true : false;
	}
	
	// 查询是否有子分类
	public function total_parentid($parentid){
		$table = DB_PREFIX . self::table;
		
		$where = array();
		if($parentid){
			$parentid = intval($parentid);
			$where = array('parentid'=>$parentid);
		}
				
		$data = $this->db->table($table)->where($where)->total();
		return $data;
	}
	
	// 删除
    public function delete($id,$whereSql=false){
        $table = DB_PREFIX . self::table;
        if(is_array($id)){
			$idStr = join(',',$id);
			$where = ' id in ('.$idStr.') ';
		}else{
			$id = intval($id);
			$where = array('id'=>$id);		
		}
		
		if($whereSql){
			$where = $whereSql;
		}
		
        $status = $this->db->table($table)->where($where)->delete();
        return $status !== false ? true : false;
    }
	
	/**
	 *  @brief 添加商品分类关系
	 *  
	 *  @param $data 插入数据
	 *  @return 
	 */
	public function add_extend($data){
		$table = DB_PREFIX . self::table_extend;
       
        $insertid = $this->db->table($table)->bind($data)->insert();
        if($insertid){
            return $insertid;
        }else{
            return false;
        }
	}
	
	// 删除商品分类关系表
    public function delete_extend($id,$whereSql=false){
        $table = DB_PREFIX . self::table_extend;
        if(is_array($id)){
			$idStr = join(',',$id);
			$where = ' id in ('.$idStr.') ';
		}else{
			$id = intval($id);
			$where = array('id'=>$id);		
		}
		
		if($whereSql){
			$where = $whereSql;
		}
		
        $status = $this->db->table($table)->where($where)->delete();
        return $status !== false ? true : false;
    }
	
	/**
	 * @param
	 * @return array
	 * @brief 无限极分类递归函数
	 */
	public function sortdata($catArray, $id = 0 , $prefix = '') {
		static $formatCat = array();
		static $floor     = 0;


		foreach($catArray as $key => $val)
		{
			if($val['parentid'] == $id)
			{
				$str         = $this->nstr($prefix,$floor);
				$val['name'] = $str.$val['name'];

				$val['floor'] = $floor;
				$formatCat[]  = $val;

				unset($catArray[$key]);

				$floor++;
				$this->sortdata($catArray, $val['id'] ,$prefix);
				$floor--;
			}
		}
		return $formatCat;
	}
	
	//处理商品列表显示缩进
	public function nstr($str,$num=0) {
		$return = '';
		for($i=0;$i<$num;$i++)
		{
			$return .= $str;
		}
		return $return;
	}
	
	// 下拉等级列表
	public function get_select($name="", $value="0", $root="",$class=""){
		$tree = $this->module_tree;
		$tree->icon = array('&nbsp;&nbsp;&nbsp;│ ','&nbsp;&nbsp;&nbsp;├─ ','&nbsp;&nbsp;&nbsp;└─ ');
		$tree->nbsp = '&nbsp;&nbsp;&nbsp;';
		$table = DB_PREFIX . self::table;
		$data = $this->db->table($table)->field('id,name,parentid')->order('sort asc,id asc')->select();
		if($root == '') $root = "≡ 作为一级分类 ≡";
		
		$html = '<select id="'.$name.'" name="'.$name.'" lay-search="" class="'.$class.'" lay-filter="'.$name.'">';
		$html.= '<option value="">'.$root.'</option>';
		$array = array();
		foreach($data as $v) {
			$v['cat_path'] = $this->get_category_path($v['id']);
			$v['selected'] = $v['id'] == $value ? 'selected' : '';
			$array[] = $v;
		}
		$str  = "<option value='\$id' y_name='\$name' cat_path='\$cat_path' \$selected>\$spacer \$name</option>";
		$tree->init($array);
		$html.= $tree->get_tree(0, $str);
		$html.= '</select>';

		return $html;
	}
    
	/**
	 * @brief 获取子分类可以无限递归获取子分类
	 * @param int $catId 分类ID
	 * @param int $level 层级数
	 * @return string 所有分类的ID拼接字符串
	 */
	public function catChild($catId,$level = 1)
	{
		if($level == 0)
		{
			return $catId;
		}
		$temp   = array();
		$result = array($catId);
		while(true)
		{
			$id = current($result);
			if(!$id)
			{
				break;
			}
			$table = DB_PREFIX . self::table;
			$temp = $this->db->table($table)->where(['parentid'=>$id])->select();
			foreach($temp as $key => $val)
			{
				if(!in_array($val['id'],$result))
				{
					$result[] = $val['id'];
				}
			}
			next($result);
		}
		return join(',',$result);
	}

	public function getsubid($catId){
		$table = DB_PREFIX . self::table;
		$temp = $this->db->table($table)->field('id')->where(['parentid'=>$catId])->select();
		return $temp;
	}

	public function getsublist($catId){
		$table = DB_PREFIX . self::table;
		if(is_array($catId)){
			$where = ' `parentid` in ('.implode(',',$catId).') ';
		}else{
			$where = ['parentid'=>$catId];
		}
		$temp = $this->db->table($table)->where($where)->order('sort asc')->select();
		return $temp;
	}

    /**
     * 获取ip
     * @return string
     */
    public function getip() {
        static $ip = '';
        $ip = $_SERVER['REMOTE_ADDR'];
        if(isset($_SERVER['HTTP_CDN_SRC_IP'])) {
            $ip = $_SERVER['HTTP_CDN_SRC_IP'];
        } elseif (isset($_SERVER['HTTP_CLIENT_IP']) && preg_match('/^([0-9]{1,3}\.){3}[0-9]{1,3}$/', $_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches)) {
            foreach ($matches[0] AS $xip) {
                if (!preg_match('#^(10|172\.16|192\.168)\.#', $xip)) {
                    $ip = $xip;
                    break;
                }
            }
        }
        return $ip;
    }

    /**
     * 执行CURL请求
     * @author: xialei<xialeistudio@gmail.com>
     * @param $url
     * @param array $params
     * @param bool $encode
     * @param int $method
     * @return mixed
     */
    public function async($url, $params = array(), $encode = true, $method = 1)
    {
        $ch = curl_init();
        if ($method == 1)
        {
            $url = $url . '?' . http_build_query($params);
            $url = $encode ? $url : urldecode($url);
            curl_setopt($ch, CURLOPT_URL, $url);
        }
        else
        {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($ch, CURLOPT_REFERER, '*');
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (iPhone; CPU iPhone OS 7_0 like Mac OS X; en-us) AppleWebKit/537.51.1 (KHTML, like Gecko) Version/7.0 Mobile/11A465 Safari/9537.53');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp;
    }
    public function test()
    {
        print_r($this->locationByIP('222.210.8.184'));die;
    }

    /**
     * ip定位
     * @param string $ip
     * @return array
     * @throws Exception
     */
    public function locationByIP($ip)
    {
        //检查是否合法IP
        if (!filter_var($ip, FILTER_VALIDATE_IP))
        {
            throw new Exception('ip地址不合法');
        }
        $params = array(
            'ak' => '百度apikey',
            'ip' => $ip,
            'coor' => 'bd09ll'//百度地图GPS坐标
        );
        $api = 'http://api.map.baidu.com/location/ip';
        $resp = $this->async($api, $params);
        $data = json_decode($resp, true);
        //有错误
        if ($data['status'] != 0)
        {
            throw new Exception($data['message']);
        }
        //返回地址信息
        return array(
            'address' => $data['content']['address'],
            'province' => $data['content']['address_detail']['province'],
            'city' => $data['content']['address_detail']['city'],
            'district' => $data['content']['address_detail']['district'],
            'street' => $data['content']['address_detail']['street'],
            'street_number' => $data['content']['address_detail']['street_number'],
            'city_code' => $data['content']['address_detail']['city_code'],
            'lng' => $data['content']['point']['x'],
            'lat' => $data['content']['point']['y']
        );
    }

    /**
     * 求两个已知经纬度之间的距离,单位为米
     *
     * @param lng1 $ ,lng2 经度
     * @param lat1 $ ,lat2 纬度
     * @return float 距离，单位米
     * @author www.Alixixi.com
     */
    function getdistance($lng1, $lat1, $lng2, $lat2) {
        // 将角度转为狐度
        $radLat1 = deg2rad($lat1); //deg2rad()函数将角度转换为弧度
        $radLat2 = deg2rad($lat2);
        $radLng1 = deg2rad($lng1);
        $radLng2 = deg2rad($lng2);
        $a = $radLat1 - $radLat2;
        $b = $radLng1 - $radLng2;
        $s = 2 * asin(sqrt(pow(sin($a / 2), 2) + cos($radLat1) * cos($radLat2) * pow(sin($b / 2), 2))) * 6378.137 * 1000;
        return $s;
    }






}






