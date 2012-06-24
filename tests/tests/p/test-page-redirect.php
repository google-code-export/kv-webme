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
// { add test page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'redirect',
	'type'  =>1
));
$expected='{"id":"2","pid":0,"alias":"redirect"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to add redirect page<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { edit the page to add the target
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'id'                    =>2,
	'name'                  =>'{"en":"redirect"}',
	'type'                  =>1,
	'date_publish'          =>'2001-01-01',
	'date_unpublish'        =>'2200-01-01',
	'associated_date'       =>'2001-01-01',
	'template'              =>'_default',
	'page_vars[redirect_to]'=>'/redirect/target'
));
$expected='{"id":2,"pid":"0","alias":"redirect"}';
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
	'parent'=>2,
	'name'  =>'target',
	'type'  =>0
));
$expected='{"id":"3","pid":2,"alias":"target"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to add redirect target page<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add some text to the target page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'id'             =>3,
	'name'           =>'{"en":"target"}',
	'type'           =>0,
	'date_publish'   =>'2001-01-01',
	'date_unpublish' =>'2200-01-01',
	'associated_date'=>'2001-01-01',
	'template'       =>'_default',
	'body[en]'       =>'redirection worked'
));
$expected='{"id":3,"pid":"2","alias":"target"}';
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
// { cleanup
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=3');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete redirect target page"}');
}
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete redirect page"}');
}
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=pages');
// }
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
