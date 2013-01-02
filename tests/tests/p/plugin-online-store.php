<?php
require_once '../config.php';
require_once 'libs.php';

// { login
$file=Curl_get('http://kvwebmerun/a/f=login', array(
	'email'=>'testemail@localhost.test',
	'password'=>'password'
));
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, '<!-- end of admin -->')===false) {
	die('{"errors":"failed to load admin page /ww.admin/ after logging in"}');
}
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled', array());
$expected='{"panels":{"name":"Panels","description":"Allows content '
	.'sections to be displayed throughout the site.","version":5}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add OnlineStore plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=online-store');
$expected='{"ok":1,"added":["online-store"],"removed":[]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
$file=Curl_get('http://kvwebmerun/a/f=nothing');
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled');
$expected='{"panels":{"name":"Panels","description":"Allows content section'
	.'s to be displayed throughout the site.","version":5}'
	.',"online-store":{"name":"Online Store","description":"Add online-shopping'
	.' capabilities to some plugins.","version":"16"}'
	.'}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add an online store page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'online-store',
	'type'  =>'online-store'
));
$expected='{"id":"2","pid":0,"alias":"online-store"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'online-store page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load the page to see that it worked
$file=Curl_get('http://kvwebmerun/online-store', array());
if (strpos($file, 'No items in your basket')===false) {
	die('{"errors":"failed to add OnlineStore page"}');
}
// }
// { load online-store edit page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/pages/form.php?id=2',
	array()
);
if (strpos($file, '{{ONLINESTORE_COUNTRIES}}')===false) {
	die('{"errors":"failed to load OnlineStore edit page"}');
}
// }
// { add products plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=products');
$expected='{"ok":1,"added":["products"],"removed":[]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { install default product type
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminTypeCopy/id=default');
$expected='{"id":1}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could not create product type.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { set the product type so it has prices and a button
$file=Curl_get(
	'http://kvwebmerun/a/p=products/f=adminTypeEdit',
	array(
		'data[id]'=>1,
		'data[name]'=>'default (copy)',
		'data[multiview_template]'=>'{{PRODUCTS_BUTTON_ADD_TO_CART}}',
		'data[singleview_template]'=>'',
		'data[data_fields][0][n]'=>'description',
		'data[data_fields][0][ti]'=>'Description',
		'data[data_fields][0][t]'=>'textarea',
		'data[data_fields][0][s]'=>0,
		'data[data_fields][0][r]'=>0,
		'data[data_fields][0][u]'=>0,
		'data[data_fields][0][e]'=>'',
		'data[is_for_sale]'=>1,
		'data[prices_based_on_usergroup]'=>0,
		'data[associated_colour]'=>'ffffff',
		'data[multiview_template_header]'=>'',
		'data[multiview_template_footer]'=>'',
		'data[meta]'=>'',
		'data[is_voucher]'=>0,
		'data[voucher_template]'=>0,
		'data[stock_control]'=>0,
		'data[default_category]'=>0,
		'data[default_category_name]'=>false
	)
);
$expected='{"ok":1}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add a product to test
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=products&_page=products-edit',
	array(
		'id'=>0,
		'action'=>'save',
		'name'=>'{"en":"product1"}',
		'product_type_id'=>1,
		'activates_on'=>'2012-01-01 00:00:00',
		'expires_on'=>'2100-01-01 00:00:00',
		'stock_number'=>'',
		'enabled'=>1,
		'user_id'=>1,
		'ean'=>'',
		'location'=>0,
		'images_directory'=>'',
		'products_default_category'=>0
	)
);
$expected='expires_on" value="2100-01-01 00:00:00"';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { edit the product to add a price
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=products&_page=products-edit',
	array(
		'id'=>1,
		'action'=>'save',
		'name'=>'{"en":"product1"}',
		'product_type_id'=>1,
		'activates_on'=>'2012-01-01 00:00:00',
		'expires_on'=>'2100-01-01 00:00:00',
		'stock_number'=>'',
		'enabled'=>1,
		'user_id'=>1,
		'ean'=>'',
		'location'=>0,
		'images_directory'=>'',
		'data_fields[description][en]'=>'',
		'online-store-fields[_price]'=>123,
		'online-store-fields[_trade_price]'=>'',
		'online-store-fields[_sale_price]'=>'',
		'online-store-fields[_sale_price_type]'=>0,
		'online-store-fields[_bulk_price]'=>'',
		'online-store-fields[_bulk_amount]'=>'',
		'online-store-fields[_weight(kg)]'=>'',
		'online-store-fields[_vatfree]'=>0,
		'online-store-fields[_custom_vat_amount]'=>'',
		'online-store-fields[_deliver_free]'=>0,
		'online-store-fields[_not_discountable]'=>0,
		'online-store-fields[_sold_amt]'=>'',
		'online-store-fields[_stock_amt]'=>'',
		'online-store-fields[_max_allowed]'=>'',
		'products_default_category'=>0
	)
);
$expected='name="online-store-fields[_price]" value="123';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add a product page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'products',
	'type'  =>'products'
));
$expected='{"id":"3","pid":0,"alias":"products"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'products page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add 3 of the product to cart
$file=Curl_get('http://kvwebmerun/a/p=online-store/f=addProductToCart', array(
	'products_action'=>'add_to_cart',
	'product_id'=>1,
	'products-howmany'=>3
));
$expected='{"ok":1}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'product not added to cart.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { set online-store page to use 5-step method and paypal
$file=Curl_get(
	'http://kvwebmerun/ww.admin/pages/form.php',
	array( // { vals
		'id'=>2,
		'MAX_FILE_SIZE'=>9999999,
		'name'=>'online-store',
		'type'=>'online-store',
		'page_vars[online_stores_postage]'=>'[]',
		'page_vars[online_stores_admin_email]'=>'',
		'online_store_currency'=>'EUR',
		'page_vars[online_stores_vat_percent]'=>0,
		'page_vars[online_stores_customers_usergroup]'=>'customers',
		'page_vars[online_stores_paypal_address]'=>'kvwebmeAdmin@kvsites.ie',
		'page_vars[online_stores_bank_transfer_bank_name]'=>'',
		'page_vars[online_stores_bank_transfer_sort_code]'=>'',
		'page_vars[online_stores_bank_transfer_account_name]'=>'',
		'page_vars[online_stores_bank_transfer_account_number]'=>'',
		'page_vars[online_stores_bank_transfer_message]'=>'<p>Thank you for your'
			.' purchase. Please send {{$total}} to the following bank account,'
			.' quoting the invoice number {{$invoice_number}}:</p> <table> <tr>'
			.'<th>Bank</th><td>{{$bank_name}}</td></tr> <tr><th>Account Name</th>'
			.'<td>{{$account_name}}</td></tr> <tr><th>Sort Code</th><td>'
			.'{{$sort_code}}</td></tr> <tr><th>Account Number</th><td>'
			.'{{$account_number}}</td></tr> </table>',
		'page_vars[online_stores_realex_merchantid]'=>'',
		'page_vars[online_stores_realex_sharedsecret]'=>'',
		'page_vars[online_store_redirect_to]'=>0,
		'page_vars[online_stores_realex_testmode]'=>'test',
		'page_vars[online_stores_quickpay_merchantid]'=>'',
		'page_vars[online_stores_quickpay_secret]'=>'',
		'page_vars[online_store_quickpay_redirect_to]'=>0,
		'page_vars[online_store_quickpay_redirect_failed]'=>0,
		'page_vars[online_stores_quickpay_autocapture]'=>0,
		'page_vars[online_stores_quickpay_testmode]'=>'test',
		'page_vars[onlinestore_viewtype]'=>2,
		'body'=>'<table class="checkout_table"> <tbody> <tr> <td width="40%">'
			.'<h3 class="__" lang-context="core"> Delivery Details</h3>'
			.'<table class="shoppingcartCheckout"> <tbody> <tr>'
			.'<td class="cellHeader" colspan="1" style="width: 20%;"> Name</td>'
			.'<td colspan="1"> <input class="text" id="FirstName" name="FirstName"'
			.' required="required" /></td> </tr> <tr> <td class="cellHeader"'
			.' colspan="1"> Surname</td> <td colspan="1">'
			.'<input class="text" id="Surname" name="Surname" required="required" />'
			.'</td> </tr> <tr> <td class="cellHeader" colspan="1"> Phone</td>'
			.'<td colspan="1"> <input class="text" id="Phone" name="Phone"'
			.' required="required" /></td> </tr> <tr> <td class="cellHeader"'
			.' colspan="1"> Email</td> <td colspan="1">'
			.'<input class="email text" id="Email" name="Email" required="required"'
			.' type="email" /></td> </tr> <tr> <td class="cellHeader" colspan="1">'
			.'Street</td> <td colspan="1">'
			.'<input class="text" id="Street" name="Street" /></td> </tr> <tr>'
			.'<td class="cellHeader" colspan="1"> Street 2</td> <td colspan="1">'
			.'<input class="text" id="Street2" name="Street2" /></td> </tr> <tr>'
			.'<td class="cellHeader"> Town</td> <td colspan="1">'
			.'<input class="text" id="Town" name="Town" /></td> </tr> <tr>'
			.'<td class="cellHeader"> County</td> <td colspan="1">'
			.'<input class="text" id="County" name="County" /></td> </tr> <tr>'
			.'<td class="cellHeader"> Country</td> <td colspan="1">'
			.'<div class="countries-list"> {{ONLINESTORE_COUNTRIES}}</div> </td>'
			.'</tr> </tbody> </table> </td> <td id="sc_paymentCell" width="60%">'
			.'<h3> Payment Details</h3> {{ONLINESTORE_PAYMENT_TYPES}}'
			.'<div id="payment_method_form"> &nbsp;</div> {{ONLINESTORE_VOUCHER}}'
			.'<br /> Click here if Billing address is different to Delivery: '
			.'<input name="BillingAddressIsDifferentToDelivery" '
			.'onclick="document.getElementById(\'billing_address\').style.display='
			.'(this.checked)?\'block\':\'none\'" type="checkbox" /> '
			.'<div id="billing_address" style="display: none;">'
			.'<table class="shoppingcartCheckout_billing"> <tbody> <tr>'
			.'<td class="cellHeader" colspan="1" style="width: 20%;"> Name</td>'
			.'<td colspan="1"> <input class="text" id="Billing_FirstName"'
			.' name="Billing_FirstName" /></td> </tr> <tr> <td class="cellHeader"'
			.' colspan="1"> Surname</td> <td colspan="1"> <input class="text"'
			.' id="Billing_Surname" name="Billing_Surname" /></td> </tr> <tr>'
			.'<td class="cellHeader" colspan="1"> Phone</td> <td colspan="1">'
			.'<input class="text" id="Billing_Phone" name="Billing_Phone" /></td>'
			.'</tr> <tr> <td class="cellHeader" colspan="1"> Email</td>'
			.'<td colspan="1"> <input class="email text" id="Billing_Email"'
			.' name="Billing_Email" type="email" /></td> </tr> <tr>'
			.'<td class="cellHeader" colspan="1"> Street</td> <td colspan="1">'
			.'<input class="text" id="Billing_Street" name="Billing_Street" /></td>'
			.'</tr> <tr> <td class="cellHeader" colspan="1"> Street 2</td>'
			.'<td colspan="1"> <input class="text" id="Billing_Street2"'
			.' name="Billing_Street2" /></td> </tr> <tr> <td class="cellHeader">'
			.'Town</td> <td colspan="1"> <input class="text" id="Billing_Town"'
			.' name="Billing_Town" /></td> </tr> <tr> <td class="cellHeader">'
			.'County</td> <td colspan="1"> <input class="text" id="Billing_County"'
			.' name="Billing_County" /></td> </tr> <tr> <td class="cellHeader">'
			.'Country</td> <td colspan="1"> <div class="countries-list">'
			.'{{ONLINESTORE_COUNTRIES prefix=&quot;Billing_&quot;}}</div> </td>'
			.'</tr> </tbody> </table> </div> </td> </tr> </tbody> </table>',
		'page_vars[online_stores_fields]'=>'{"FirstName":{"required":"required",'
			.'"show":1},"Surname":{"required":"required","show":1},'
			.'"Phone":{"required":"required","show":1},'
			.'"Email":{"required":"required","show":1},"Street":{"show":1},'
			.'"Street2":{"show":1},"Town":{"show":1},"County":{"show":1},'
			.'"BillingAddressIsDifferentToDelivery":{"show":1},'
			.'"Billing_FirstName":{"show":1},"Billing_Surname":{"show":1},'
			.'"Billing_Phone":{"show":1},"Billing_Email":{"show":1},'
			.'"Billing_Street":{"show":1},"Billing_Street2":{"show":1},'
			.'"Billing_Town":{"show":1},"Billing_County":{"show":1}}',
		'page_vars[online-store-countries][Ireland]'=>'on',
		'page_vars[online_stores_exportdir]'=>'',
		'page_vars[online_stores_exportcustomers]'=>'',
		'page_vars[online_stores_exportcustomer_filename]'=>'',
		'title'=>'',
		'keywords'=>'',
		'description'=>'',
		'short_url'=>'',
		'importance'=>'0.5',
		'page_vars[google-site-verification]'=>'',
		'date_publish'=>'0000-00-00 00:00:00',
		'date_unpublish'=>'0000-00-00 00:00:00',
		'associated_date'=>'2012-08-26 21:41:38',
		'page_vars[order_of_sub_pages]'=>0,
		'page_vars[order_of_sub_pages_dir]'=>0,
		'template'=>'_default',
		'action'=>'Update Page Details'
	) // }
);
$expected='option value="2" selected="selected"';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { check the list of countries (should be just Ireland)
$file=Curl_get(
	'http://kvwebmerun/a/p=online-store/f=getCountries/page_id=2'
);
$expected='["Ireland"]';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { submit the order
$file=Curl_get(
	'http://kvwebmerun/online-store',
	array(
		'Billing_Country'=>'Ireland',
		'Billing_County'=>'county1',
		'Billing_Email'=>'kvwebmeUser@kvsites.ie',
		'Billing_FirstName'=>'kae',
		'Billing_Phone'=>'2341234',
		'Billing_Postcode'=>'postcode1',
		'Billing_Street'=>'street1',
		'Billing_Street2'=>'street2',
		'Billing_Surname'=>'verens',
		'Billing_Town'=>'town1',
		'Country'=>'Ireland',
		'County'=>'Dcounty1',
		'Email'=>'DkvwebmeUser@kvsites.ie',
		'FirstName'=>'Dkae',
		'Phone'=>'D2341234',
		'Postcode'=>'Dpostcode1',
		'Street'=>'Dstreet1',
		'Street2'=>'Dstreet2',
		'Surname'=>'Dverens',
		'Town'=>'Dtown1',
		'_payment_method_type'=>'PayPal',
		'os_voucher'=>'',
		'os_pandp'=>'0',
		'action'=>'Proceed to Payment'
	)
);
$expected='go to PayPal for payment';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { test that setting an invoice as Paid sends the invoice as an email
// { empty the user's email account
Email_empty('user');
// }
// { mark this order as Paid
$file=Curl_get(
	'http://kvwebmerun/a/p=online-store/f=adminChangeOrderStatus/id=1/status=1'
);
$expected='{"ok":1}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { check that the invoice arrived
$email=Email_getOne('user');
$expected='Dstreet1';
if (strpos($email['body'], $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'failed to send invoice<br/>expected: '.$expected.'<br/>actual: '
				.json_encode($email)
		))
	);
}
// }
// { empty the user's email account again
Email_empty('user');
// }
// }
// { test again, but this time setting invoices not to be emailed
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=online-store&_page=site-options',
	array( // { vals
		'online_store_vars[vat_display]'=>0,
		'online_store_vars[invoices_by_email]'=>1,
		'os-currencies_name[]'=>'Euro',
		'os-currencies_iso[]'=>'Eur',
		'os-currencies_symbol[]'=>'€',
		'os-currencies_value[]'=>1,
		'discounts[1]'=>0,
		'discounts[20]'=>0,
		'action'=>'Save'
	) // }
);
$expected='Saved';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { check that orders are displayed in the admin area
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=online-store&_page=orders'
);
$expected='369.00';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { test vouchers
// { check that the admin page loads
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=online-store&_page=vouchers'
);
$expected='Create a voucher';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { try a voucher without any parameters to see if it loads in the frontend
$file=Curl_get('http://kvwebmerun/a/p=online-store/f=checkVoucher');
$expected='{"error":"Invalid or missing parameters"}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// }
// { clean up, to try using the wizard
// { remove page
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete online store page (1)"}');
}
// }
// }
// { test the wizard
// { wizard opening page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=online-store&_page=wizard'
);
$expected='div id="online-store-wizard"';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { page 1
$file=Curl_get(
	'http://kvwebmerun/ww.plugins/online-store/admin/wizard/step1.php'
);
$expected='name for the page';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.htmlspecialchars($file)
		))
	);
}
// }
// { page 2
$file=Curl_get(
	'http://kvwebmerun/ww.plugins/online-store/admin/wizard/step2.php',
	array(
		'wizard-name'=>'online-store'
	)
);
$expected='Do customers need to';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.htmlspecialchars($file)
		))
	);
}
// }
// { page 3
$file=Curl_get(
	'http://kvwebmerun/ww.plugins/online-store/admin/wizard/step3.php',
	array(
		'wizard-email'=>'testemail@localhost.test',
		'wizard-login'=>'no',
		'wizard-payment-paypal'=>0,
		'wizard-payment-Bank Transfer'=>0,
		'wizard-payment-Realex'=>0,
		'wizard-paypal-email'=>'',
		'wizard-transfer-bank-name'=>'',
		'wizard-transfer-sort-code'=>'',
		'wizard-transfer-account-name'=>'',
		'wizard-transfer-account-number'=>'',
		'wizard-message-to-buyer'=>'<p>Thank you for your purchase. Please send {{$total}} to the following bank account, quoting the invoice number {{$invoice_number}}:</p><table><tr><th>Bank</th><td>{{$bank_name}}</td></tr><tr><th>Account Name</th><td>{{$account_name}}</td></tr><tr><th>Sort Code</th><td>{{$sort_code}}</td></tr><tr><th>Account Number</th><td>{{$account_number}}</td></tr></table>',
		'wizard-realex-merchant-id'=>'',
		'wizard-realex-shared-secret'=>'',
		'wizard-realex-redirect-after-payment'=>0,
		'wizard-realex-mode'=>'test'
	)
);
$expected='These details are used to populate';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.htmlspecialchars($file)
		))
	);
}
// }
// { page 4
$file=Curl_get(
	'http://kvwebmerun/ww.plugins/online-store/admin/wizard/step4.php',
	array(
		'wizard-company-name'=>'',
		'wizard-company-telephone'=>'',
		'wizard-company-address'=>'',
		'wizard-company-fax'=>'',
		'wizard-company-email'=>'',
		'wizard-company-vat-number'=>'',
		'wizard-company-invoice'=>2
	)
);
$expected='What type of products';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.htmlspecialchars($file)
		))
	);
}
// }
// { page 5
$file=Curl_get(
	'http://kvwebmerun/ww.plugins/online-store/admin/wizard/step5.php',
	array(
		'wizard-products-type'=>'default',
	)
);
$expected='Your store has been created';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.htmlspecialchars($file)
		))
	);
}
// }
// { load online-store edit page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/pages/form.php?id=3',
	array()
);
if (strpos($file, 'value="0" selected="selected">All products')===false) {
	die('{"errors":"failed to load OnlineStore edit page (after Wizard)"}');
}
// }
// }
// { remove pages
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=3');
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=4');
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=5');
$file=Curl_get('http://kvwebmerun/a/f=adminPageChildnodes?id=0');
if ($file!='[{"data":"Home","attr":{"id":"page_1"},"children":false}]') {
	die('{"errors":"failed to list pages after deleting test pages"}');
}
// }
// { remove product
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminProductDelete/id=1');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete product"}');
}
// }
// { remove product type
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminTypeDelete/id=2');
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminTypeDelete/id=1');
$expected='true';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could not delete product type.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["online-store","products"]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=pages');
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=products');
Curl_get(
	'http://kvwebmerun/a/f=adminDBClearAutoincrement/table=products_types'
);
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
