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
// { check current home page
$file=Curl_get('http://kvwebmerun/Home', array());
if (strpos($file, 'If you have forgotten')===false) {
	die('{"errors":"failed to load front page"}');
}
// }
// { edit the page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'id'             =>1,
	'name'           =>'{"en":"test2"}',
	'type'           =>0,
	'title'          =>'test3',
	'keywords'       =>'test4',
	'description'    =>'test5',
	'date_publish'   =>'2001-01-01',
	'date_unpublish' =>'2200-01-01',
	'associated_date'=>'2001-01-01',
	'template'       =>'_default',
	'special[0]'     =>'on',
	'body[en]'       =>'<p>test2</p>'
));
$expected='{"id":1,"pid":"0","alias":"test2"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to edit home page.<br />'
			.'expected: '.$expected.'<br/>'
			.'actual: '.$file
	)));
}
// }
// { check home page again
$file=Curl_get('http://kvwebmerun/test2', array());
if (strpos($file, '<p>test2</p>')===false) {
	die('{"errors":"failed to load front page (after edit)"}');
}
if (strpos($file, '<title>test3</title>')===false) {
	die('{"errors":"failed to edit front page title"}');
}
if (strpos($file, '<meta http-equiv="description" content="test5"/>')===false) {
	die('{"errors":"failed to edit front page description"}');
}
if (strpos($file, '<meta http-equiv="keywords" content="test4" />')===false) {
	die('{"errors":"failed to edit front page keywords"}');
}
// }
// { cleanup
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'id'             =>1,
	'name'           =>'{"en":"Home"}',
	'type'           =>0,
	'title'          =>'test3',
	'keywords'       =>'test4',
	'description'    =>'test5',
	'date_publish'   =>'2001-01-01',
	'date_unpublish' =>'2200-01-01',
	'associated_date'=>'2001-01-01',
	'template'       =>'_default',
	'special[0]'     =>'on',
	'body[en]'       =>'<p>test2</p>'
));
$expected='{"id":1,"pid":"0","alias":"home"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to restore home page.<br />'
			.'expected: '.$expected.'<br/>'
			.'actual: '.$file
	)));
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
