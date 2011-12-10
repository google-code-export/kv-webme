<?php
function Curl_get($url, $post=array(), $output=false) {
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIEJAR, '../../run/files/curl_cookies.txt');
	curl_setopt($ch, CURLOPT_COOKIEFILE, '../../run/files/curl_cookies.txt');
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$ret=curl_exec($ch);
	if ($output) {
		var_dump(curl_getinfo($ch));
	}
	curl_close($ch);
	return $ret;
}
