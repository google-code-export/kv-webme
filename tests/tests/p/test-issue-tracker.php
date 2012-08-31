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
// { add issue-tracker plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=issue-tracker');
$expected='{"ok":1,"added":["issue-tracker"],"removed":[]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled');
$expected='{"panels":{"name":"Panels","description":"Allows content sections'
	.' to be displayed throughout the site.","version":5},"issue-tracker":{"na'
	.'me":"Issue Tracker","description":"project management, issue tracking, t'
	.'ask management","version":9}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add an issue-tracker page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'issue-tracker',
	'type'  =>'issue-tracker'
));
$expected='{"id":"2","pid":0,"alias":"issue-tracker"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'issue-tracker page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check projects (should be empty array)
$file=Curl_get('http://kvwebmerun/a/p=issue-tracker/f=projectsGet');
$expected='[]';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { cleanup
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["issue-tracker"]}';
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
