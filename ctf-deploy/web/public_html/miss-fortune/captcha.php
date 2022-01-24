<?php
session_start();
function genCaptcha(){
    $string = md5(time());
    $string = substr($string, 0, 6);
    $_SESSION['captcha'] = $string;
    $img = imagecreate(150,50);
    $background = imagecolorallocate($img, 0,0,0);
    $text_color = imagecolorallocate($img, 255,255,255);
    imagestring($img, 4,40,15, $string, $text_color);
    ob_start();
    imagepng($img);
    $data = ob_get_contents();
    ob_end_clean();
    $data = base64_encode( $data );
    return "data:image/png;base64,".$data;
}
?>