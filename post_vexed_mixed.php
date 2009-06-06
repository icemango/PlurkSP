<?php
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

    $message = 'test!! (rock)';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie_test.txt');
    curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie_test.txt');

    // login
    curl_setopt($ch, CURLOPT_URL, 'http://www.plurk.com/Users/login');
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'nick_name='.$pName.'&password='.$pPasswd);
    curl_exec($ch);

    //get plurk uid
    curl_setopt($ch, CURLOPT_URL, "http://www.plurk.com/$pName");
    $res = curl_exec($ch);
    preg_match('/var GLOBAL = \{.*"uid": ([\d]+),.*\}/imU', $res,$matches);
    $uid =  $matches[1];

    // post
    curl_setopt($ch, CURLOPT_URL, 'http://www.plurk.com/TimeLine/addPlurk');
    curl_setopt($ch, CURLOPT_POSTFIELDS, 'qualifier=says&content='.urlencode($message).'&lang=tr_ch&no_comments=0&uid='.$uid);
    $res = curl_exec($ch);
    echo $res;

    curl_close($ch);

?>
