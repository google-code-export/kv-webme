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
// { add Quiz plugin using InstallOne method
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsInstallOne/name=quiz');
$expected='{"ok":1,"added":["quiz"],"removed":[]}';
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
	.'s to be displayed throughout the site.","version":5},"quiz":'
	.'{"name":"Quizzes","description":"Create a quiz with this plugin",'
	.'"version":4}}';
if ($expected!=$file) {
	die(
		json_encode(array(
			'errors'=>'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { test admin area
// { load quiz admin page
$file=Curl_get('http://kvwebmerun/ww.admin/plugin.php?_plugin=quiz&_page=index');
$expected='New Quiz';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'quiz admin page not loaded.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { new quiz
$file=Curl_get('http://kvwebmerun/ww.admin/plugin.php?_plugin=quiz&_page=index&action=newQuiz');
$expected='Number of Questions';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not create new quiz.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { create the new quiz
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=quiz&_page=index&action=newQuiz',
	array(
		'name'=>'test quiz',
		'description'=>'a test of a quiz',
		'number of questions'=>2,
		'enabled'=>1,
		'errors[]'=>'',
		'id'=>0,
		'action'=>'Add Quiz'
	)
);
$expected='Number of Questions';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not create the quiz settings.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { insert first question
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=quiz&_page=index&action=newQuestion&id=1',
	array(
		'name'=>'test quiz',
		'description'=>'a test of a quiz',
		'number of questions'=>2,
		'enabled'=>1,
		'errors[]'=>'',
		'id'=>1,
		'quiz_id'=>1,
		'question'=>'what\'s your favourite colour',
		'topic'=>'blah',
		'answers[]'=>'Blue',
		'answers[]'=>'Red',
		'answers[]'=>'Aaaargh',
		'isCorrect'=>3,
		'answers[]'=>'',
		'questionErrors'=>'',
		'questionAction'=>'Add Question'
	)
);
$expected='New Question';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not insert the first question.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { insert second question
$file=Curl_get(
	'http://kvwebmerun/ww.admin/plugin.php?_plugin=quiz&_page=index&action=newQuestion&id=1',
	array(
		'name'=>'test quiz',
		'description'=>'a test of a quiz',
		'number of questions'=>2,
		'enabled'=>1,
		'errors[]'=>'',
		'id'=>1,
		'quiz_id'=>1,
		'question'=>'what is the answer to this question',
		'topic'=>'blah2',
		'answers[]'=>'a',
		'answers[]'=>'b',
		'answers[]'=>'c',
		'isCorrect'=>2,
		'answers[]'=>'',
		'questionErrors'=>'',
		'questionAction'=>'Add Question'
	)
);
$expected='You need to provide';
if (strpos($file, $expected)===false) {
	die(json_encode(array(
		'errors'=>'could not insert the second question.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { add a page
$file=Curl_get('http://kvwebmerun/a/f=adminPageEdit', array(
	'parent'=>0,
	'name'  =>'quiz',
	'type'  =>0
));
$expected='{"id":"2","pid":0,"alias":"quiz"}';
if ($file!=$expected) {
	die(json_encode(array(
		'errors'=>'page not created.<br/>expected:<br/>'
			.htmlspecialchars($expected).'<br/>actual:<br/>'.$file
	)));
}
// }
// { save the page to set initial quiz somethingorothers
$file=Curl_get('http://kvwebmerun/ww.admin/pages/form.php', array(
	'id'=>2,
	'parent'=>0,
	'name'  =>'quiz',
	'type'  =>'quiz|quiz',
	'body[en]'=>'<h1>quiz</h1>testing quiz',
	'action'=>'Update Page Details'
));
// }
// }
// { test frontend
$file=Curl_get('http://kvwebmerun/quiz');
$expected='quizzesFrontend';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { cleanup
// { remove plugins
$file=Curl_get('http://kvwebmerun/a/f=adminPluginsSetInstalled',
	array('plugins[panels]'=>'on')
);
$expected='{"ok":1,"added":[],"removed":["quiz"]}';
if (strpos($file, $expected)===false) {
	die(
		json_encode(array(
			'errors'=>
				'expected: '.$expected.'<br/>actual: '.$file
		))
	);
}
// }
// { remove page
$file=Curl_get('http://kvwebmerun/a/f=adminPageDelete/id=2');
if ($file!='{"ok":1}') {
	die('{"errors":"failed to delete page"}');
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
