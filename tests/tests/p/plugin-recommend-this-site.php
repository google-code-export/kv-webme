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
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled', array());
$expected='{"panels":{"name":"Panels","description":"Allows content sections'
	.' to be displayed throughout the site.","version":5}}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add plugin
$file=Curl_get(
	'http://kvwebmerun/a/f=adminPluginsInstallOne/name=recommend-this-site'
);
$expected='{"ok":1,"added":["recommend-this-site"],"removed":[]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
$file=Curl_get('http://kvwebmerun/a/f=nothing');
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled');
$expected='{"panels":{"name":"Panels","description":"Allows content sections to'
	.' be displayed throughout the site.","version":5},"recommend-this-site":{"na'
	.'me":"Recommend This Site","description":"Let visitors send an email to a fr'
	.'iend about your site","version":"0"}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add a page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'recommend-this-site',
	'type'  =>'recommend-this-site'
));
$expected='{"id":"2","pid":0,"alias":"recommend-this-site"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load edit page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/pages/form.php?id=2',
	array()
);
if (strpos($file, 'recommendthissite_emailtothefriend_subject')===false) {
	die('{"errors":"failed to load edit page"}');
}
// }
// { remove pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes?id=0');
if ($file!='[{"data":"Home","attr":{"id":"page_1"},"children":false}]') {
	die(
		'{"errors":"failed to list pages after deleting test pages: '
		.addslashes($file).'"}'
	);
}
// }
// { cleanup
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["recommend-this-site"]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
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
