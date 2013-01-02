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
// { add ImageGallery plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=image-gallery');
$expected='{"ok":1,"added":["image-gallery"],"removed":[]}';
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
	.'s to be displayed throughout the site.","version":5},"image-gallery":'
	.'{"name":"Image Gallery","description":"Allows a directory of images to'
	.' be shown as a gallery.","version":3}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add an image gallery page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'image-gallery',
	'type'  =>'image-gallery'
));
$expected='{"id":"2","pid":0,"alias":"image-gallery"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'image gallery page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load the page to see that it worked
$file=Curl_get('http://kvwebmerun/image-gallery', array());
if (strpos($file, 'gallery directory has not yet been set')===false) {
	die('{"errors":"failed to add Image Gallery page"}');
}
// }
// { load image gallery edit page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/pages/form.php?id=2',
	array()
);
if (strpos($file, 'no images yet. please upload some')===false) {
	die('{"errors":"failed to load Image Gallery edit page"}');
}
// }
// { check via API what's in the gallery
$file=Curl_get('http://kvwebmerun/a/p=image-gallery/f=galleryGet/id=2');
$expected='"items":[],"caption-in-slider":0,"image-width":0';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'problem checking gallery via API<br/>expected: '
				.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add a youtube video
$file=Curl_get(
	'http://kvwebmerun/a/p=image-gallery/f=adminAddVideo',
	array(
		'link'=>'http://www.youtube.com/watch?v=QHELOP82b04',
		'id'=>2,
		'image'=>'http://'
	)
);
// }
// { check via API what's in the gallery
$file=Curl_get('http://kvwebmerun/a/p=image-gallery/f=galleryGet/id=2');
$expected='"items":[{"id":"1","media":"video","url":"\\/a\\/f=getIm';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'problem checking gallery via API<br/>expected: '
				.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { cleanup
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete image gallery page"}');
}
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["image-gallery"]}';
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
