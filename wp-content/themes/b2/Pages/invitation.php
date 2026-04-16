<?php
use \Firebase\JWT\JWT;
$token = isset($_GET['token']) ? esc_attr($_GET['token']) : false;
if(!$token){
    wp_die(__('参数错误','b2'));
}

//解码
$decoded = JWT::decode($token, AUTH_KEY,array('HS256'));

if(!isset($decoded->data->access_token) || !isset($decoded->data->uid) || !isset($decoded->data->type)){
    wp_die(__('Token 过期或错误，请重新登录','b2'));
}

