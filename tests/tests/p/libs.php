<?php
function Curl_get($url, $post=array()) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIEJAR, '../../run/files/curl_cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, '../../run/files/curl_cookies.txt');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$ret=curl_exec($ch);
	curl_close($ch);
	return $ret;
}
