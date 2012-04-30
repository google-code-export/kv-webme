$(function(){
	function teardown() {
		addRow('Teardown previous tests');
		$.post('/p/teardown.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			testCopySite();
		}, 'json');
	}
	function testCopySite() {
		addRow('Copy Site');
		$.post('p/copy-site.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			testInstaller();
		}, 'json');
	}
	function testInstaller() {
		addRow('Installer');
		$.post('p/test-installer.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testAdminLogin();
		}, 'json');
	}
	function testAdminLogin() {
		addRow('Admin Login');
		$.post('p/test-admin-login.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testPluginInstallDeinstall();
		}, 'json');
	}
	function testPluginInstallDeinstall() {
		addRow('Plugin Installation and Deinstallation');
		$.post('p/test-plugin-install-deinstall.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testPageCreation();
		}, 'json');
	}
	function testPageCreation() {
		addRow('Page Creation');
		$.post('p/test-page-creation.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testOnlineStore();
		}, 'json');
	}
	function testOnlineStore() {
		addRow('OnlineStore plugin');
		$.post('p/test-online-store.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testPageNonLatin();
		}, 'json');
	}
	function testPageNonLatin() {
		addRow('Non-latin page names');
		$.post('p/test-page-non-latin.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testPageRedirect();
		}, 'json');
	}
	function testPageRedirect() {
		addRow('Page-type: Redirect');
		$.post('p/test-page-redirect.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testPrivacy();
		}, 'json');
	}
	function testPrivacy() {
		addRow('User Authentication plugin');
		$.post('p/test-privacy.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testPageEditing();
		}, 'json');
	}
	function testPageEditing() {
		addRow('Page Editing');
		$.post('p/test-page-editing.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testProducts();
		}, 'json');
	}
	function testProducts() {
		addRow('Products plugin');
		$.post('p/test-products.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testCodeFormatting();
		}, 'json');
	}
	function testCodeFormatting() {
		addRow('Check Code Formatting');
		$.post('p/check-code-formatting.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
			ret.ok && testCodeCoverage();
		}, 'json');
	}
	function testCodeCoverage() {
		addRow('Check Code Coverage');
		$.post('p/check-code-coverage.php?rand='+Math.random(), function(ret) {
			timerStop(ret);
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
		teardown();
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
