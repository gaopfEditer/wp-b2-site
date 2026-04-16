<?php
use B2\Modules\Common\Post;
$token = isset($_GET['token']) ? $_GET['token'] : '';
if($token){
    //获取下载地址
    $url = Post::download_file($token);

    if(isset($url['error'])){
        echo $url['error'];
        exit;
    } 
    // var_dump($url);
    // exit;

    echo '<script language="JavaScript">  
        window.location.href = "'.$url.'"
    </script>';
}
exit;