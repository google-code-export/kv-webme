<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die("access denied");

$id=(int)$_REQUEST['id'];
$widgets=addslashes($_REQUEST['data']);
dbQuery("update panels set body='$widgets' where id=$id");
Core_cacheClear('panels');
Core_cacheClear('pages');
