<?php

//引用大神的 code
require_once("clyang.php");

$fname_cfg = ".config";

//check config file exists or not
clearstatcache();	    //clear file state cache
if(file_exists($fname_cfg))
{
    // read config file
    $file_content = file($fname_cfg);
    $tmp_str = trim($file_content[0]);
    $pName = substr($tmp_str, 10);
    $tmp_str = trim($file_content[1]);
    $pPasswd = substr($tmp_str, 10);
}
else
{
    //open file and stdin
    $fp_cfg = @fopen($fname_cfg, 'w') or die("config file $fname_cfg can't open!\n");
    $fp_stdin = @fopen('/dev/stdin', 'r') or die("stdin can't open!\n");

    //interactive mode
    echo "Plurk Username: ";
    $pName = fgets($fp_stdin, 1024);
    echo "Plurk Password: ";
    $pPasswd = fgets($fp_stdin, 1024);
    fclose($fp_stdin);

    //write config file
    fwrite($fp_cfg, "Username: ");
    fwrite($fp_cfg, $pName);
    fwrite($fp_cfg, "Password: ");
    fwrite($fp_cfg, $pPasswd);
    fclose($fp_cfg);

    //change file permission
    chmod($fname_cfg, 0600);
}


//Plurk start

//initialize cURL and JSON class
$curl_handle = curl_init();
$json = new Services_JSON(SERVICES_JSON_LOOSE_TYPE);

//登入
$uid = getOwnUid();

//取得前一天的日期
$mm = date("m");
$dd = date("d");
$yy = date("Y");

$y_beg = mktime(0, 0, 0, $mm, $dd, $yy);
$y_end = mktime(23, 59, 59, $mm, $dd, $yy);

//切換時區至 GMT+0
if (version_compare(PHP_VERSION, '5.2.0', '>='))
    date_default_timezone_set('Europe/London');
else
    putenv("TZ=Europe/London");

//台灣與GMT+0的秒差
$taiwan = 8 * 60 * 60;

$from_date = urlencode(date('Y-m-d', $y_beg). "T" . date('H:i:s',$y_beg));
//$offset = urlencode(date('Y-m-d', $y_end). "T" . date('H:i:s',$y_end));
$offset = 0;

//取得所有plurk
$plurks = array_reverse(get_plurk($from_date,$offset,$uid));
$result = "";

for( $i = 10 ; $i <= 19 ; $i++ ){
    echo "$i\n";
    echo "\033[36m".getAuthor($plurks[$i]['owner_id'])."\033[m ".$plurks[$i]['qualifier'].': '.$plurks[$i]['content_raw']."\n\n";
}

?>
