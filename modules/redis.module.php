<?php

class ModuleRedis extends Module{

    private $handler = null;

    protected function init(){
        if ($this->handler == null) {
            $this->handler = new Redis();
            $this->handler->connect($this->config->get('redis.host'), $this->config->get('redis.port'));
            $this->handler->auth($this->config->get('redis.password')); //密码验证
            $this->handler->select($this->config->get('redis.select'));//选择数据库
        }
    }

    ##########  String  ##########
    public function set($key, $value,$expire = false){
        $this->init();
        return $this->handler->set($key, $value);
    }
    ## 获取一个
    public function get($key){
        $this->init();
        return $this->handler->get($key);    
    }
    ## 减少一个
    public function incr($key){
        $this->init();
        return $this->handler->incr($key);    
    }
    ## 增加一个
    public function decr($key){
        $this->init();
        return $this->handler->incr($key);    
    }



    
}