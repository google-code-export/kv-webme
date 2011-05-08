<?php
/**
	* this file displays an invoice, as generated by the Online-Store
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
if (!is_admin()) {
	die('access denied');
}

if (!isset($_REQUEST['id'])) {
	exit;
}
$id=(int)$_REQUEST['id'];

$inv=dbOne('select invoice from online_store_orders where id='.$id, 'invoice');
if (strpos($inv, '<body')===false) {
	$inv='<body>'.$inv.'</body>';
}
if (isset($_REQUEST['print'])) {
	$inv=str_replace('<body', '<body onload="window.print()"', $inv);
}
echo $inv;
