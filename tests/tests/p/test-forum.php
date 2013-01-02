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
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add forum plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=forum');
$expected='{"ok":1,"added":["forum"],"removed":[]}';
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
$expected='{"panels":{"name":"Panels","description":"Allows content sections'
	.' to be displayed throughout the site.","version":5},"forum":{"name":"For'
	.'um","description":"Add a forum to let your readers talk to each other","'
	.'version":6}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add a forum page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'forum',
	'type'  =>'forum'
));
$expected='{"id":"2","pid":0,"alias":"forum"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'forum page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load up the forum page admin
$file=Curl_get('http://kvwebmerun/ww.admin/pages/form.php?id=2');
$expected='administrators';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not load forum admin page.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check the front page
$file=Curl_get('http://kvwebmerun/forum');
$expected='forum has no threads';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not load forum admin page.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { try delete a non-existent message
$file=Curl_get('http://kvwebmerun/a/p=forum/f=delete/id=1');
$expected='{"error":"post does not exist"}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'failed to delete non-existent message<br/>'
				.'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { cleanup
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["forum"]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
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
