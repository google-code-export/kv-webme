<?php
$expected=2000; // acceptable number of issues

require_once '../config.php';
$errors=array();
function checkJs($rootdir) {
	global $errors;
	$dir=new DirectoryIterator($rootdir);
	foreach ($dir as $file) {
		if ($file->isDot()) {
			continue;
		}
		if ($file->isDir()) {
			$dirname=$file->getFilename();
			if (in_array($dirname, array(
				'CodeMirror-2.24',
				'featuredimagezoomer-1.51',
				'jquery.jqplot',
				'jquery.multiselect',
				'jquery.uploadify',
				'jquery.dataTables-1.7.5',
				'cluetip',
				'farbtastic',
				'dompdf',
				'farbtastic-1.3u',
				'jstree',
				'wpaudioplayer-2.2',
				'ckeditor-3.6.2',
				'kfm'
			))) {
				continue;
			}
			checkJs($rootdir.'/'.$dirname);
			continue;
		}
		if ($file->getExtension()!='js') {
			continue;
		}
		if (strpos($file->getFilename(), '.min.')!==false) {
			continue;
		}
		$fileToCheck=$rootdir.'/'.$file->getFilename();
		$opts='curly=true,quotmark=single,undef=true,unused=true,trailing=true'
			.',maxdepth=4,maxstatements=40,maxcomplexity=10,browser=true,jquery=true'
			.',maxerr=20';
		$result=shell_exec("../../jshint/jshint-rhino.js $fileToCheck $opts");
		$arr=array($fileToCheck, count(explode("\n", $result))/2);
		if ($arr[1]==0) {
			continue;
		}
		$errors[]=$arr;
	}
}
checkJs(realpath('../../run/trunk'));
$total=0;
$biggest_offender='';
$biggest_offender_num=0;
foreach ($errors as $line) {
	$issues=$line[1];
	if ($issues>$biggest_offender_num) {
		$biggest_offender_num=$issues;
		$biggest_offender=$line[0];
	}
	$total+=$issues;
}
if ($total==0) {
	echo '{"errors":"no formatting problems found... that\'s suspicious!"}';
}
elseif ($total<=$expected) {
	echo '{"notes":"'.$total.' problems found. This is acceptable.","ok":1}';
}
else {
	echo '{"errors":"'.$total.' problems found. This is above the allowed '
		.'limit of '.$expected.' problems. biggest problem found: '
		.addslashes($biggest_offender).'"}';
}
