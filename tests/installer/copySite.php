<?php
function chmod_R($path, $filemode, $dirmode) { 
	if (is_dir($path) ) { 
		if (!chmod($path, $dirmode)) { 
			$dirmode_str=decoct($dirmode); 
			print "Failed applying filemode '$dirmode_str' on directory '$path'\n"; 
			print "  `-> the directory '$path' will be skipped from recursive chmod\n"; 
			return; 
		} 
		$dh = opendir($path); 
		while (($file = readdir($dh)) !== false) { 
			if($file != '.' && $file != '..') {  // skip self and parent pointing directories 
				$fullpath = $path.'/'.$file; 
				chmod_R($fullpath, $filemode, $dirmode); 
			} 
		} 
		closedir($dh); 
	} else { 
		if (is_link($path)) { 
			print "link '$path' is skipped\n"; 
			return; 
		} 
		if (!chmod($path, $filemode)) { 
			$filemode_str=decoct($filemode); 
			print "Failed applying filemode '$filemode_str' on file '$path'\n"; 
			return; 
		} 
	} 
}
require_once dirname(__FILE__).'/../config.php';

class copySite extends PHPUnit_Framework_TestCase{
	public function testCreateNewFiles() {
		global $run_dir;
		@mkdir($run_dir.'/files');
		$this->assertEquals(true, file_exists($run_dir.'/files'));
	}

	public function testCopySiteFiles() {
		global $run_dir;
		$str="rsync $run_dir/../../trunk $run_dir/ -ra --exclude '*svn*'"
			." --exclude 'ww.plugins/_*'";
		`$str`;
		$this->assertEquals(true, file_exists($run_dir.'/trunk'));
	}

	/**
		* @depends testCreateNewFiles
		* @depends testCopySiteFiles
		*/
	public function testFinishUp() {
		global $run_dir;
		unlink($run_dir.'/trunk/.private/config.php');
		chmod_R($run_dir.'/trunk', 0744, 0755);
	}
}
