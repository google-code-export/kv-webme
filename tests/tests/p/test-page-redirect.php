<?php
require_once '../config.php';
require_once 'libs.php';

// { login
$file=Curl_get('http://kvwebmerun/a/f=login', array(
	'email'=>'testemail@localhost.test',
	'password'=>'password'
));
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, '<!-- end of WebME admin -->')===false) {
	die('{"errors":"failed to load admin page /ww.admin/ after logging in"}');
}
// }
// { add test page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'redirect',
	'type'  =>1
));
$expected='{"id":"4","pid":0,"alias":"redirect"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to add redirect page<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { edit the page to add the target
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'id'                    =>4,
	'name'                  =>'{"en":"redirect"}',
	'type'                  =>1,
	'date_publish'          =>'2001-01-01',
	'date_unpublish'        =>'2200-01-01',
	'associated_date'       =>'2001-01-01',
	'template'              =>'_default',
	'page_vars[redirect_to]'=>'/redirect/target'
));
$expected='{"id":4,"pid":"0","alias":"redirect"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to edit redirect page.<br />'
			.'expected: '.$expected.'<br/>'
			.'actual: '.$file
	)));
}
// }
// { add target page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>4,
	'name'  =>'target',
	'type'  =>0
));
$expected='{"id":"5","pid":4,"alias":"target"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to add redirect target page<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add some text to the target page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'id'             =>5,
	'name'           =>'{"en":"target"}',
	'type'           =>0,
	'date_publish'   =>'2001-01-01',
	'date_unpublish' =>'2200-01-01',
	'associated_date'=>'2001-01-01',
	'template'       =>'_default',
	'body[en]'       =>'redirection worked'
));
$expected='{"id":5,"pid":"4","alias":"target"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to edit redirect target page.<br />'
			.'expected: '.$expected.'<br/>'
			.'actual: '.$file
	)));
}
// }
// { check the redirection worked
$file=Curl_get('http://kvwebmerun/redirect', array());
if (strpos($file, 'redirection worked')===false) {
	die(json_encode(array(
		'errors'=>'redirection fails.<br />'
			.htmlspecialchars($file)
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
