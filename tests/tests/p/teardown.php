<?php
require_once '../config.php';
$run_dir=realpath('../../run');
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
rrmdir($run_dir.'/trunk');
rrmdir($run_dir.'/files');
`echo "drop table groups; drop table user_accounts; drop table users_groups;" | mysql -uroot kvwebmetest`;
echo '{"ok":1,"notes":"actual time may vary"}';
