<?php
require_once '../config.php';
require_once 'libs.php';

// { login
$file=Curl_get('http://kvwebmerun/a/f=login', array(
	'email'=>'testemail@localhost.test',
	'password'=>'password'
));
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, '<!-- end of admin -->')===false) {
	die('{"errors":"failed to load admin page /ww.admin/ after logging in"}');
}
// }
// { load user management page
$file=Curl_get('http://kvwebmerun/ww.admin/siteoptions.php?page=users');
$expected='List Users';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'user management page not loaded.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load admin profile
$file=Curl_get('http://kvwebmerun/ww.admin/siteoptions.php?page=users&id=1');
$expected='Mobile';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not load user profile.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
