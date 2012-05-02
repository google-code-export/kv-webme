<?php
require_once dirname(__FILE__).'/../config.php';
require_once dirname(__FILE__).'/../libs.php';

class testInstaller extends PHPUnit_Framework_TestCase{
	// check that the root index.php loads correctly
	function testRootLoads() {
		$file=file_get_contents('http://kvwebmerun/');
		$this->assertEquals(
			false,
			!$file || strpos($file, '"/install/index.php"')===false
		);
	}

	/**
		* check that installer starts up
		*
		* @depends testRootLoads
		*/
	function testInstallerStartsUp() {
		$file=file_get_contents('http://kvwebmerun/install/index.php');
		$this->assertEquals(
			false,
			!$file || strpos($file, '<a href="step1.php">Continue</a>')===false
		);
	}

	/**
		* check that step 1 loads
		*
		* @depends testInstallerStartsUp
		*/
	function testStep1Loads() {
		$file=file_get_contents('http://kvwebmerun/install/step1.php');
		$this->assertEquals(
			false,
			!$file || strpos($file, '<form method="post" id="database-form">')===false
		);
	}

	/**
		* fake data should fail
		*
		* @depends testStep1Loads
		*/
	function testFakeDbDataShouldFail() {
		$file=Curl_get('http://kvwebmerun/install/step1.php', array(
			'username'=>'a',
			'password'=>'b',
			'hostname'=>'c',
			'db_name'=>'d',
			'action'=>'Configure Database'
		));
		$this->assertEquals(
			false,
			!$file || strpos($file, 'Please check your values and try again')===false
		);
	}

	/**
		* check that valid values work
		*
		* @depends testFakeDbDataShouldFail
		*/
	function testValidDbData() {
		global $dbuser, $dbpass, $dbhost, $dbname;
		$file=Curl_get('http://kvwebmerun/install/step1.php', array(
			'username'=>$dbuser,
			'password'=>$dbpass,
			'hostname'=>$dbhost,
			'db_name'=>$dbname,
			'action'=>'Configure Database'
		));
		$this->assertEquals(
			false,
			$file && strpos($file, "connect to local MySQL")!==false
		);
		$this->assertEquals(
			false,
			!$file || strpos($file, 'document.location="/install/step2.php";')===false
		);
	}

	/**
		* check that database installs
		*
		* @depends testValidDbData
		*/
	function testDbInstalls() {
		$file=Curl_get('http://kvwebmerun/install/step2.php');
		$this->assertEquals(
			true,
			$file && strpos($file, 'document.location="/install/step3.php";')
		);
	}

	/**
		* load the user account creator page
		*
		* @depends testDbInstalls
		*/
	function testUserAccountCreatorLoads() {
		$file=Curl_get('http://kvwebmerun/install/step3.php');
		$this->assertEquals(
			true,
			$file && strpos($file, 'Create Admin')
		);
	}

	/**
		* check with incorrect data
		*
		* @depends testUserAccountCreatorLoads
		*/
	function testUserAccountWithIncorrectData() {
		$file=Curl_get('http://kvwebmerun/install/step3.php', array(
			'name'=>'',
			'email'=>'fakeemail',
			'password'=>'pass1',
			'password2'=>'pass2',
			'action'=>'Create Admin'
		));
		$this->assertTrue(strpos($file, '<body>')!==false);
		$this->assertEquals(
			false,
			strpos($file, 'Passwords do not match or are empty.')===false
		);
		$this->assertEquals(
			false,
			strpos($file, 'Email not valid.')===false
		);
		$this->assertEquals(
			false,
			strpos($file, 'Name is empty.')===false
		);
	}

	/**
		* check with correct data
		*
		* @depends testUserAccountWithIncorrectData
		*/
	function testUserAccountWithCorrectData() {
		$file=Curl_get('http://kvwebmerun/install/step3.php', array(
			'name'=>'testname',
			'email'=>'testemail@localhost.test',
			'password'=>'password',
			'password2'=>'password',
			'action'=>'Create Admin'
		));
		$this->assertEquals(
			false,
			!$file || strpos($file, 'document.location="/install/step4.php"')===false
		);
	}

	/**
		* userbase
		*
		* @depends testUserAccountWithCorrectData
		*/
	function testUserbase() {
		$file=Curl_get('http://kvwebmerun/install/step4.php');
		$this->assertEquals(
			false,
			!$file || strpos($file, 'outside the web')===false
		);
	}
	/*
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
echo '{"ok":1}';
*/
}
