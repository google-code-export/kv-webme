$(function(){
	var test1=[ // { tests that /must/ go at the beginning
		['Teardown previous tests', 'teardown'],
		['Copy Site', 'copy-site'],
		['Installer', 'test-installer']
	]; // }
	var test2=[ // { put these in whatever order you want
		['plugin: Quiz', 'plugin-quiz'],
		['Convert FreeCssTemplates Theme', 'convert-freecsstemplate-theme'],
		['Non-latin page names', 'test-page-non-latin'],
		['Page Creation', 'test-page-creation'],
		['Page Editing', 'test-page-editing'],
		['Page-type: Redirect', 'test-page-redirect'],
		['Plugin Installation and Deinstallation', 'test-plugin-install-deinstall'],
		['User Management', 'test-user-management'],
		['plugin: Ads', 'plugin-ads'],
		['plugin: Banner Image', 'plugin-banner-image'],
		['plugin: Blog', 'plugin-blog'],
		['plugin: Comments', 'plugin-comments'],
		['plugin: Forms', 'test-forms'],
		['plugin: Forum', 'test-forum'],
		['plugin: Image Gallery', 'test-image-gallery'],
		['plugin: Issue Tracker', 'test-issue-tracker'],
		['plugin: Messaging Notifier', 'test-messaging-notifier'],
		['plugin: Mailinglists', 'plugin-mailinglists'],
		['plugin: Online Store', 'plugin-online-store'],
		['plugin: Privacy', 'test-privacy'],
		['plugin: Products', 'test-products'],
		['plugin: Protected Files', 'plugin-protected-files'],
		['plugin: Site Credits', 'test-site-credits'],
		['plugin: SMS', 'plugin-sms'],
		['Admin Login', 'test-admin-login']
	]; // }
	var test3=[ // { tests that /must/ go at the end
		['Check Code Formatting', 'check-code-formatting'],
		['Check Code Coverage', 'check-code-coverage']
	]; // }
	var testAt=0, tests=[];
	function runTest() {
		addRow(tests[testAt][0]);
		$.post('/p/'+tests[testAt][1]+'.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			testAt++;
			if (testAt<tests.length && ret.ok) {
				runTest();
			}
		}, 'json');
	}

	var starttime=0, timer=false;
	function addRow(name) {
		$('#current').removeAttr('id');
		$('<tr id="current">'
			+'<th>'+name+'</th><td class="time"></td>'
			+'<td class="errors"></td><td class="notes"></td>'
			+'</tr>'
		)
			.appendTo('#tests');
		timerStart();
	}
	function startTests(ret) {
		$('<table id="tests">'
			+'<tr><th>Name</th><th>Time</th>'
			+'<th>Errors</th><th>Notes</th></tr>'
			+'</table>')
			.appendTo($('body').empty());
		for (var i=0;i<test1.length;++i) {
			tests.push(test1[i]);
		}
		for (var i=0;i<test2.length;++i) {
			tests.push(test2[i]);
		}
		for (var i=0;i<test3.length;++i) {
			tests.push(test3[i]);
		}
		runTest();
	}
	function timerStart() {
		var d=new Date();
		starttime=d.getTime();
		timer=setTimeout(timerUpdate, 1);
	}
	function timerUpdate() {
		timer=setTimeout(timerUpdate, 500);
		var d=new Date();
		$('#current .time').text((d.getTime()-starttime)/1000);
	}
	function timerStop(ret) {
		clearTimeout(timer);
		var d=new Date();
		var ms=d.getTime()-starttime;
		$('#current .time').text(ms/1000);
		if (ret.notes) {
			$('#current .notes').text(ret.notes);
		}
		else if (ret.ok) {
			$('#current .notes').text('ok');
		}
		if (ret.errors) {
			$('#current')
				.addClass('has-errors')
				.find('.errors')
				.html(ret.errors);
		}
	}
	$('<button>Start the tests</button>')
		.click(function(){
			startTests();
		})
		.appendTo($('body').empty());
});
