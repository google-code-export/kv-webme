<?php
require_once '../config.php';
require_once 'libs.php';

// { try load up admin page
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to load admin page /ww.admin/"}');
}
// }
// { try login with no parameters
$file=Curl_get('http://kvwebmerun/a/f=login', array());
if ($file!='{"error":"either the email address or the password are incorrect"}') {
	die('{"errors":"login with no parameters returned incorrect response"}');
}
// }
// { try login with correct parameters
$file=Curl_get('http://kvwebmerun/a/f=login', array(
	'email'=>'testemail@localhost.test',
	'password'=>'password'
	));
if ($file!='{"ok":1}') {
	die('{"errors":"failed to login with correct data"}');
}
// }
// { try load up admin page again
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'page generated in')===false) {
	die('{"errors":"failed to load admin page /ww.admin/ after logging in"}');
}
// }
// { try logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
