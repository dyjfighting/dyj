<?php
class cookie{
    private $di;
    public function __construct($di) {
        $this->di = $di;
    }
    public function __get($key) {
            return $this->di->get($key);
    }

    public function __set($key, $value) {
            $this->di->set($key, $value);
    }
    
    /**
     * 设置cookie
     * @param type $key
     * @param type $val
     * @param type $time
     * @param type $path
     */
    public function set($key,$val,$time=3600,$path='/'){
        $time = TIME + $time;
        $val = $this->base->encrypt($val);
        setcookie($key,$val,$time,$path);
    }
    
    /**
     * 获取cookie
     * @param type $key
     */
    public function get($key){
        if(!isset($this->request->cookie[$key])) return false;
        $val = $this->request->cookie[$key];
        return $this->base->decrypt($val);
    }
    
    /**
     * 清除cookie
     * @param type $key
     */
    public function clear($key){
        if(isset($this->request->cookie[$key])){
            unset($this->request->cookie[$key]);
        }
        $this->set($key,'',-10);
    }
    
}
