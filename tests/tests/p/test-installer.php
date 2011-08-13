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
if (!$file || strpos($file, 'document.location="/install/step2.php";')===false) {
	die('{"errors":"failed when checking correct data in step 1"}');
}
// }
// { check that database installs
$file=Curl_get('http://kvwebmerun/install/step2.php');
if (!$file || strpos($file, 'document.location="/install/step3.php";')===false) {
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
if (!$file || strpos($file, 'Your WebME installation is complete')===false) {
	die('{"errors":"could not load final page in wizard"}');
}
// }
// { finally, check that maintenance page is active
$file=Curl_get('http://kvwebmerun/Home');
$file=Curl_get('http://kvwebmerun/ww.incs/upgrade.php');
$file=Curl_get('http://kvwebmerun/ww.admin/');
$file=Curl_get('http://kvwebmerun/Home');
if (!$file || strpos($file, 'This is your new website')===false) {
	die('{"errors":"final step failed"}');
}
// }

echo '{"ok":1}';
