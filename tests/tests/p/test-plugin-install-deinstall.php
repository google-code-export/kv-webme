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
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled', array());
$expected='{"panels":{"name":"Panels","description":"Allows content '
	.'sections to be displayed throughout the site.","version":5}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { check current list of available plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetAvailable', array());
$expected='{"name":"Forum","description":"'; // }
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.substr($file, 0, 200).'...'
		))
	);
}
// }
// { install Backup plugin
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array(
		'plugins[backup]'=>'on',
		'plugins[panels]'=>'on'
	)
);
$expected='{"ok":1}';
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
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled', array());
$expected='{"backup":{"name":"Backup","description":"backup your website, o'
	.'r replace with an old backup","version":"0"},"panels":{"name":"Panels",'
	.'"description":"Allows content sections to be displayed throughout the s'
	.'ite.","version":5}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { now try add backup plugin again using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=backup');
$expected='{"ok":1,"message":"already installed"}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { try add a non-existing plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=nosuchplug');
$expected='{"ok":0,"message":"plugin not found"}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add Products plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=products');
$expected='{"ok":1}';
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
$expected='{"backup":{"name":"Backup","description":"backup your website, o'
	.'r replace with an old backup","version":"0"},"panels":{"name":"Panels",'
	.'"description":"Allows content sections to be displayed throughout the s'
	.'ite.","version":5},"products":{"name":"Products","description":"Product'
	.' catalogue.","version":"39"}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { try remove a non-existent plugin with RemoveOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsRemoveOne/name=nosuchplug');
$expected='{"ok":1,"message":"already removed"}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { remove Products plugin with RemoveOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsRemoveOne/name=products');
$expected='{"ok":1}';
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
$expected='{"backup":{"name":"Backup","description":"backup your website, o'
	.'r replace with an old backup","version":"0"},"panels":{"name":"Panels",'
	.'"description":"Allows content sections to be displayed throughout the s'
	.'ite.","version":5}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { remove Backup plugin
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array(
		'plugins[panels]'=>'on'
	)
);
$expected='{"ok":1}';
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
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled', array());
$expected='{"panels":{"name":"Panels","description":"Allows content '
	.'sections to be displayed throughout the site.","version":5}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
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
