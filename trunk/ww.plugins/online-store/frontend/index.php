<?php
/**
	* Online-Store front-end page type
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { OnlineStore_getCountriesSelectbox

/**
  * function for showing list of countries selected
  *
	* @param array  $params  Smarty parameters
	* @param object &$smarty Smarty object
	*
  * @return string the HTML
  */
function OnlineStore_getCountriesSelectbox($params, &$smarty) {
	$page=Page::getInstance($_SESSION['onlinestore_checkout_page']);
	$cjson=$page->vars['online-store-countries'];
	$required=@$params['prefix']?'':' required="required"';
	$countries='<select name="'.(@$params['prefix']).'Country"'.$required.'>'
		.'<option value="" class="__" lang-context="core"> -- choose -- </option>';
	if ($cjson) {
		$cjson=json_decode($cjson);
		foreach ($cjson as $country=>$val) {
			$countries.='<option>'.htmlspecialchars($country).'</option>';
		}
	}
	return $countries.'</select>';
}

// }
// { OnlineStore_showVoucherInput

/**
  * function for showing HTML of a voucher input
  *
  * @return string the HTML
  */
function OnlineStore_showVoucherInput() {
	$code=@$_REQUEST['os_voucher'];
	return '<div id="os-voucher"><span class="__">Voucher Code:</span> '
		.'<input name="os_voucher" value="'.htmlspecialchars($code).'"/></div>';
}

// }

