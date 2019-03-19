<?php
final class view{
        /**
        * 编译
        * @param unknown_type $tpl
        */
        public function compilefile($tpl,$htmlformat){
            $tpl=preg_replace_callback('/\{foreach(.*?)\}/i',array(&$this,'parse_foreach') , $tpl);
            $tpl=strtr($tpl,array('{/foreach}'=>'<?php }}?>'
                                                    ,'{elseforeach}'=>'<?php }}else{ if(1==1){?>'
                                                     ,'{/if}'=>'<?php }?>'
                                                     ,'{else}'=>'<?php }else{?>'
                                                     ,'{php}'=>'<?php'
                                                     ,'{/php}'=>'?>'));
            $tpl=preg_replace('/\{if\s+(.+?)\}/is', '<?php if(\1){ ?>', $tpl);
            $tpl=preg_replace('/\{elseif\s+(.+?)\}/is', '<?php }elseif(\1){ ?>', $tpl);
            $tpl=preg_replace('/\{\$(.*?)\}/i', '<?php echo $\1; ?>',$tpl);
            $tpl=preg_replace('/\{php\s+(.*?)\}/i', '<?php \1 ?>',$tpl);
            $tpl=preg_replace('/\{func (.*?)\}/i', '<?php echo \1 ?>',$tpl);
            $tpl=preg_replace('/\{__(.*?)__\}/is', '<?php echo \1; ?>',$tpl);
            $tpl=preg_replace('/\{include\s+action=(.+?)\}/i','<?php $action = new Action(\1);echo $action->execute($this->di);?>',$tpl);
            if($htmlformat){
                $tpl=preg_replace('/\{include\s+file=(.+?)\}/i','<?php $this->view(\1,$___sys__assign,false,false,true);?>',$tpl);
            }else{
                $tpl=preg_replace('/\{include\s+file=(.+?)\}/i','<?php $this->view(\1,$___sys__assign);?>',$tpl);
            }
            return $tpl;
        }
	
        /**
         * 解析循环
         * @param $matches
         */
        public function parse_foreach($matches){
            $tplcode=rtrim($matches[1]);
            $tplcodearray=explode(' ',$matches[1]);
            $temparray=array();
            foreach($tplcodearray as $value){
                    if(strpos($value,'=')){
                            $temparray[]=explode('=',$value);
                    }
            }
            $keys=null;
            $fordata=null;
            $values=null;
            foreach($temparray as $key=>$value){
                    if(strval($value[0])=='key'){
                            $keys=$value[1];
                    }elseif(strval($value[0])=='data'){
                            $fordata=$value[1];
                    }elseif(strval($value[0])=='value'){

                    $values=$value[1];
                    }
            }

            if($fordata==null || $value=null){
					qingmi::halt('编译出错： foreach 缺少关键参数');
            }

            if($keys==null){
                    $tpl='<?php if(count('.$fordata.')>0){ foreach('.$fordata.' as '.$values.'){ ?>';
            }else{
                    $tpl='<?php if(count('.$fordata.')>0){ foreach('.$fordata.' as '.$keys.'=>'.$values.'){ ?>';
            }
            return $tpl;
        }
}