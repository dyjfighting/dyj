<?php
namespace Driver\Cache;
class File {
	private $expire;
	public function __construct($expire = 3600) {
		$this->expire = $expire;
	}
        
	public function get($key) {
                    $files = glob(DIR_CACHE . 'cache.' . $key . '.*');
                    if($files && file_exists($files[0])){
                        $time = substr(strrchr($files[0], '.'), 1);
                        if($time < time()){
                             unlink($files[0]);
                        }else{
                            $handle = fopen($files[0], 'r');
                            flock($handle, LOCK_SH);
                            $data = fread($handle, filesize($files[0]));
                            flock($handle, LOCK_UN);
                            fclose($handle);
                            return unserialize($data);
                        }
                    }
                    return false;
	}

	public function set($key, $value) {
		$this->delete($key);
		$file = DIR_CACHE . 'cache.' . $key. '.' . (time() + $this->expire);
		$handle = fopen($file, 'w');
		flock($handle, LOCK_EX);
		fwrite($handle, serialize($value));
		fflush($handle);
		flock($handle, LOCK_UN);
		fclose($handle);
	}



	public function delete($key) {
		$files = glob(DIR_CACHE . 'cache.' . $key. '.*');
		if ($files) {
			foreach ($files as $file) {
				if (file_exists($file)) {
					unlink($file);
				}
			}
		}
	}

}