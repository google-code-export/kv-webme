<?php
require_once '../config.php';
$run_dir=realpath('../../run');

$lines_in_total=0;
$lines_covered=0;
$largest_offender='';
$largest_offender_size=0;
$largest_offender_coverage=100;

function testCoverageFile($file) {
	global $run_dir, $largest_offender, $largest_offender_size,
		$largest_offender_coverage, $lines_in_total, $lines_covered;
	$coverage=file($run_dir.'/files/xdebug/coverage');
	$flines=file($file);
	$lines_in_file=count($flines);
	$lines_tested=array();
	foreach ($coverage as $data) {
		if ($data=='') {
			continue;
		}
		$parts=explode(' | ', $data);
		if ($parts[0]!=$file) {
			continue;
		}
		$lines=explode(',', $parts[1]);
		foreach ($lines as $line) {
			if (!in_array($line, $lines_tested)) {
				$lines_tested[]=$line;
			}
		}
	}
	$percent=0;
	if (count($lines_tested)) {
		$lines_not_tested=0;
		sort($lines_tested);
#		echo '<br/><strong>'.$file.'</strong><br/>';
		for ($i=0;$i<$lines_in_file;++$i) {
			if (in_array($i, $lines_tested)) {
				continue;
			}
			$line=$flines[$i];
			$line=trim($line);
			if ($line==''
				|| preg_match('/^(\*|\/|public|global|}|static|function|class)/', $line)
				|| preg_match('/^(if |foreach |elseif |else |<)/', $line)
			) {
				continue;
			}
#			echo htmlspecialchars($line).'<br/>'."\n";
			$lines_not_tested++;
		}
#		echo join(', ', $lines_tested);
		$lines_in_file = $lines_not_tested + count($lines_tested);
		$percent=count($lines_tested)/$lines_in_file;
	}
	$lines_in_total+=$lines_in_file;
	$lines_covered+=count($lines_tested);
	if ($percent<$largest_offender_coverage) {
		$largest_offender=$file;
		$largest_offender_size=filesize($file);
		$largest_offender_coverage=$percent;
	}
	elseif ($percent==$largest_offender_coverage
		&& filesize($file)>$largest_offender_size
	) {
		$largest_offender=$file;
		$largest_offender_size=filesize($file);
	}
}
function testCoverageDirectory($dir) {
	$files=new DirectoryIterator($dir);
	foreach ($files as $file) {
		if ($file->isDot()) {
			continue;
		}
		if ($file->isDir()) {
			// { ignore external libraries
			if (in_array($file->getFilename(), array(
				'kfm',
				'Minify',
				'Smarty-2.6.26',
				'recaptcha-php-1.11',
				'ckeditor-3.6.2'
			))) {
				continue;
			}
			// }
			// { ignore tmp files
			if (in_array($file->getFilename(), array(
				'ww.cache'
			))) {
				continue;
			}
			// }
			testCoverageDirectory($dir.'/'.$file->getFilename());
			continue;
		}
		if ($file->getExtension()=='php') {
			// { ignore any files that are not mine...
			if (in_array($file->getFilename(), array(
				'phpqrcode.php',
				'AmazonProductAPI.php'
			))) {
				continue;
			}
			// }
			testCoverageFile($dir.'/'.$file->getFileName());
		}
	}
}
testCoverageDirectory($run_dir.'/trunk');

$coverage=100*$lines_covered/$lines_in_total;
$largest_offender_coverage*=100;
echo '{"notes":"Code coverage: '.$coverage.'% ('.$lines_covered.' out of '
	.$lines_in_total.'). Largest offender is '
	.$largest_offender.' which has a coverage of '.$largest_offender_coverage
	.'%"}';
