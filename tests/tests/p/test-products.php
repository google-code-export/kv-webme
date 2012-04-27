<?php
require_once '../config.php';
require_once 'libs.php';

// { login
$file=Curl_get('http://kvwebmerun/a/f=login', array(
	'email'=>'testemail@localhost.test',
	'password'=>'password'
));
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, '<!-- end of WebME admin -->')===false) {
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
// }
// { check current list of installed plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsGetInstalled');
$expected='{"panels":{"name":"Panels","description":"Allows content section'
	.'s to be displayed throughout the site.","version":5},"products":{"name"'
	.':"Products","description":"Product catalogue.","version":"40"}}';
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
		'errors'=>'could note check list of types.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { get list of type templates
$file=Curl_get('http://kvwebmerun/a/p=products/f=typesTemplatesGet');
$expected='["default"]';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could note check list of type templates.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { install default type
$file=Curl_get('http://kvwebmerun/a/p=products/f=adminTypeCopy/id=default');
$expected='{"id":1}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'could note create product type.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { cleanup
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete redirect page"}');
}
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
// }
// { logout
$file=Curl_get('http://kvwebmerun/a/f=logout', array());
$file=Curl_get('http://kvwebmerun/ww.admin/', array());
if (strpos($file, 'Forgotten Password')===false) {
	die('{"errors":"failed to log out"}');
}
// }

echo '{"ok":1}';