// { setup
if (isset($PAGEDATA->vars['online_stores_requires_login'])
	&& $PAGEDATA->vars['online_stores_requires_login']
	&& !isset($_SESSION['userdata'])
) {
	$c='<h2 class="__" lang-context="core">Login Required</h2>'
		.'<p class="__" lang-context="core">You must be logged-in in order to '
		.'use this online store. Please '
		.'<a href="/_r?type=privacy">login / register</a> to access the checkout.'
		.'</p>';
	return;
}
WW_addScript('online-store/j/basket.js');
$c='';
global $DBVARS,$online_store_currencies;
$submitted=0;
// }
// { handle a submitted checkout
if (@$_REQUEST['action'] && !(@$_REQUEST['os_no_submit']==1)) {
	$errors=array();
	$uid=isset($_SESSION['userdata']) && $_SESSION['userdata']['id']
		?(int)$_SESSION['userdata']['id']:0;
	// { check for errors in form submission
	$fields=$PAGEDATA->vars['online_stores_fields'];
	if (!$fields) {
		$fields='{}';
	}
	$fields=json_decode($fields);
	foreach ($fields as $name=>$field) { 
		if (!$field->show) {
			continue;
		} 
		if (@$field->required && (!isset($_REQUEST[$name]) || !$_REQUEST[$name])) {
			$errors[]='You must enter the "'.htmlspecialchars($name).'" field.';
		} 
	}
	// }
	// { if no payment method is selected, then choose the first available
	if (!isset($_REQUEST['_payment_method_type'])
		|| $_REQUEST['_payment_method_type']==''
	) {
		if (@$PAGEDATA->vars['online_stores_paypal_address']) {
			$_REQUEST['_payment_method_type'] = 'PayPal';
		}
		elseif (@$PAGEDATA->vars['online_stores_quickpay_merchantid']) {
			$_REQUEST['_payment_method_type'] = 'QuickPay';
		}
		elseif (@$PAGEDATA->vars['online_stores_realex_sharedsecret']) {
			$_REQUEST['_payment_method_type'] = 'Realex';
		}
		elseif (@$PAGEDATA->vars['online_stores_bank_transfer_account_number']) {
			$_REQUEST['_payment_method_type'] = 'Bank Transfer';
		}
	}
	// }
	// { if a voucher is submitted, check that it's still valid
	if (@$_REQUEST['os_voucher']) {
		require_once dirname(__FILE__).'/voucher-libs.php';
		$email=$_REQUEST['Email'];
		$code=$_REQUEST['os_voucher'];
		$valid=OnlineStore_voucherCheckValidity($code, $email);
		if (isset($valid['error'])) {
			$errors[]=$valid['error'];
		}
	}
	// }
	// { check that payment method is valid
	switch($_REQUEST['_payment_method_type']){
		case 'Bank Transfer': // {
			if (!@$PAGEDATA->vars['online_stores_bank_transfer_account_number']) {
				$errors[]='Bank Transfer payment method not available.';
			}
		break; // }
		case 'PayPal': // {
			if (!@$PAGEDATA->vars['online_stores_paypal_address']) {
				$errors[]='PayPal payment method not available.';
			}
		break; // }
		case 'QuickPay': // {
			if (!@$PAGEDATA->vars['online_stores_quickpay_secret']) {
				$errors[]='QuickPay payment method not available.';
			}
		break; // }
		case 'Realex': // {
			if (!@$PAGEDATA->vars['online_stores_realex_sharedsecret']) {
				$errors[]='Realex payment method not available.';
			}
		break; // }
		default: // {
			$errors[]='Invalid payment method "'
				.htmlspecialchars($_REQUEST['_payment_method_type'])
				.'" selected.';
			// }
	}
	// }
	if ($uid) { // user account stuff
		$_user=dbRow(
			'select email,name,contact,address from user_accounts where id='.$uid
		);
		$user=User::getInstance($uid);
		// { check if new address was entered
		$addresses=(array)json_decode($_user['address'], true);
		$newAddress=array(
			'street'=>$_POST['Street'],
			'street2'=>$_POST['Street2'],
			'town'=>$_POST['Town'],
			'postcode'=>$_POST['Postcode'],
			'county'=>$_POST['County'],
			'country'=>$_POST['Country'],
			'phone'=>$_POST['Phone']
		);
		$found=0;
		foreach ($addresses as $address) {
			if ($address['street']==$newAddress['street']
				&& $address['street2']==$newAddress['street2']
				&& $address['town']==$newAddress['town']
				&& $address['postcode']==$newAddress['postcode']
				&& $address['county']==$newAddress['county']
				&& $address['country']==$newAddress['country']
				&& $address['phone']==$newAddress['phone']
			) {
				$found=1;
				break;
			}
		}
		if (!$found) {
			$addresses[]=$newAddress;
			$addresses=addslashes(json_encode($addresses));
			$_SESSION['userdata']['address']=$addresses;
			dbQuery(
				'update user_accounts set address="'.$addresses.'" where id='.$uid
			);
		}
		// }
		// { check if new name, surname, phone were entered
		if (@$_POST['Email']==$_user['email']) {
			$contact=json_decode($_user['contact'], true);
			if (isset($_POST['FirstName'])) {
				$_user['name']=$_POST['FirstName'].' '.$_POST['Surname'];
				$_SESSION['userdata']['name']=$_user['name'];
			}
			if (isset($_POST['Phone'])) {
				$contact['phone']=$_POST['Phone'];
				$_SESSION['userdata']['phone']=$contact['phone'];
			}
			$contact=json_encode($contact);
			dbQuery(
				'update user_accounts set name="'.addslashes($_user['name']).'"'
				.', contact="'.addslashes($contact).'" where id='.$uid
			);
		}
		// }
		// { add to user group if it's set
		if (isset($PAGEDATA->vars['online_stores_customers_usergroup'])) {
			if (!$user->isInGroup(
				$PAGEDATA->vars['online_stores_customers_usergroup']
			)) {
				$user->addToGroup($PAGEDATA->vars['online_stores_customers_usergroup']);
			}
		}
		// }
	}
	unset($_REQUEST['action'], $_REQUEST['page']);
	if (count($errors)) {
		$c.='<div class="errors"><em class="__" lang-context="core">'
			.join('</em><br /><em class="__" lang-context="core">', $errors)
			.'</em></div>';
	} 
	else {
		$formvals = addslashes(json_encode($_REQUEST));
		$items=addslashes(json_encode($_SESSION['online-store']['items']));
		$total=OnlineStore_getFinalTotal();
		// { save data
		dbQuery(
			'insert into online_store_orders (form_vals,total,items,date_created,user_id)'
			." values('$formvals', $total, '$items', now(), '"
			. @$_SESSION[ 'userdata' ][ 'id' ] . "' )"
		);
		$id=dbOne('select last_insert_id() as id', 'id');
		 // }
		// { generate invoice
		require_once SCRIPTBASE . 'ww.incs/Smarty-2.6.26/libs/Smarty.class.php';
		$smarty = new Smarty;
		$smarty->compile_dir=USERBASE.'/ww.cache/templates_c';
		if (!file_exists(USERBASE.'/ww.cache/templates_c')) {
			mkdir(USERBASE.'/ww.cache/templates_c');
		}
		$smarty->register_function('INVOICETABLE', 'online_store_invoice_table');
		foreach ($_REQUEST as $key=>$val) {
			$smarty->assign($key, $val);
		}
		// { table of items
		$table='<table id="onlinestore-invoice" style="clear:both" width="100%"'
			.'><tr><th class="quantityheader __" lang-context="core">Quantity</th>'
			.'<th class="descriptionheader __" lang-context="core">Description</th>'
			.'<th class="unitamountheader __" lang-context="core">Unit Price</th>'
			.'<th class="amountheader __" lang-context="core">Amount</th>'
			.'</tr>';
		$user_is_vat_free=0;
		$group_discount=0;
		if (@$_SESSION['userdata']['id']) {
			$user=User::getInstance($_SESSION['userdata']['id']);
			$user_is_vat_free=$user->isInGroup('_vatfree');
			$group_discount=$user->getGroupHighest('discount');
		}
		$grandTotal=0;
		$discountableTotal=0;
		$deliveryTotal=0;
		$vattable=0;
		$has_vatfree=false;
		foreach ($_SESSION['online-store']['items'] as $key=>$item) {
			$totalItemCost=$item['cost']*$item['amt'];
			$table.='<tr><td class="quantitycell">'.$item['amt']
				.'</td><td class="descriptioncell"><a href="'.$item['url'].'">'
				.preg_replace('/<[^>]*>/', '', $item['short_desc'])
				.'</td><td class="unitamountcell">'
				.OnlineStore_numToPrice($item['cost'])
				.'</td><td class="amountcell">'
				.OnlineStore_numToPrice($totalItemCost)
				.'</td></tr>';
			if ($item['long_desc']) {
				$table.='<tr><td colspan="3">'.$item['long_desc'].'</td><td></td></tr>';
			}
			$grandTotal+=$totalItemCost;
			if ($item['vat']) {
				$vattable+=$totalItemCost;
			}
			if (!isset($item['delivery_free']) || !$item['delivery_free']) {
				$deliveryTotal+=$totalItemCost;
			}
			if (!isset($item['not_discountable']) || !$item['not_discountable']) {
				$discountableTotal+=$totalItemCost;
			}
		}
		$table.='<tr class="os_basket_totals">'
			.'<td colspan="2" class="nobord">&nbsp;</td>'
			.'<td style="text-align:right" class="__" lang-context="core">'
			.'Subtotal</td><td class="totals amountcell">'
			.OnlineStore_numToPrice($grandTotal)
			.'</td></tr>';
		if (isset($_REQUEST['os_voucher']) && $_REQUEST['os_voucher']) {
			$email=$_REQUEST['Email'];
			$code=$_REQUEST['os_voucher'];
			$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
			if ($voucher_amount) {
				$table.='<tr class="os_basket_totals"><td colspan="2" class="nobord">&nbsp;</td>'
					.'<td class="voucher" style="text-align: right;">'
					.'<span class="__" lang-context="core">Voucher</span> '
					.'('.htmlspecialchars($code).')</td><td class="totals amountcell">-'
					.OnlineStore_numToPrice($voucher_amount).'</td></tr>';
				$grandTotal-=$voucher_amount;
				OnlineStore_voucherRecordUsage($id, $voucher_amount);
			}
		}
		if ($group_discount) { // group discount
			$discount_amount=$grandTotal*($group_discount/100);
			$table.='<tr class="os_basket_totals"><td colspan="2" class="nobord">'
				.'&nbsp;</td><td class="group-discount" style="text-align:right;">'
				.'<span class="__" lang-context="core">'
				.'Group Discount</span> ('.$group_discount.'%)</td><td class="totals">-'
				.OnlineStore_numToPrice($discount_amount).'</td></tr>';
			$grandTotal-=$discount_amount;
		}
		// { postage
		$postage=OnlineStore_getPostageAndPackaging($deliveryTotal, '', 0);
		if ($postage['total']) {
			$grandTotal+=$postage['total'];
			$table.='<tr class="os_basket_totals"><td colspan="2" class="nobord">'
				.'&nbsp;</td><td class="p_a'
				.'nd_p __" lang-context="core" style="text-align: right;">'
				.'Postage and Packaging (P&amp;P)</td><td class="amountcell">'
				.OnlineStore_numToPrice($postage['total']).'</td></tr>';
		}
		// }
		if ($vattable && $_SESSION['onlinestore_vat_percent']) {
			$table.='<tr class="os_basket_totals"><td colspan="2" class="nobord">&nbsp;</td>'
				.'<td style="text-align:right" class="vat">'
				.'<span class="__" lang-context="core">VAT</span> '
				.'('.$_SESSION['onlinestore_vat_percent'].'% on '
				.OnlineStore_numToPrice($vattable).')</td><td class="amountcell">';
			$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
			$table.=OnlineStore_numToPrice($vat).'</td></tr>';
			$grandTotal+=$vat;
		}
		$table.='<tr class="os_basket_totals os_basket_amountcell">'
			.'<td colspan="2" class="nobord">&nbsp;</td>'
			.'<td class="totalcell __" lang-context="core" '
			.'style="text-align: right;">Total Due</td>'
			.'<td class="amountcell">'.OnlineStore_numToPrice($grandTotal)
			.'</td></tr>';
		$table.='</table>';
		$smarty->assign('_invoice_table', $table);
		$smarty->assign('_invoicenumber', $id);
		// }
		if (!file_exists(USERBASE.'/ww.cache/online-store/'.$PAGEDATA->id)) {
			@mkdir(USERBASE.'/ww.cache/online-store');
			file_put_contents(
				USERBASE.'/ww.cache/online-store/'.$PAGEDATA->id,
				$PAGEDATA->vars['online_stores_invoice']
			);
		}
		$invoice=addslashes(
			$smarty->fetch(
				USERBASE.'/ww.cache/online-store/'.$PAGEDATA->id
			)
		);
		dbQuery("update online_store_orders set invoice='$invoice' where id=$id");
		// }
		// { show payment button
		switch($_REQUEST['_payment_method_type']){
			case 'Bank Transfer': // {
				$msg=$PAGEDATA->vars['online_stores_bank_transfer_message'];
				$msg=str_replace(
					'{{$total}}',
					OnlineStore_numToPrice($grandTotal),
					$msg
				);
				$msg=str_replace(
					'{{$invoice_number}}',
					$id,
					$msg
				);
				$msg=str_replace(
					'{{$bank_name}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_bank_name']
					),
					$msg
				);
				$msg=str_replace(
					'{{$account_name}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_account_name']
					),
					$msg
				);
				$msg=str_replace(
					'{{$account_number}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_account_number']
					),
					$msg
				);
				$msg=str_replace(
					'{{$sort_code}}',
					htmlspecialchars(
						$PAGEDATA->vars['online_stores_bank_transfer_sort_code']
					),
					$msg
				);
				$c.=$msg;
			break; // }
			case 'PayPal': // {
				$c.='<p class="__" lang-context="core">Your order has been recorded. '
					.'Please click the button below to go to PayPal for payment. '
					.'Thank you.</p>';
				$c.=OnlineStore_generatePaypalButton($PAGEDATA, $id, $total);
			break; // }
			case 'QuickPay': // {
				$c.='<p class="__" lang-context="core">Your order has been recorded. '
					.'Please click the button below to go to QuickPay for payment. '
					.'Thank you.</p>';
				$c.=OnlineStore_generateQuickPayButton($PAGEDATA, $id, $total);
			break; // }
			case 'Realex': // {
				$c.='<p class="__" lang-context="core">Your order has been recorded. '
					.'Please click the button below to go to Realex Payments for '
					.'payment. Thank you.</p>';
				$c.=OnlineStore_generateRealexButton($PAGEDATA, $id, $total);
			break; // }
		}
		// }
		// { unset the shopping cart data
