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
function Email_getMbox($user) {
	$settings=$GLOBALS['emailSettings'][$user];
	$mbox = imap_open(
		'{'.$settings['server'].':143/imap/novalidate-cert}INBOX',
		$settings['username'],
		$settings['password']
	);
	if ($mbox===false) {
		die('failed to open mailbox');
	}
	return $mbox;
}
function Email_empty($user) {
	$mbox=Email_getMbox($user);
	$numMails=imap_num_msg($mbox);
	for ($i=$numMails;$i;--$i) {
		imap_delete($mbox, $i);
	}
	imap_close($mbox, CL_EXPUNGE);
	return true;
}
function Email_getOne($user) {
	$found=0;
	$pow=0;
	do {
		sleep(pow(2, $pow++));
		$mbox=Email_getMbox($user);
		$numMails=imap_num_msg($mbox);
		if ($numMails) {
			$found=1;
		}
		else {
			imap_close($mbox);
		}
	} while(!$found && $pow<6);
	if (!$found) {
		return false;
	}
	return array(
		'header'=>imap_headerinfo($mbox, 1),
		'body'=>imap_body($mbox, 1)
	);
}
