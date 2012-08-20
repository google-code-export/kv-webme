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
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled');
$expected='{"panels":{"name":"Panels","description":"Allows content section'
	.'s to be displayed throughout the site.","version":5}'
	.',"online-store":{"name":"Online Store","description":"Add online-shopping'
	.' capabilities to some plugins.","version":"15"}'
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
// { delete it, to try using the wizard
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete online store page (1)"}');
}
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=pages');
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
if (strpos($file, '{{ONLINESTORE_COUNTRIES}}')===false) {
	die('{"errors":"failed to load OnlineStore edit page (after Wizard)"}');
}
// }
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete product page"}');
}
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=3');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete online store page (2)"}');
}
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=pages');
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
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
