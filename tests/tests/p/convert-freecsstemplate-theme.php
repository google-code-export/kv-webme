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
// { upload a test theme
$file=Curl_get(
	'http://kvwebmerun/ww.admin/siteoptions/themes/theme-upload.php',
	array(
		'theme-zip'=>'@../../files/uncovered.zip',
		'upload-theme'=>'Upload'
	)
);
// }
// { see what templates are installed now
$file=Curl_get(
	'http://kvwebmerun/ww.admin/siteoptions.php?page=themes'
);
$expected='/ww.skins/uncovered/screenshot.png';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { delete the test theme
$file=Curl_get(
	'http://kvwebmerun/ww.admin/siteoptions.php?page=themes&action=install',
	array(
		'theme_name'=>'uncovered',
		'delete-theme'=>'Delete'
	)
);
// }
// { see what templates are installed now
$file=Curl_get(
	'http://kvwebmerun/ww.admin/siteoptions.php?page=themes'
);
$expected='/ww.skins/uncovered/screenshot.png';
if (strpos($file, $expected)!==false) {
	die(
		json_encode(array(
			'errors'=>'failed to delete theme'
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
