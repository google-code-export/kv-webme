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
$file=Curl_get('http://kvwebmerun/home', array()); // reset panels
// }
// { add MessagingNotifier plugin using InstallOne method
$file=Curl_get(
	'http://kvwebmerun/a/f=adminPluginsInstallOne/name=messaging-notifier'
);
$expected='{"ok":1,"added":["messaging-notifier"],"removed":[]}';
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
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled', array());
$expected='{"panels":{"name":"Panels","description":"Allows content sections'
	.' to be displayed throughout the site.","version":5},'
	.'"messaging-notifier":{"name":"Feed Reader",'
	.'"description":"Show messages from feeds such as twitter, rss, phpbb3",'
	.'"version":"3"}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add widget to sidebar
$file=Curl_get(
	'http://kvwebmerun/a/p=panels/f=adminSave/id=2',
	array(
		'data'=>'{"widgets":[{"type":"messaging-notifier","name":"Feed Reader",'
		.'"panel":""}]}'
	)
);
$expected='null';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { check front-end
$file=Curl_get('http://kvwebmerun/home', array());
if (strpos($file, 'panel-widget-messaging-notifier')===false) {
	die('{"errors":"failed to add messaging notifier widget"}');
}
// }
// { remove widget from sidebar
$file=Curl_get(
	'http://kvwebmerun/ww.plugins/panels/admin/remove-panel.php?id=3',
	array()
);
// }
// { cleanup
// { remove plugins
$file=Curl_get(
	'http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["messaging-notifier"]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=panels');
// }
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
