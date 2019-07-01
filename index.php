<?php
require_once __DIR__ . './vendor/autoload.php';
require_once 'config.php';
require_once 'component.php';

$klein = new \Klein\Klein();

$klein->respond('GET', $base . '/', function () {
    return file_get_contents("app.html");
});
$klein->respond('POST', $base . '/login', function () {
    json(login());
});
$klein->respond('GET', $base . '/nokori', function () {
    json(nokori());
});
$klein->respond('GET', $base . '/get', function () {
    json(get());
});
$klein->respond('POST', $base . '/post', function () {
    json(post());
});
$klein->onHttpError(function ($code, $router) {
    switch ($code) {
        case 404:
            $router->response()->body("404");
            break;
        default:
            $router->response()->body(
                $code
            );
    }
});

$klein->dispatch();

function login()
{
    $link = connectMysql();
    $request = json_decode(file_get_contents('php://input'), true);
    $at = $request["access_token"];
    if (!$at) {
        $error = "accesss token required";
        $errorja = "アクセストークン(Mastodon)が認識できません。";
        echo displayError($error, $errorja);
        exit();
    }
    $domain = INSTANCE;
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://" . $domain . "/api/v1/accounts/verify_credentials");
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $at]);
    curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
    $buf = curl_exec($curl);
    curl_close($curl);
    $json = json_decode($buf);
    if (!$json->username) {
        $error = "your access token or domain is invalid";
        $errorja = "アクセストークンまたはドメインが無効です。";
        echo displayError($error, $errorja);
        exit();
    }
    $name = $json->username;
    $token = get_token();
    $sql = "INSERT INTO " . DB2NAME . " (Token,User) VALUES ('$token','$name');";
    $result = $link->query($sql);
    if (!$result) {
        $error = "internal error(SQL)";
        echo displayError($error, "データベースエラー");
        exit();
    }
    $array = [
        "status" => "success",
        "token" => $token
    ];
    return $array;
}
function nokori()
{
    $link = connectMysql();
    $mass = "SELECT COUNT(*) FROM " . DBNAME . " WHERE Cat='diff' OR Cat='その他'";
    $flagmass = $link->query($mass);
    if (!$flagmass) {
        $error = "internal error(SQL)";
        echo displayError($error, "データベースエラー");
        exit();
    }
    $vct = $flagmass->fetch_assoc();
    $nokori = $vct["COUNT(*)"];
    $array = [
        "status" => "success",
        "nokori" => $nokori * 1
    ];
    return $array;
}
function get()
{
    $link = connectMysql();
    $get = "SELECT * FROM " . DBNAME . " WHERE Cat='diff' OR Cat='その他' ORDER BY RAND() LIMIT 1";
    $flagget = $link->query($get);
    if (!$flagget) {
        $error = "internal error(SQL)";
        echo displayError($error, "データベースエラー");
        exit();
    }
    $vct = $flagget->fetch_assoc();
    $url = $vct["URL"];
    $shortcode = $vct["Shortcode"];
    $array = [
        "status" => "success",
        "image" => $url,
        "shortcode" => $shortcode
    ];
    return $array;
}
function post()
{
    $link = connectMysql();
    $request = json_decode(file_get_contents('php://input'), true);
    $token = $request["token"];
    $sc = $request["shortcode"];
    $cat = $request["cat"];
    if (!$token) {
        $error = "the token required";
        $errorja = "トークンが認識できません。";
        echo displayError($error, $errorja);
        exit();
    }
    $user = "SELECT User FROM " . DB2NAME . " WHERE Token='$token'";
    $flaguser = $link->query($user);
    if (!$flaguser) {
        $error = "internal error(SQL)";
        echo displayError($error, "データベースエラー");
        exit();
    }
    $username = $flaguser->fetch_assoc();
    $username = $username["User"];
    $sql = "UPDATE " . DBNAME . " SET Cat='$cat',CreatedBy='$username' WHERE Shortcode='$sc'";
    $result = $link->query($sql);
    if (!$result) {
        $error = "internal error(SQL)";
        echo displayError($error, "データベースエラー");
        exit();
    }
    $array = [
        "status" => "success"
    ];
    return $array;
}
