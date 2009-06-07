<?php
// Written by clyang.
// Require JSON and Mail library from PEAR.

require_once('JSON.php');

function getPermalink($plurk_id) {
    if (!is_int($plurk_id)) return '';
        
    return "http://www.plurk.com/p/" . base_convert($plurk_id, 10, 36);
}

function getResponses($plurk_id) {
    global $curl_handle, $json;
    
    curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/Responses/get2');
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, 'plurk_id='.$plurk_id);
    $cnt = curl_exec($curl_handle);
    return $json->decode($cnt);
}

function uid2name($uid) {
    global $curl_handle, $json;
    
    curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/Users/fetchUserInfo');
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, 'user_id='.$uid);
    $tmp = curl_exec($curl_handle);
    $res = $json->decode($tmp);
    
    if($res['display_name'] != '') return $res['display_name'];
    else                           return $res['nick_name'];
}

function get_plurk($from_date, $offset, $uid) {
    global $curl_handle, $json;

    curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/TimeLine/getPlurks');
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "user_id=$uid&from_date=%22$from_date%22&&offset=%22$offset%22");
    $cnt = curl_exec($curl_handle);
    return  $json->decode($cnt);
}

function strwidth($s){
    $ret = mb_strwidth($s, 'UTF-8');
    return $ret;
}

function mb_wordwrap($str, $wid, $tag){
    $pos = 0;
    $tok = array();
    $l = mb_strlen($str, 'UTF-8');

    if($l == 0) return '';

    $flag = false;
    $tok[0] = mb_substr($str, 0, 1, 'UTF-8');
    
    for($i = 1 ; $i < $l ; ++$i){
        $c = mb_substr($str, $i, 1, 'UTF-8');
        if(!preg_match('/[a-z\'\"]/i',$c)){
            ++$pos;
            $flag = true;
        }elseif($flag){
            ++$pos;
            $flag = false;
        }
        $tok[$pos] .= $c;
    }

    $linewidth = 0;
    $pos = 0;
    $ret = array();
    $l = count($tok);
    for($i = 0 ; $i < $l ; ++$i){
        if($linewidth + ($w = strwidth($tok[$i])) > $wid){
            ++$pos;
            $linewidth = 0;
        }
        $ret[$pos] .= $tok[$i];
        $linewidth += $w;
    }
    return implode($tag, $ret);
} 

function getAuthor($uid){
    global $friend_list;
    
    if(empty($friend_list[$uid]))
         $friend_list[$uid]['author'] = uid2name($uid);

    return $friend_list[$uid]['author'];
}

function convertTime($str) {
    $arr_month = array('January' => 1,'February' => 2,
                       'March' => 3,'April' => 4,'May' => 5,'June' => 6,
                       'July' => 7,'August' => 8,'September' => 9,
                       'October' => 10,'November' => 11,'December' => 12);
    
    $str = str_replace(array(","," at"),"",$str);
    $str = str_replace("PM"," pm",$str);
    $str = str_replace("AM"," am",$str);
    $str = str_replace(":"," ",$str);
    $piece = explode(" ",$str);
    $mm = $arr_month[$piece[0]]; $dd = (int)$piece[1]; $yy = (int)$piece[2];
    $min = (int)$piece[4]; $hour = ($piece[5] == "pm") ? (int)$piece[3]+12 : (int)$piece[3];
    return mktime($hour, $min, 0, $mm, $dd, $yy);
}

function checkTime($link, $s, $e) {
    global $taiwan;
    
    //取得該plurk發布的時間
    $cPlurk = file_get_contents($link);
    preg_match("/div class=\"time\".*/",$cPlurk,$matches);
    $getTime = str_replace(array("div class=\"time\">posted on ","</div>"),"",$matches[0]);
    $t = convertTime($getTime);
    
    //取得其他回應的時間
    $resp_time = array();
    preg_match_all("/span class=\"time\">.*/", $cPlurk,$matches);
    foreach($matches[0] as $rTime)
        $resp_time[] = date('H:i',convertTime(str_replace(array("span class=\"time\">","</span>"),"",$rTime)));
    
    //只有在前一天回應的才算
    if($t >= $s && $t <= $e )    return array(date('g:ia',$t+$taiwan),$resp_time);
    else                         return '';
}

function getOwnUid(){
    global $curl_handle,$pName,$pPasswd;

    //登入plurk
    curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($curl_handle, CURLOPT_COOKIEJAR, 'cookie.txt');
    curl_setopt($curl_handle, CURLOPT_COOKIEFILE, 'cookie.txt');
    curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/Users/login');
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "nick_name=$pName&password=$pPasswd");
    curl_exec($curl_handle);

    //取得plurk內部id
    curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/$pName');
    $res = curl_exec($curl_handle);
//    echo $res;
    preg_match('/var GLOBAL = \{.*"uid": ([\d]+),.*\}/imU', $res,$matches);
    return $matches[1];
}                                                    
?>
