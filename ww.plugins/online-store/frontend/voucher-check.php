<?php
/**
	* check a voucher to see if it's valid
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
require_once dirname(__FILE__).'/voucher-libs.php';

$code=$_REQUEST['code'];
$email=$_REQUEST['email'];

$valid=OnlineStore_voucherCheckValidity($code, $email);
if ($valid['error']) {
	echo json_encode($valid);
}
else {
	echo '{"ok":1}';
}
