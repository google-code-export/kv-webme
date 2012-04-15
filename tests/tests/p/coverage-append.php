<?php
if (defined('COVERAGE_ON')) {
	$stats_file=realpath(dirname(__FILE__).'/../../run/files/xdebug/coverage');
	foreach (xdebug_get_code_coverage() as $k=>$v) {
		$str=$k.' | ';
		$str.=join(',', array_keys($v));
		file_put_contents($stats_file, $str."\n", FILE_APPEND);
	}
}
