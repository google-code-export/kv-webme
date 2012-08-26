<?php
function Curl_get($url, $post=array(), $output=false) {
//	$fname='../../run/files/curl_cookies.txt';
	$fname='/tmp/kvwebmecookie.txt';
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_COOKIEJAR, $fname);
	curl_setopt($ch, CURLOPT_COOKIEFILE, $fname);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
	curl_setopt($ch, CURLOPT_REFERER, 'http://kvwebmetests/');
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
	$ret=curl_exec($ch);
	curl_close($ch);
	unset($ch);
	return $ret;
}
