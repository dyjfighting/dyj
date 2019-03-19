<?php

class ModuleCrmAes extends Module
{

    const USERINFO_KEY = 'qJzGEh6hESZDVJeCnFPGuxzaiB7NLQM3';

    const CRM_KEY = 'fd184d62755746ddac04972e7d37742e';

    // 加密
    public function decrypt($str)
    {
        $key       = self::CRM_KEY;
        $iv        = '';
        $str       = base64_decode($str);
        $encrypted = @mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB, $iv);
        return $encrypted;
    }

    // 加密解密
    public function encrypt($str)
    {
        $key       = self::CRM_KEY;
        $iv        = '';
        $encrypted = @mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $str, MCRYPT_MODE_ECB, $iv);
        return base64_encode($encrypted);
    }

    // 内容管理
    public function decryptUserinfo($g)
    {
        $data = $this->_decrypt($g);
        return $data;
    }

    private function _encrypt($input)
    {
        $key   = self::USERINFO_KEY;
        $key   = substr($key, 0, 8);
        $iv    = substr($key, 0, 8);
        $size  = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC); //3DES加密将MCRYPT_DES改为MCRYPT_3DES
        $input = $this->_pkcs5Pad($input, $size); //如果采用PaddingPKCS7，请更换成PaddingPKCS7方法。
        $td    = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, '');
        @mcrypt_generic_init($td, $key, $iv);
        $data = mcrypt_generic($td, $input);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $data = base64_encode($data); //如需转换二进制可改成 bin2hex 转换
        return $data;
    }
    private function _decrypt($encrypted)
    {

        $key       = self::USERINFO_KEY;
        $key       = substr($key, 0, 8);
        $iv        = substr($key, 0, 8);
        $encrypted = base64_decode($encrypted); //如需转换二进制可改成 bin2hex 转换
        $td        = mcrypt_module_open(MCRYPT_DES, '', MCRYPT_MODE_CBC, ''); //3DES加密将MCRYPT_DES改为MCRYPT_3DES
        $ks        = mcrypt_enc_get_key_size($td);
        @mcrypt_generic_init($td, $key, $iv);
        $decrypted = mdecrypt_generic($td, $encrypted);
        mcrypt_generic_deinit($td);
        mcrypt_module_close($td);
        $y = $this->_pkcs5Unpad($decrypted);
        return $y;
    }
    private function _pkcs5Pad($text, $blocksize)
    {
        $pad = $blocksize - (strlen($text) % $blocksize);
        return $text . str_repeat(chr($pad), $pad);
    }
    private function _pkcs5Unpad($text)
    {
        $pad = ord($text{strlen($text) - 1});
        if ($pad > strlen($text)) {
            return false;
        }
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) {
            return false;
        }
        return substr($text, 0, -1 * $pad);
    }
    private function _PaddingPKCS7($data)
    {
        $block_size   = mcrypt_get_block_size(MCRYPT_DES, MCRYPT_MODE_CBC); //3DES加密将MCRYPT_DES改为MCRYPT_3DES
        $padding_char = $block_size - (strlen($data) % $block_size);
        $data .= str_repeat(chr($padding_char), $padding_char);
        return $data;
    }

}
