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
// { get current list of root pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes', array(
	'id'=>'0'
));
$expected='[{"data":"Home","attr":{"id":"page_1"},"children":false}]';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to get current list of root pages.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add test page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'test',
	'type'  =>0
));
if ($file!='{"id":"2","pid":0,"alias":"test"}') {
	die('{"errors":"failed to add test page"}');
}
// }
// { check new list of root pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes?id=0');
if ($file!='[{"data":"Home","attr":{"id":"page_1"},"children":false},{"data":"test","attr":{"id":"page_2"},"children":false}]') {
	die('{"errors":"failed to list pages after adding root page"}');
}
// }
// { add test sub-page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>2,
	'name'  =>'test',
	'type'  =>0
));
if ($file!='{"id":"3","pid":2,"alias":"test"}') {
	die('{"errors":"failed to add test sub-page"}');
}
// }
// { check new list of root pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes?id=0');
if ($file!='[{"data":"Home","attr":{"id":"page_1"},"children":false},{"data":"test","attr":{"id":"page_2"},"children":[]}]') {
	die('{"errors":"failed to list pages after adding test sub-page"}');
}
// }
// { delete main page
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete test page"}');
}
// }
// { check new list of root pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes?id=0');
if ($file!='[{"data":"Home","attr":{"id":"page_1"},"children":false},{"data":"test","attr":{"id":"page_3"},"children":false}]') {
	die('{"errors":"failed to list pages after deleting test root page"}');
}
// }
// { delete remaining test page
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=3');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete test page"}');
}
// }
// { check new list of root pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes?id=0');
if ($file!='[{"data":"Home","attr":{"id":"page_1"},"children":false}]') {
	die('{"errors":"failed to list pages after deleting remaining test page"}');
}
// }
// { cleanup
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
