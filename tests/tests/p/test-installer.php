<?php
require_once '../config.php';
require_once 'libs.php';

// { check that the root index.php loads correctly
$file=file_get_contents('http://kvwebmerun/');
if (!$file || strpos($file, '"/install/index.php"')===false) {
	die('{"errors":"could not load http://kvwebmerun/"}');
}
// }
// { check that installer starts up
$file=file_get_contents('http://kvwebmerun/install/index.php');
if (!$file || strpos($file, '<a href="step1.php">Continue</a>')===false) {
	die('{"errors":"could not load /install/index.php"}');
}
// }
// { check that step 1 loads
$file=file_get_contents('http://kvwebmerun/install/step1.php');
if (!$file || strpos($file, '<form method="post" id="database-form">')===false) {
	die('{"errors":"could not load /install/step1.php"}');
}
// }
// { fake data should fail
$file=Curl_get('http://kvwebmerun/install/step1.php', array(
	'username'=>'a',
	'password'=>'b',
	'hostname'=>'c',
	'db_name'=>'d',
	'action'=>'Configure Database'
));
if (!$file || strpos($file, 'Please check your values and try again')===false) {
	die('{"errors":"failed when checking incorrect data in step 1"}');
}
// } 
// { check that valid values work
$file=Curl_get('http://kvwebmerun/install/step1.php', array(
	'username'=>$dbuser,
	'password'=>$dbpass,
	'hostname'=>$dbhost,
	'db_name'=>$dbname,
	'action'=>'Configure Database'
));
if ($file && strpos($file, "connect to local MySQL")!==false) {
	die('{"errors":"database appears to be down"}');
}
if (!$file || strpos($file, 'document.location="/install/step2.php";')===false) {
	die('{"errors":"failed when checking correct data in step 1"}');
}
// }
// { check that database installs
$file=Curl_get('http://kvwebmerun/install/step2.php');
if (!$file
	|| strpos($file, 'document.location="/install/step3.php";')===false
) {
	die('{"errors":"failed when installing database"}');
}
// }
// { load the user account creator page
$file=Curl_get('http://kvwebmerun/install/step3.php');
if (!$file || strpos($file, 'Create Admin')===false) {
	die('{"errors":"failed when loading user account creator"}');
}
// }
// { check with incorrect data
$file=Curl_get('http://kvwebmerun/install/step3.php', array(
	'name'=>'',
	'email'=>'fakeemail',
	'password'=>'pass1',
	'password2'=>'pass2',
	'action'=>'Create Admin'
));
if (!$file) {
	die('{"errors":"failed to load /install/step3.php with parameters"}');
}
if (strpos($file, 'Passwords do not match or are empty.')===false) {
	die('{"errors":"failed to detect mismatched passwords"}');
}
if (strpos($file, 'Email not valid.')===false) {
	die('{"errors":"failed to detect invalid email address"}');
}
if (strpos($file, 'Name is empty.')===false) {
	die('{"errors":"failed to detect missing name"}');
}
// }
// { check with correct data
$file=Curl_get('http://kvwebmerun/install/step3.php', array(
	'name'=>'testname',
	'email'=>'testemail@localhost.test',
	'password'=>'password',
	'password2'=>'password',
	'action'=>'Create Admin'
));
if (!$file || strpos($file, 'document.location="/install/step4.php"')===false) {
	die('{"errors":"failed to create user account"}');
}
// }
// { userbase
$file=Curl_get('http://kvwebmerun/install/step4.php');
if (!$file || strpos($file, 'outside the web')===false) {
	die('{"errors":"failed when loading file locations page"}');
}
// }
// { check invalid userbase
$file=Curl_get('http://kvwebmerun/install/step4.php', array(
	'userbase'=>'/'
));
if (!$file || strpos($file, 'is not writable')===false) {
	die('{"errors":"was able to enter an invalid userbase"}');
}
// }
// { check valid userbase
$userbase=realpath('../../run/files');
$file=Curl_get('http://kvwebmerun/install/step4.php', array(
	'userbase'=>$userbase
));
if (!$file || strpos($file, 'document.location="/install/step5.php"')===false) {
	die('{"errors":"valid userbase was not accepted"}');
}
// }
// { install config.php
$file=Curl_get('http://kvwebmerun/install/step5.php');
if (!$file || strpos($file, 'document.location="/install/step6.php"')===false) {
	die('{"errors":"could not install config.php file"}');
}
// }
// { add "testmode" flag to config file
$f=file_get_contents('../../run/trunk/.private/config.php');
file_put_contents(
	'../../run/trunk/.private/config.php',
	str_replace(
		"'username' => 'root',",
		"'username'=>'root','testmode'=>true,",
		$f
	)
);
// }
// { add code coverage to htaccess
@mkdir($userbase.'/xdebug', 0777);
file_put_contents($userbase.'/xdebug/coverage', '');
file_put_contents(
	'../../run/.htaccess',
	"php_flag xdebug.profiler_enable On\n"
	.'php_value auto_prepend_file "'.dirname(__FILE__)
	.'/coverage-prepend.php"'."\n"
	.'php_value auto_append_file "'.dirname(__FILE__)
	.'/coverage-append.php"'
);
chmod('../../run/.htaccess', 0644);
// }
// { load theme installer
$file=Curl_get('http://kvwebmerun/install/step6.php');
if (!$file || strpos($file, 'Select Themes')===false) {
	die('{"errors":"could not load theme installer"}');
}
// }
// { try the theme installation
$file=Curl_get('http://kvwebmerun/install/step6.php', array(
	'theme_id'=>115,
	'install-theme'=>1
));
if (!$file || strpos($file, 'document.location="/install/step7.php";')===false) {
	die('{"errors":"installation of a theme failed"}');
}
// }
// { load final page in wizard
$file=Curl_get('http://kvwebmerun/install/step7.php');
if (!$file || strpos($file, 'installation is complete')===false) {
	die('{"errors":"could not load final page in wizard"}');
}
// }
// { finally, check that installation is complete
$file=Curl_get('http://kvwebmerun/Home');
$file=Curl_get('http://kvwebmerun/ww.incs/upgrade.php');
$file=Curl_get('http://kvwebmerun/ww.admin/');
$file=Curl_get('http://kvwebmerun/Home');
if (!$file || strpos($file, 'This is your new website')===false) {
	die('{"errors":"final step failed"}');
}
// }
// { test that theme actually was loaded
$file=file_get_contents('http://kvwebmerun/ww.skins/altruism/c/images/img02.gif');
$md5=md5($file);
if ($md5!='20ed57b97193f592d20ab2408af7ad58') {
	die('{"errors":"failed to load theme image"}');
}
// }
// { upload a test theme
$file=Curl_get(
	'http://kvwebmerun/install/theme-upload.php',
	array(
		'theme-zip'=>'@../../files/uncovered.zip',
		'upload-theme'=>'Upload'
	)
);
// }

echo '{"ok":1}';
