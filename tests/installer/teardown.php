<?php
function rrmdir($dir) {
	$objects = scandir($dir);
	foreach ($objects as $object) {
		if ($object != "." && $object != "..") {
			if (filetype($dir."/".$object) == "dir") rrmdir($dir."/".$object);
			else unlink($dir."/".$object);
		}
	}
	reset($objects);
	rmdir($dir);
}
require_once dirname(__FILE__).'/../config.php';

class teardown extends PHPUnit_Framework_TestCase{
	public function testRemoveOldTrunk() {
		global $run_dir;
		@rrmdir($run_dir.'/trunk');
		$this->assertEquals(false, file_exists($run_dir.'/trunk'));
	}

	function testRemoveOldFiles() {
		global $run_dir;
		@rrmdir($run_dir.'/files');
		$this->assertEquals(false, file_exists($run_dir.'/files'));
	}

	function testRemoveOldXdebug() {
		global $run_dir;
		@rrmdir($run_dir.'/xdebug');
		$this->assertEquals(false, file_exists($run_dir.'/xdebug'));
	}

	function testRecreateDatabase() {
		global $run_dir;
		@unlink($run_dir.'/.htaccess');
		`echo "drop database kvwebmetest; create database kvwebmetest;" | mysql -uroot`;
	}
}
