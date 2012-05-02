<?php
function Curl_get($url, $post=array(), $output=false) {
	$ch = curl_init($url);
	$cookiefile=dirname(__FILE__).'/run/files/curl_cookies.txt';
	curl_setopt($ch, CURLOPT_COOKIEJAR, $cookiefile);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookiefile);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$ret=curl_exec($ch);
	if ($output) {
		var_dump(curl_getinfo($ch));
	}
	curl_close($ch);
	return $ret;
}
