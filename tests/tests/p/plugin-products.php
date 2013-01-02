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
// { add Products plugin using InstallOne method
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
$file=Curl_get('http://kvwebmerun/a/f=nothing');
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled');
$expected='{"panels":{"name":"Panels","description":"Allows content section'
	.'s to be displayed throughout the site.","version":5},"products":{"name"'
	.':"Products","description":"Product catalogue.","version":"'
	.PLUGIN_PRODUCTS.'"}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
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
$expected='{"id":"2","pid":0,"alias":"products"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'products page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load the page to see that it worked
$file=Curl_get('http://kvwebmerun/products', array());
if (strpos($file, '<div class="products-pagination">')===false) {
	die('{"errors":"failed to add Products page"}');
}
// }
// { load up the products page admin
$file=Curl_get('http://kvwebmerun/ww.admin/pages/form.php?id=2');
$expected='products_what_to_show_1';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not load product admin page.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load products edit page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=products&_page=products-edit',
	array()
);
if (strpos($file, 'EAN-13 barcode')===false) {
	die('{"errors":"failed to load Products edit page"}');
}
// }
// { check list of existing types
$file=Curl_get('http://kvwebmerun/a/p=products/f=typesGet');
$expected='{"sEcho":0,"iTotalRecords":0,"iTotalDisplayRecords":0,"aaData":[]}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could not check list of types.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { get list of type templates
$file=Curl_get('http://kvwebmerun/a/p=products/f=typesTemplatesGet');
$expected='["default"]';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could not check list of type templates.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { install default type
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminTypeCopy/id=default');
$expected='{"id":1}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could not create product type.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
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
// { set the product type so it has bells and whistles
$file=Curl_get(
	'http://kvwebmerun/a/p=products/f=adminTypeEdit',
	array(
		'data[id]'=>1,
		'data[name]'=>'default (copy)',
		'data[multiview_template]'=>'{{PRODUCTS_CATEGORIES}} {{PRODUCTS_DATATABLE}}'
			.' {{PRODUCTS_EXPIRY_CLOCK}} {{PRODUCTS_IMAGE}}'
			.' {{PRODUCTS_IMAGES}} {{PRODUCTS_IMAGES_SLIDER}} {{PRODUCTS_LINK}}'
			.' {{PRODUCTS_LIST_CATEGORIES}} {{PRODUCTS_LIST_CATEGORY_CONTENTS}}'
			.' {{PRODUCTS_MAP}} {{PRODUCTS_OWNER}} {{PRODUCTS_QRCODE}}'
			.' {{PRODUCTS_RELATED}} {{PRODUCTS_REVIEWS}}',
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
// { load the page to see that it worked
$file=Curl_get('http://kvwebmerun/products', array());
if (strpos($file, 'Nobody has reviewed')===false) {
	die('{"errors":"failed to add Products page"}');
}
// }
// { widgets
// { add tree widget to sidebar
$file=Curl_get(
	'http://kvwebmerun/a/p=panels/f=adminSave/id=1',
	array(
		'data'=>'{"widgets":[{"type":"products","name":"Products","widget_type":"Tr'
		.'ee View","parent_cat":"0","show_products":"0","diameter":"280"}]}'
	)
);
$expected='null';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { load the page to see that it worked
$file=Curl_get('http://kvwebmerun/', array());
if (strpos($file, '/products/default">default</a>')===false) {
	die('{"errors":"failed to add Products widget"}');
}
// }
// { remove widget from sidebar
$file=Curl_get(
	'http://kvwebmerun/a/p=panels/f=adminSave/id=1',
	array(
		'data'=>'{"widgets":[]}'
	)
);
// }
// }
// { cleanup
// { remove page
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete product page"}');
}
// }
// { remove product
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminProductDelete/id=1');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete product"}');
}
// }
// { remove product type
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
$expected='{"ok":1,"added":[],"removed":["products"]}';
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
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=panels');
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=products');
Curl_get('http://kvwebmerun/a/f=adminDBClearAutoincrement/table=products_types');
// }
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
