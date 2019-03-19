<?php
class ModulePhpcode extends Module{
           /**
            * 不带logo
            * @param type $data
            */
	function getqrcode($data,$matrixPointSize=8){
                include_once 'phpqrcode/qrlib.php';
                $errorCorrectionLevel = 'L';
                QRcode::png($data, false, $errorCorrectionLevel, $matrixPointSize, 2);
	}
           
         /**
         * 带logo
         * @param type $data
         * @param type $logo
         */
           function getqrcode_logo($data,$logo){
                include_once 'phpqrcode/qrlib.php';
                $errorCorrectionLevel = 'L';
                $matrixPointSize = 8;
                if($logo !== FALSE){
                    $qrpath = DIR_ROOT.md5($logo).'.png';
                    QRcode::png($data, $qrpath, $errorCorrectionLevel, $matrixPointSize, 2);
                    $QR = imagecreatefromstring(file_get_contents($qrpath));    
                    $logo = imagecreatefromstring(file_get_contents($logo));    
                    $QR_width = imagesx($QR);    
                    $QR_height = imagesy($QR);    
                    $logo_width = imagesx($logo);    
                    $logo_height = imagesy($logo);    
                    $logo_qr_width = $QR_width / 5;    
                    $scale = $logo_width / $logo_qr_width;    
                    $logo_qr_height = $logo_height / $scale;    
                    $from_width = ($QR_width - $logo_qr_width) / 2;    
                    imagecopyresampled($QR, $logo, $from_width, $from_width, 0, 0, $logo_qr_width, $logo_qr_height, $logo_width, $logo_height);
                    header('Content-Type:image/png');
                    imagepng($QR);
                    unlink($qrpath);
                }else{
                     QRcode::png($data, '', $errorCorrectionLevel, $matrixPointSize, 2);
                }
	}
        
        
}