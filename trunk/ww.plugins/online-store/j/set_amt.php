<?php
/**
	* this ajax file sets how many of a specified product is in the basket
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@webworks.ie>
	* @license  GPL 2.0
	* @link     None
	*/

require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/basics.php';
$md5=$_REQUEST['md5'];
$id=preg_replace('/^products_([0-9]*)(,.*)?$/', '\1', $md5);
$md5=preg_replace('/^products_[0-9]*/', '', $md5);
if (!isset($_SESSION['online-store']['items']['products_'.$id.$md5])) {
 die('no such item');
}

$product=Product::getInstance($id);
require_once '../libs.php';
$amount=(int)$_REQUEST['amt']-$_SESSION['online-store']['items']['products_'.$id.$md5]['amt'];
list($price, $amount, $vat)=Products_getProductPrice(
	$product, $amount, $md5, false
);
if ($amount<1) {
	unset($_SESSION['online-store']['items']['products_'.$id.$md5]);
}
else {
	$_SESSION['online-store']['items']['products_'.$id.$md5]['cost']=$price;
	$_SESSION['online-store']['items']['products_'.$id.$md5]['amt']=$amount;
}

$total=OnlineStore_calculateTotal();
$item_total=$amount?$_SESSION['online-store']['items']['products_'.$id.$md5]['cost']*$amount:0;

echo '{'.
	'"md5": "'.'products_'.$id.$md5.'",'.
	'"amt":'.$amount.','
	.'"item_total":'.$item_total.','
	.'"total":'.$total
.'}';