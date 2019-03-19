<?php
/* 
 *  Copyright © 2013~2017 QingMi All rights reserved.
 *  Author: Lzsen <lzs.vip@qq.com>
 */
class ModulePage extends Module{
	
    private $url;         //当前URL
	private $total_rows;  //一共多少条数据
	private $list_rows;   //每页显示记录数
	private $total_page;  //总的分页数
	private $now_page; 	  //当前页
	private $parameter;   //分页跳转的参数


	
	/**
	 * 获取全部列表---首页上页[1][2][3][4][5]下页尾页
	 */
	public function getfull($total_rows, $list_rows = 10, $parameter = array()){
		$this->total_rows = $total_rows;
		$this->list_rows = $list_rows; 
		$this->total_page = ceil($this->total_rows/$this->list_rows); 
		$this->now_page = $this->request->get('page') ? intval($this->request->get('page')) : 1;
		$this->now_page = $this->now_page>0 ? $this->now_page : 1;
		$this->now_page = $this->now_page<$this->total_page ? $this->now_page : $this->total_page;
        $this->parameter  = empty($parameter) ? $this->request->get : $parameter;		

        $this->url = $this->geturl();
		if($this->total_rows == 0) return '';
	    return ($this->gethome()).($this->getpre()).($this->getlist()).($this->getnext()).($this->getend());
	}


	/**
	 * 获得当前地址
	 */
	protected function geturl(){
		$this->parameter['page'] = 'PAGE';
		$route = trim(strchr($this->request->get(ROUTEQUERY),'/'),'/');

		unset($this->parameter[ROUTEQUERY]);
		
		return $this->base->geturl($route,$this->parameter);
	}
	
	

			
	/**
	 * 生成链接URL
	 */
    private function make_url($page){
        return str_replace('PAGE', $page, $this->url);
    }

	
	/**
	 * 总页数
	 */
	function total(){
		return $this->total_page;
	}

	
	/**
	 * 获得当前页
	 */
	public function getpage(){
		return $this->now_page;
	}

	
	/**
	 * 获得首页
	 */
	public function gethome(){	
		return '<li class="left"><a href="'.$this->make_url(1).'" class="isbd bg5">首页</a></li>';
	}

	
	/**
	 * 获得尾页
	 */
	function getend(){	
		return '<li class="left"><a href="'.$this->make_url($this->total_page).'" class="isbd bg5">尾页</a></li>';
	}

	
	/**
	 * 获得上页
	 */
	public function getpre(){
		if($this->now_page<=1){
			return '<li class="left"><a href="'.$this->make_url(1).'" class="isbd bg5">上一页</a></li>';
		}
		return '<li class="left"><a href="'.$this->make_url($this->now_page-1).'" class="isbd bg5">上一页</a></li>';
	}

	
	/**
	 * 获得下页
	 */
	public function getnext(){
		if($this->now_page>=$this->total_page){
			return '<li class="left"><a href="'.$this->make_url($this->now_page).'" class="isbd bg5">下一页</a></li>';	
		}
		return '<li class="left"><a href="'.$this->make_url($this->now_page+1).'" class="isbd bg5">下一页</a></li>';
	}
	
	/**
	 * 获得总页数
	 */
	public function gettotal(){
		return '<li class="left">共'.$this->total().'页</li>';
	}
	

	/**
	 * 获取开始数列
	 */
	public function start_rows(){ 
		if($this->total_page && $this->now_page > $this->total_page) $this->now_page = $this->total_page;
		return ($this->now_page-1)*($this->list_rows);
	}
	

	/**
	 * 每页显示的条数
	 */
	public function list_rows(){
		return $this->list_rows;
	}	
	
	
	/**
	 * 供外部分页使用
	 */
	public function limit(){
		return $this->start_rows().','.$this->list_rows();
	}	
	
	
	/**
	 * 数字数字列表页---[1][2][3][4][5]
	 */
	public function getlist(){
		$str = '';
		if($this->total_page<=5){
			for($i=1; $i<=$this->total_page; $i++){
				$class = $this->now_page==$i ? ' actived' : '';
				$str.='<li class="left '.$class.'"><a href="'.$this->make_url($i).'" class="isbd bg5">'.$i.'</a></li>';
			}
		}else{	
			if($this->now_page <= 3){
				$p =5;
			}else{
				$p = ($this->now_page+2)>=$this->total_page ? $this->total_page : $this->now_page+2;
			} 
			for($i=$p-4; $i<=$p; $i++){
				$class = $this->now_page==$i ? ' actived' : '';
				$str.='<li class="left '.$class.'"><a href="'.$this->make_url($i).'" class="isbd bg5">'.$i.'</a></li>';
			}
		}
		return $str;
	}	
	
	

 
    
    
}






