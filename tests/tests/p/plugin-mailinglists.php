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
// { add Mailinglist plugin using InstallOne method
$file=Curl_get(
	'http://kvwebmerun/a/f=adminPluginsInstallOne/name=mailinglists'
);
$expected='{"ok":1,"added":["mailinglists"],"removed":[]}';
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
$expected='{"panels":{"name":"Panels","description":"Allows content'
	.' sections to be displayed throughout the site.","version":5},'
	.'"mailinglists":{"name":"Mailing Lists","description":"Mailing lists",'
	.'"version":"4"}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { load mailinglists details
$file=Curl_get('http://kvwebmerun/a/p=mailinglists/f=adminGetDashboardInfo');
$expected='{"numlists":0,"numpeople":0}';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'couldn\'t load list of mailinglists<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check with fake mailchimp details
$file=Curl_get(
	'http://kvwebmerun/a/p=mailinglists/f=adminListsGetMailChimp?selected=0'
	.'&other_GET_params='
);
$expected='{"error":104,"message":"API Key can not be blank"}';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'couldn\'t load list of MailChimp lists with fake data<br/>'
		.'expected:<br/>'
		.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { cleanup
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["mailinglists"]}';
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
