function onLoad(){
	document.addEventListener("deviceready", onDeviceReady, true);
}
function onDeviceReady(){
	window.webme={
		plugins:false
	};
	$('button').click(function() {
		var site=$('input[type=text]').val().toLowerCase(),
			email=$('input[type=email]').val().toLowerCase(),
			password=$('input[type=password]').val();
		if (!/([a-z0-9\-]+\.)+[a-z]+/.test(site)) {
			return alert('"'+site+'" not a valid domain name\nshould be like www.whatever.com');
		}
		var credentials={
			'site':site,
			'email':email,
			'password':password
		};
		if ($('input[type=checkbox]:checked').length) {
			window.localStorage.setItem(
				'credentials',
				JSON.stringify(credentials)
			);
		}
		webme.furl='http://'+site+'/a/f=';
		$.post(webme.furl+'login', {
			'email':email,
			'password':password
		}, function(ret) {
			if (ret.error) {
				return alert(error);
			}
			if (ret.ok) { // great! logged in.
				showPage('mainmenu');
			}
		}, 'json');
	});
	try{
		var credentials=JSON.parse(
			window.localStorage.getItem('credentials')
		);
		$('input[type=text]').val(credentials.site);
		$('input[type=email]').val(credentials.email);
		$('input[type=password]').val(credentials.password);
		$('button').click();
	}
	catch(e) {
	}
}
function showPage(name) {
	if (window['page_'+name]) {
		return window['page_'+name]();
	}
	$.getScript('j/page_'+name+'.js', function() {
		window['page_'+name]();
	});
}