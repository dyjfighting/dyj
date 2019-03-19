<?php
/*
 * Token模块
 */
class ModuleToken extends Module{
    const keyname = 'medbktoken';
    /**
     * 创建Token
     */
    public function create($key='medbk'){
        $token = sha1($key.TIME);
        $this->session->set(self::keyname,$token);
        return $token;
    }
    
    /**
     * 重置Token
     */
    public function reset($key='medbk'){
        return $this->create();
    }
    
    /**
     * 获取Token
     */
    public function get(){
        $token = $this->session->get(self::keyname);
        return $token;
    }
    
    /**
     * 删除Token
     */
    public function clear(){
        $this->session->clear(self::keyname);
        return true;
    }
    
    public function check($token){
        $s_token = $this->session->get(self::keyname);
        if($token==$s_token){
            return true;
        }else{
            return false;
        }
    }
    
    
}

