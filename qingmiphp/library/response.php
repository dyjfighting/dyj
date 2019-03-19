<?php
class Response {
        private $headers = array();

        public function addheader($header) {
				$this -> headers[] = $header;
        }

        public function setheader($header) {
                header($header);
        }

        public function url($url, $status = 302) {
                header('Location:'.strtr($url, array('&amp;' => '&', "\n" => '', "\r" => '')), true, $status);
				exit;
        }

        public function redirect($url, $args = false, $status = 302, $jumptype = 'php') {
                    //$url = '?q='.$url;
                    if($args){
                            $url .= '&'.ltrim(strtr($args, array('&amp;' => '&')), '&');
                    }
                    $url = strtr($url, array('&amp;' => '&', "\n" => '', "\r" => ''));
                    //exit($url);
                    if ($jumptype == 'php') {
                            header('Location:'.$url, true, $status);
							exit;
                    } else {
                            exit('<script>window.top.location.href="'.$url.'"</script>');
                    }
        }

        public function json($array,$isreturn=false) {
            if (!defined(JSON_UNESCAPED_UNICODE)) {
                $jsondata = json_encode($array, JSON_UNESCAPED_UNICODE);
            } else {
                $jsondata =  json_encode($array);
            }
            if($isreturn){
                return $jsondata;
            }else{
                header('Content-type: application/json; charset=utf-8');
                exit($jsondata);
            }
        }

        public function jsonp($array, $callback = false) {
                header('Content-type: application/json; charset=utf-8');
                $request = new Request();
                $call = (!$callback) ? $request -> get('callback') : $callback;
                if (!$call) $this -> json($array);
                if (!defined(JSON_UNESCAPED_UNICODE)) {
                    echo $call.'('.json_encode($array, JSON_UNESCAPED_UNICODE).')';
                } else {
                    echo $call.'('.json_encode($array).')';
                }
        }

        public function header($code){
            switch ($code){
                case 404:
                        header('HTTP/1.1 404 Not Found');
                        header("status: 404 Not Found");
                        break;
                case 500:
                        header('HTTP/1.1 500 Internal Server Error');
                        break;
                default:
                        break;
            }
        }
        
        
        
        
}