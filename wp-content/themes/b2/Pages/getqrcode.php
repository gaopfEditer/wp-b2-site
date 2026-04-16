<?php
$str = isset($_GET['str']) ? $_GET['str'] : false;

if($str){
    require_once B2_THEME_DIR . '/Library/Qrcode/phpqrcode.php';
    $q = new \QRcode();
    QRcode::png($str, false, QR_ECLEVEL_L,6,1,false,array(255,255,255,0),array(0,0,0,0));
}

