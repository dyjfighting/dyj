<?php

class Request {
    public $get = array();
    public $post = array();
    public $cookie = array();
    public $files = array();
    public $server = array();
    public function __construct() {
            $this->get = $_GET;
            $this->post = $_POST;
            $this->request = $_REQUEST;
            $this->cookie = $_COOKIE;
            $this->files = $_FILES;
            $this->server = $_SERVER;
    }
    /**
     * 安全获取GET参数
     */
    public function get($key,$removexss=true){
        if(ROUTEQUERY==$key){
            return isset($this->get[$key])?$this->get[$key]:false;
        }
        if(isset($this->get[$key])) 
            return $this->filters($this->get[$key],$removexss);
        return false;
    }
    
     /**
     * 安全获取POST参数
     */
    public function post($key,$removexss=true){

        if(isset($this->post[$key])) 
            return $this->filters($this->post[$key],$removexss);
        return false;
    }
    
    /**
     * 过滤
     * @param type $string
     * @param type $removexss
     * @return type
     */
    private function filters($string,$removexss) {
        if(!is_array($string)){
            if($removexss){
                return $this->filterxss($string);
            }else{
                return $string;
                //return htmlspecialchars($string, ENT_COMPAT, 'UTF-8');
            }
        }
        foreach($string as $key => $val){
            $string[$key] = $this->filters($val,$removexss);
        }
        return $string;
    }
    
    /**
     * 过滤XSS
     * @param type $val
     * @return type
     */
    private function filterxss($val) {

        $val = preg_replace('/([\x00-\x08,\x0b-\x0c,\x0e-\x19])/', '', $val);
        $val = strtr($val,["\x0b"=>'',"'"=>'',"\""=>'']);
        // $val = strtr($val,[' '=>'',"\x0b"=>'',"'"=>'',"\""=>'']);
        $search = 'abcdefghijklmnopqrstuvwxyz'; 
        $search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';  
        $search .= '1234567890!@#$%^&*()'; 
        $search .= '~`";:?+/={}[]-_|\'\\'; 
        for ($i = 0; $i < strlen($search); $i++) { 
           $val = preg_replace('/(&#[xX]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val);
           $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val);
        }
        $ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title'); 
        $ra2 = Array('onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload'); 
        // sql;
        $ra3 = Array('select','update','union','create','drop','join','backup','truncate','replace','load_file','outfile','infile','delete','databases','tables','index','columns','execute','count','table');
        $rrr = 's';
        $_data = '0123456789qwertyuiopasdfghjklzxcvbnm';
        for($_d=0;$_d<=5;$_d++){
            $rrr .= substr($_data,rand(0,35),1);
        }
        $val = strtr($val,['select'=>$rrr,'update'=>$rrr,'union'=>$rrr,'create'=>$rrr,'drop'=>$rrr,'join'=>$rrr,'backup'=>$rrr,'truncate'=>$rrr,'replace'=>$rrr,'load_file'=>$rrr,'outfile'=>$rrr,'infile'=>$rrr,'delete'=>$rrr,'databases'=>$rrr,'tables'=>$rrr,'index'=>$rrr,'columns'=>$rrr,'execute'=>$rrr,'count'=>$rrr,'table'=>$rrr]);
        $ra = array_merge($ra1, $ra2, $ra3);

        $found = true;
        while ($found == true) { 
           $val_before = $val; 
           for ($i = 0; $i < sizeof($ra); $i++) { 
              $pattern = '/'; 
              for ($j = 0; $j < strlen($ra[$i]); $j++) { 
                 if ($j > 0) { 
                    $pattern .= '(';  
                    $pattern .= '(&#[xX]0{0,8}([9ab]);)'; 
                    $pattern .= '|';  
                    $pattern .= '|(&#0{0,8}([9|10|13]);)'; 
                    $pattern .= ')*'; 
                 } 
                 $pattern .= $ra[$i][$j]; 
              } 
              $pattern .= '/i';
              $val = preg_replace($pattern, '', $val);
              if ($val_before == $val) { 
                 $found = false;  
              }  
           }  
        }  
        return strip_tags($val);  
     }   
    
    

}