<?php
ini_set("display_errors", 1);
ini_set("display_startup_errors", 1);
require("../config.php");
require("../component.php");
$link = connectMysql();
$create = "CREATE TABLE " . DBNAME . " ( `ID` int(5) NOT NULL,`Shortcode` varchar(100) CHARACTER SET latin1 NOT NULL,`URL` varchar(500) CHARACTER SET latin1 NOT NULL,`Cat` varchar(30) NOT NULL,`CreatedBy` varchar(50) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;ALTER TABLE " . DBNAME . "ADD PRIMARY KEY (`ID`);ALTER TABLE " . DBNAME . "MODIFY `ID` int(5) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5602;COMMIT;CREATE TABLE " . DB2NAME . " (`ID` int(3) NOT NULL,`Token` varchar(50) NOT NULL,`User` varchar(100) NOT NULL) ENGINE=InnoDB DEFAULT CHARSET=utf8;ALTER TABLE " . DB2NAME . "ADD PRIMARY KEY (`ID`);ALTER TABLE " . DB2NAME . " MODIFY `ID` int(3) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;COMMIT;";
$result_flag = $link->query($create);
if (!$result_flag) {
    die('create query error');
}
$url = "https://" . INSTANCE . "/api/v1/custom_emojis";
$data = [];
$results = http_req($url, $data);
$api = $results["content"];
$api = json_decode($api);
if (empty($api)) {
    echo 'RError';
    exit();
}
for ($l == 0; $l < count($api); $l++) {
    $emojis = $api[$l];
    $sc = $emojis->shortcode;
    $url = $emojis->url;
    $cat = "undefined";
    $sql = "INSERT INTO " . DBNAME . " (Shortcode,URL,Cat) VALUES ('$sc','$url','$cat')";
    $result_flag = $link->query($sql);
    if (!$result_flag) {
        die('ins query error');
    }
}
function http_req($url, $data)
{
    $data_url = http_build_query($data);
    $data_len = strlen($data_url);

    return array(
        'content' =>  file_get_contents(
            $url,
            false,
            stream_context_create(
                array(
                    'http' =>
                    array(
                        'method' => 'GET',
                        'header' => "",
                        'content' => $data_url
                    )
                )
            )
        ),
        'headers' => $http_response_header
    );
}
?>
</pre>