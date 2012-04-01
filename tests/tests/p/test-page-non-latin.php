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
// { get current list of root pages
$file=Curl_get('http://kvwebmerun/a/f=getMenu', array());
$expected='[null,[{"subid":"1","id":"1","name":"test2","alias":"test2","type":"0","numchildren":"0","classes":"menuItemTop first c1","link":"\/test2","parent":null},{"subid":"4","id":"4","name":"redirect","alias":"redirect","type":"1","numchildren":"1","classes":"menuItemTop c2 ajaxmenu_hasChildren","link":"\/redirect","parent":null}]]';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'failed to get current list of root pages.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add page with non-latin name
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'áéíóúÁÉÍÓÚæøå',
	'type'  =>0
));
$expected='{"id":"6","pid":0,"alias":"aeiouaeiouaoa"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'non-latin page not entered correctly.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check new list of root pages
$file=Curl_get('http://kvwebmerun/a/f=getMenu', array());
$expected='[null,[{"subid":"1","id":"1","name":"test2","alias":"test2","type":"0","numchildren":"0","classes":"menuItemTop first c1","link":"\/test2","parent":null},{"subid":"4","id":"4","name":"redirect","alias":"redirect","type":"1","numchildren":"1","classes":"menuItemTop c2 ajaxmenu_hasChildren","link":"\/redirect","parent":null},{"subid":"6","id":"6","name":"\u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00e6\u00f8\u00e5","alias":"aeiouaeiouaoa","type":"0","numchildren":"0","classes":"menuItemTop c3","link":"\/aeiouaeiouaoa","parent":null}]]';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'incorrect URL for transcribed page.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add sub-page with non-latin name
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'  =>6,
	'name'    =>'áéíóúÁÉÍÓÚæøåSubpage',
	'type'    =>0,
	'body[en]'=>'<p>test non-latin page</p>'
));
$expected='{"id":"7","pid":6,"alias":"aeiouaeiouaoasubpage"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'non-latin page not entered correctly.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check new list of root pages
$file=Curl_get('http://kvwebmerun/a/f=getMenu', array());
$expected='[null,[{"subid":"1","id":"1","name":"test2","alias":"test2","type":"0","numchildren":"0","classes":"menuItemTop first c1","link":"\/test2","parent":null},{"subid":"4","id":"4","name":"redirect","alias":"redirect","type":"1","numchildren":"1","classes":"menuItemTop c2 ajaxmenu_hasChildren","link":"\/redirect","parent":null},{"subid":"6","id":"6","name":"\u00e1\u00e9\u00ed\u00f3\u00fa\u00c1\u00c9\u00cd\u00d3\u00da\u00e6\u00f8\u00e5","alias":"aeiouaeiouaoa","type":"0","numchildren":"1","classes":"menuItemTop c3 ajaxmenu_hasChildren","link":"\/aeiouaeiouaoa","parent":null}]]';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'list of root pages doesn\'t show new sub-page.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check that sub-page loads
$file=Curl_get('http://kvwebmerun/aeiouaeiouaoa/aeiouaeiouaoasubpage', array());
if (strpos($file, '<h1>áéíóúÁÉÍÓÚæøåSubpage</h1>')===false) {
	die('{"errors":"failed to load new sub-page"}');
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