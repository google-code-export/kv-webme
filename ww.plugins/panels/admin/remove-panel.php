<?php
require $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if(!is_admin())die('access denied');

if(isset($_REQUEST['id'])){
	$id=(int)$_REQUEST['id'];
	dbQuery("delete from panels where id=$id");
	Core_cacheClear('panels');
}
