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
	'http://kvwebmerun/a/f=adminPluginsInstallOne/name=ads'
);
$expected='{"ok":1,"added":["ads"],"removed":[]}';
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
$expected='{"panels":{"name":"Panels","description":"Allows content section'
	.'s to be displayed throughout the site.","version":5},"ads":{"name":"Ads'
	.'","description":"Add ads. Record each click and view.","version":9}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { test public API
$file=Curl_get(
	'http://kvwebmerun/a/p=ads/f=adsGetMy',
	array()
);
$expected='[]';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { load admin page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=ads&_page=ad-types',
	array()
);
$expected='New Ads Type';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { cleanup
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["ads"]}';
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
