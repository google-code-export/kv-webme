<?php
require_once '../config.php';
$run_dir=realpath('../../run');
function rrmdir($dir) {
	if (!file_exists($dir)) {
		return;
	}
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
rrmdir($run_dir.'/trunk');
if (file_exists($run_dir.'/trunk')) {
	echo '{"errors":"could not remove trunk"}';
	exit;
}
rrmdir($run_dir.'/files');
if (file_exists($run_dir.'/files')) {
	echo '{"errors":"could not remove files"}';
	exit;
}
rrmdir($run_dir.'/xdebug');
if (file_exists($run_dir.'/xdebug')) {
	echo '{"errors":"could not remove xdebug"}';
}
@unlink($run_dir.'/.htaccess');
@unlink('/tmp/kvwebmecookie.txt');
`echo "drop database kvwebmetest; create database kvwebmetest;" | mysql -uroot`;
echo '{"ok":1,"notes":""}';