//		unset($_SESSION['online-store']);
		// }
		$submitted=1;
	} 
}
// }
// { else show the checkout
if (!$submitted) {
	if (@$_SESSION['online-store']['items']
		&& count($_SESSION['online-store']['items'])>0
	) {
		$viewtype=(int)@$_REQUEST['viewtype'];
		$pviewtype=(int)@$PAGEDATA->vars['onlinestore_viewtype'];
		// { show basket contents
		$user_is_vat_free=0;
		// { get user data
		$group_discount=0;
		if (@$_SESSION['userdata']['id']) {
			$user=User::getInstance($_SESSION['userdata']['id']);
			$user_is_vat_free=$user->isInGroup('_vatfree');
			$group_discount=$user->getGroupHighest('discount');
		}
		// }
		// { show headers
		$c.='<table id="onlinestore-checkout" width="100%"><tr>';
		$c.='<th style="width:60%" class="__" lang-context="core">Item</th>';
		$c.='<th class="__" lang-context="core">Price</th>';
		$c.='<th class="__" lang-context="core">Amount</th>';
		$c.='<th class="totals __" lang-context="core">Total</th>';
		$c.='</tr>';
		// }
		// { set up variables
		$grandTotal = 0;
		$deliveryTotal=0;
		$discountableTotal=0;
		$vattable=0;
		$has_vatfree=false;
		// }
		foreach ($_SESSION['online-store']['items'] as $md5=>$item) {
			$c.='<tr product="'.$md5.'" class="os_item_numbers '.$md5.'">';
			// { item name and details
			$c.='<td class="products-itemname">';
			if (isset($item['id']) && $item['id']) {
				$p=Product::getInstance($item['id']);
				if ($p) {
					$img=$p->getDefaultImage();
					if ($img) {
						$c.='<a href="/f/'.$img.'" target="popup" '
							.'class="online-store-thumb-wrapper">'
							.'<img src="/a/f=getImg/w=32/h=32/'.$img.'"/>'
							.'</a>';
					}
				}
			}
			if (isset($item['url'])&&!empty($item['url'])) {
				$c.='<a href="'.$item['url'].'">';
			}
			$c.= htmlspecialchars(__FromJson($item['short_desc']));
			if (isset($item['url'])&&!empty($item['url'])) {
				$c.='</a>';
			}
			if (!$item['vat'] && !$user_is_vat_free) {
				$c.='<sup>1</sup>';
				$has_vatfree=true;
			}
			$c.='</td>';
			// }
			// { cost per item
			$c.='<td>'.OnlineStore_numToPrice($item['cost']).'</td>';
			// }
			// { amount
			$c.='<td class="amt"><span class="'.$md5.'-amt amt-num">'
				.$item['amt']
				.'</span></td>';
			// }
			// { total cost of the item
			$totalItemCost=$item['cost']*$item['amt'];
			$grandTotal+=$totalItemCost;
			if ($item['vat'] && !$user_is_vat_free) {
				$vattable+=$totalItemCost;
			}
			if (!(@$item['delivery_free'])) {
				$deliveryTotal+=$totalItemCost;
			}
			if (!isset($item['not_discountable']) || !$item['not_discountable']) {
				$discountableTotal+=$totalItemCost;
			}
			$c.='<td class="'.$md5.'-item-total totals">'
				.OnlineStore_numToPrice($totalItemCost).'</td>';
			// }
			$c.='</tr>';
			if ($item['long_desc']) {
				$c.='<tr><td colspan="3" class="products-longdescription">'
					.$item['long_desc'].'</td><td></td></tr>';
			}
		}
		$c.='<tr class="os_basket_totals"><td style="text-align: right;" colspa'
			.'n="3" class="__" lang-context="core">Subtotal</td>'
			.'<td class="totals">'.OnlineStore_numToPrice($grandTotal).'</td></tr>';
		if (isset($_REQUEST['os_voucher']) && $_REQUEST['os_voucher']=='') {
			unset($_REQUEST['os_voucher']);
		}
		if (isset($_REQUEST['os_voucher']) && $_REQUEST['os_voucher']) {
			require_once dirname(__FILE__).'/voucher-libs.php';
			$email=$_REQUEST['Email'];
			$code=$_REQUEST['os_voucher'];
			$voucher_amount=OnlineStore_voucherAmount($code, $email, $grandTotal);
			if ($voucher_amount) {
				$c.='<tr class="os_basket_totals">'
					.'<td class="voucher" style="text-align: right;" colspan="3">'
					.'<span class="__" lang-context="core">Voucher</span> ('
					.htmlspecialchars($code)
					.'<span style="display:inline-block;"'
					.' class="ui-icon ui-icon-circle-close online-store-voucher-remove">'
					.'</span>'
					.')</td><td class="totals">-'
					.OnlineStore_numToPrice($voucher_amount)
					.'</td></tr>';
				$grandTotal-=$voucher_amount;
			}
			else {
				$c.='<tr class="os_basket_totals">'
					.'<td class="voucher" style="text-align: right;" colspan="4">'
					.'<span class="__" lang-context="core">Voucher has no effect on'
					.' cart. Removed from cart.</span></td></tr>';
				unset($_REQUEST['os_voucher']);
			}
		}
		if (!isset($_REQUEST['os_voucher']) || !$_REQUEST['os_voucher']) {
			$siteHasVouchers=Core_cacheLoad('online-store', 'site-has-vouchers', -1);
			if ($siteHasVouchers===-1) {
				$siteHasVouchers=dbOne(
					'select count(id) ids from online_store_vouchers', 'ids'
				);
				Core_cacheSave('online-store', 'site-has-vouchers', $siteHasVouchers);
			}
			if ($siteHasVouchers) {
				$c.='<tr class="os_basket_totals online-store-vouchers">'
					.'<td class="voucher" style="text-align: right;" colspan="4">'
					.OnlineStore_showVoucherInput()
					.'</td></tr>';
			}
		}
		if ($group_discount && $discountableTotal) { // group discount
			$discount_amount=$discountableTotal*($group_discount/100);
			$c.='<tr class="os_basket_totals">'
				.'<td class="group-discount" style="text-align:right;" colspan="3">'
				.'<span class="__" lang-context="core">Group Discount'
				.'</span> ('.$group_discount.'%)</td><td class="totals">-'
				.OnlineStore_numToPrice($discount_amount).'</td></tr>';
			$grandTotal-=$discount_amount;
		}
		// { postage
		$postage=OnlineStore_getPostageAndPackaging(
			$deliveryTotal,
			@$_REQUEST['Country'],
			0
		);
		if ($postage['total']) {
			$grandTotal+=$postage['total'];
			$c.='<tr class="os_basket_totals"><td class="p_and_p __" lang-context="core" '
				.'style="text-align: right;" colspan="3">'
				.'Postage and Packaging (P&amp;P)</td><td class="totals">'
				.OnlineStore_numToPrice($postage['total']).'</td></tr>';
		}
		// }
		if ($vattable && $_SESSION['onlinestore_vat_percent']) {
			$c.='<tr class="os_basket_totals">'
				.'<td style="text-align:right" class="vat" colspan="3">'
				.'<span class="__" lang-context="core">VAT</span> ('
				.$_SESSION['onlinestore_vat_percent'].'% on '
				.OnlineStore_numToPrice($vattable).')</td><td class="totals">';
			$vat=$vattable*($_SESSION['onlinestore_vat_percent']/100);
			$c.=OnlineStore_numToPrice($vat).'</td></tr>';
			$grandTotal+=$vat;
		}
		$c.='<tr class="os_basket_totals"><td style="text-align: right;" colspa'
			.'n="3" class="__" lang-context="core">Total Due</td>'
			.'<td class="totals">'.OnlineStore_numToPrice($grandTotal).'</td></tr>'
			.'</table>';
		if ($has_vatfree) {
			$c.='<div><sup>1</sup><span class="__" lang-context="core">'
				.'VAT-free item</span></div>';
		}
		// }
		// { show details form
		$_POST['_viewtype']=$pviewtype;
		if ($pviewtype==1&&$viewtype==1 || !$pviewtype) {
			$c.='<form method="post">'
				.$PAGEDATA->render()
				.'<input type="hidden" name="viewtype" value="1"/>'
				.'<input type="hidden" name="action" value="Proceed to Payment" />'
				.'<button class="__" lang-context="core">Proceed to Payment</button>'
				.'</form>';
		}
		else if ($pviewtype==2 || $pviewtype==3) {
			$c.='<div id="online-store-wrapper" class="online-store"></div>';
		}
		else {
			$c.='<form method="post" action="'.$PAGEDATA->getRelativeUrl().'">'
				.'<input type="hidden" name="viewtype" value="1"/>'
				.'<button class="onlinestore-view-checkout __" lang-context="core">'
				.'Checkout</button></form>';
		}
		// }
		// { add scripts
		// { set up variables
		$post=$_POST;
		unset($post['action']);
		$postage=dbOne(
			'select value from page_vars where page_id='.$PAGEDATA->id.' and '
			.'name="online_stores_postage"',
			'value'
		);
		if (!$postage) {
			$post['_pandp']=0;
		}
		else {
			$post['_pandp']=count(json_decode($postage));
		}
		$post['os_pandp']=isset($_SESSION['os_pandp'])?(int)$_SESSION['os_pandp']:0;
		// }
		WW_addInlineScript('var os_post_vars='.json_encode($post).';');
		WW_addScript('online-store');
		// }
	}
	else {
		$c.='<em class="__" lang-context="core">No items in your basket</em>';
	}
}
// }
