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
// { check limits on page body size
// { set limit to 50 characters
$file=Curl_get(
	'http://kvwebmerun/ww.admin/siteoptions.php?page=general',
	array(
		'MAX_FILE_SIZE'=>9999999,
		'site_title'=>'Site Title',
		'site_subtitle'=>'Website\'s Subtitle',
		'canonical_name'=>'',
		'site_page_length_limit'=>50,
		'f_cache'=>0,
		'disable-hidden-sitemap'=>0,
		'maintenance-mode'=>'No',
		'maintenance-mode-ips'=>'192.168.2.104',
		'maintenance-mode-message'=>'blah',
		'disable-jqueryui-css'=>0,
		'site_thousands_sep'=>',',
		'site_dec_point'=>'.',
		'action'=>'Save'
	)
);
$expected='options updated';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'could not update site options.<br/>expected: '
				.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { create a page with a long body
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'test long page',
	'type'  =>0
));
if ($file!='{"id":"4","pid":0,"alias":"test long page"}') {
	die('{"errors":"failed to add long page test"}');
}
// }
// { now give it a long body and see what happens
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'id'    =>4,
	'name'  =>'test long page',
	'type'  =>0,
	'body'  =>str_repeat('blah ', 1000)
));
$expected='{"id":4,"pid":"0","alias":"test long page"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to add content to long page.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check the length of the page
$file=Curl_get('http://kvwebmerun/test-long-page');
if (strlen($file)>5000) {
	die('{"errors":"page body was not shortened. length:'.strlen($file).'"}');
}
// }
// }
// { delete remaining test pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=3');
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=4');
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
