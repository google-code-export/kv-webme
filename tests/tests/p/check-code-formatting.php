<?php
$expected=251; // acceptable number of issues

require_once '../config.php';
$run_dir=realpath('../../run');
$files_to_check="$run_dir/trunk/index.php"
	." $run_dir/trunk/install"
	." $run_dir/trunk/ww.admin"
	." $run_dir/trunk/ww.css/all.php"
	." $run_dir/trunk/ww.incs/*php"
	." $run_dir/trunk/ww.php_classes"
	." $run_dir/trunk/ww.plugins"
	;
$res=shell_exec("phpcs --extensions=php --standard=../../../phpcs-standards/WebME/ruleset.xml --report=summary $files_to_check");
$lines=explode("\n", $res);
$total=0;
$biggest_offender='';
$biggest_offender_num=0;
foreach ($lines as $line) {
	if (!preg_match('/[0-9] +[0-9]/', $line)) {
		continue;
	}
	$numbers=preg_replace('/.*[^0-9]([0-9]+) +([0-9]+)/', '\1|\2', $line);
	$numbers=explode('|', $numbers);
	$issues=(int)$numbers[0]+(int)$numbers[1];
	if ($issues>=$biggest_offender_num) {
		$biggest_offender_num=$issues;
		$biggest_offender=$line;
	}
	$total+=$issues;
}
if ($total==0) {
	echo '{"errors":"no formatting problems found... that\'s suspicious!","ok":1}';
	exit;
}
echo '{"notes":"'.$total.' problems found. biggest problem found: '
	.addslashes($biggest_offender).'","ok":1}';
