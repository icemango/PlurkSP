<?php

    //require library
    require_once('JSON.php');

    //config file name
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

    //get plurk uid
    $uid = getOwnUid();

    $message = 'Test version 4 BB~!!';

    //post Plurk
    curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/TimeLine/addPlurk');
    curl_setopt($curl_handle, CURLOPT_POSTFIELDS, 'qualifier=says&content='.urlencode($message).'&lang=tr_ch&no_comments=0&uid='.$uid);
    curl_exec($curl_handle);

    curl_close($curl_handle);


    function getOwnUid()
    {
	global $curl_handle, $pName, $pPasswd;

	//login Plurk
	curl_setopt($curl_handle, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl_handle, CURLOPT_COOKIEJAR, 'cookie.txt');
	curl_setopt($curl_handle, CURLOPT_COOKIEFILE, 'cookie.txt');
	curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/User/login');
	curl_setopt($curl_handle, CURLOPT_POSTFIELDS, "nick_name=$pName&password=$pPasswd");
	curl_exec($curl_handle);

	//get plurk uid
	curl_setopt($curl_handle, CURLOPT_URL, 'http://www.plurk.com/$pName');
	$res = curl_exec($curl_handle);
	preg_match('/var GLOBAL = \{.*"uid": ([\d]+),.*\}/imU', $res,$matches);
	return $matches[1];
    }

?>
