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
// { add Forms plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=forms');
$expected='{"ok":1,"added":["forms"],"removed":[]}';
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
	.'s to be displayed throughout the site.","version":5},"forms":'
	.'{"name":"Form","description":"Allows forms to be created so visitors can'
	.' contact you","version":8}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { add a forms page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'forms',
	'type'  =>'forms'
));
$expected='{"id":"2","pid":0,"alias":"forms"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'forms page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { load the page to see that it worked
$file=Curl_get('http://kvwebmerun/forms', array());
if (strpos($file, 'class="ww_form"')===false) {
	die('{"errors":"failed to add Forms page"}');
}
// }
// { load forms edit page
$file=Curl_get(
	'http://kvwebmerun/ww.admin/pages/form.php?id=2',
	array()
);
if (strpos($file, '"_body"')===false) {
	die('{"errors":"failed to load Forms edit page"}');
}
// }
// { insert a form
$file=Curl_get('http://kvwebmerun/ww.admin/pages/form.php', array(
	'MAX_FILE_SIZE'=>9999999,
	'id'=>2,
	'name'=>'forms',
	'type'=>'forms|forms',
	'title'=>'',
	'keywords'=>'',
	'description'=>'',
	'short_url'=>'',
	'importance'=>'0.5',
	'page_vars[google-site-verification]'=>'',
	'date_publish'=>'0000-00-00 00:00:00',
	'date_unpublish'=>'0000-00-00 00:00:00',
	'associated_date'=>'2012-08-17 17:17:32',
	'page_vars[order_of_sub_pages]'=>0,
	'page_vars[order_of_sub_pages_dir]'=>0,
	'template'=>'_default',
	'action'=>'Update Page Details',
	'page_vars[forms_fields]'=>'[{"name":"name","isrequired":"0","type":"input box","help":""},{"name":"email","isrequired":"0","type":"email","extra":0,"help":""}]',
	'page_vars[forms_record_in_db]'=>1,
	'page_vars[forms_successmsg]'=>'<p>

		Thank you! We will be in contact soon</p>',
	'page_vars[forms_send_as_email]'=>0,
	'page_vars[forms_recipient]'=>'info@kvwebmerun',
	'page_vars[forms_replyto]'=>'name',
	'page_vars[forms_captcha_required]'=>0,
	'page_vars[forms_helpType]'=>0,
	'page_vars[forms_create_user]'=>0,
	'page_vars[forms_preventUserFromSubmitting]'=>0,
	'page_vars[_body]'=>'<h1>forms</h1><p></p>'
));
$expected='Does not appear in ';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'form not inserted.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { check the page on the front to see if the form is there
$file=Curl_get('http://kvwebmerun/forms', array());
if (strpos($file, '>email</th>')===false) {
	die('{"errors":"failed to verify form was inserted on front end"}');
}
// }
// { submit a form with invalid email address
$file=Curl_get('http://kvwebmerun/forms', array(
	'name'=>'test Name',
	'email'=>'invalid email address',
	'funcFormInput'=>'submit',
	'requiredFields'=>''
));
$expected='must provide a valid email';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'did not notice the email address was invalid.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { cleanup
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete forms page"}');
}
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["forms"]}';
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
