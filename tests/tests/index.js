$(function(){
	function teardown() {
		addRow('Teardown previous tests', 3.5);
		$.post('/p/teardown.php', function(ret) {
			timerStop(ret);
			testCopySite();
		}, 'json');
	}
	function testCopySite() {
		addRow('Copy Site', 2);
		$.post('p/copy-site.php', function(ret) {
			timerStop(ret);
			testInstaller();
		}, 'json');
	}
	function testInstaller() {
		addRow('Installer', 4);
		$.post('p/test-installer.php', function(ret) {
			timerStop(ret);
			ret.ok && testAdminLogin();
		}, 'json');
	}
	function testAdminLogin() {
		addRow('Admin Login', 1);
		$.post('p/test-admin-login.php', function(ret) {
			timerStop(ret);
			ret.ok && testCodeFormatting();
		}, 'json');
	}
	function testCodeFormatting() {
		addRow('Check Code Formatting', 300);
		$.post('p/check-code-formatting.php', function(ret) {
			timerStop(ret);
		}, 'json');
	}

	var starttime=0, timer=false;
	function addRow(name, est) {
		$('#current').removeAttr('id');
		$('<tr id="current">'
			+'<th>'+name+'</th><td class="est">'+est+'</td><td class="act"></td>'
			+'<td class="errors"></td><td class="notes"></td>'
			+'</tr>'
		)
			.appendTo('#tests');
		timerStart();
	}
	function startTests(ret) {
		$('<table id="tests">'
			+'<tr><th>Name</th><th>Time Est.</th><th>Time Act.</th>'
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
		$('#current .act').text((d.getTime()-starttime)/1000);
	}
	function timerStop(ret) {
		clearTimeout(timer);
		var d=new Date();
		var ms=d.getTime()-starttime;
		$('#current .act').text(ms/1000);
		var est=$('#current .est').text()*1000;
		if (ms>est) {
			$('#current .est').addClass('late');
		}
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
				.text(ret.errors);
		}
	}
	$('<button>Start the tests</button>')
		.click(function(){
			startTests();
		})
		.appendTo($('body').empty());
});
