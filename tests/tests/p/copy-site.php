<?php
require_once '../config.php';
$run_dir=realpath('../../run');
mkdir($run_dir.'/files');
`rsync ../../../trunk $run_dir/ -r --exclude '*svn*'`;
echo '{"ok":1,"notes":"actual time may vary"}';
