<?php
function connectMysql()
{
    $link = new mysqli(DB_HOST, DB_USER, DB_PASSWORD, DB_NAME);
    if ($link->connect_error) {
        $error = "internal error(SQL)";
        echo displayError($error, "データベースエラー");
        exit();
    } else {
        $link->set_charset(DB_CHARSET);
    }
    return $link;
}
function json($json)
{
    header("Access-Control-Allow-Origin:*");
    header("Access-Control-Max-Age:86400");
    header("Access-Control-Allow-Methods: GET,POST,PUT,PATCH,DELETE,HEAD,OPTIONS");
    header("Access-Control-Allow-Headers: content-type,Accept,X-Custom-Header");
    header("Content-Type: application/json; charset=utf-8");
    date_default_timezone_set('UTC');
    echo json_encode($json);
}
function get_token() {
    $TOKEN_LENGTH = 16;//16*2=32桁
    $bytes = openssl_random_pseudo_bytes($TOKEN_LENGTH);
    return bin2hex($bytes);
}
function makeRandStr($length) {
    $str = array_merge(range('a', 'z'), range('0', '9'), range('A', 'Z'));
    $r_str = null;
    for ($i = 0; $i < $length; $i++) {
        $r_str .= $str[rand(0, count($str) - 1)];
    }
    return $r_str;
}
function displayError($code, $ja)
{
    $array = [
        "status" => "error",
        "data" => $code,
        "data_ja" => $ja
    ];
    return json($array);
}