window.page_mainmenu=function() {
	if (!webme.plugins) {
		$.post(webme.furl+'adminPluginsGetInstalled', function(ret) {
			if (ret.error) {
				return alert(ret.error);
			}
			var plugins={};
			$.each(ret, function(k, v) {
				plugins[k]=1;
			});
			console.log(1);
			webme.plugins=plugins;
			showPage('mainmenu');
		}, 'json');
		return;
	}
	console.log(2);
	var menu=[
		['Stats', 'coreStats']
	];
	if (webme.plugins['online-store']) {
		menu.push(['Online Store', 'onlineStore']);
	}
	menu.push(['Logout', 'coreLogout']);
	var $ul=$('<ul class="menu">');
	for (var i=0; i<menu.length; ++i) {
		$ul.append('<li><a href="javascript:showPage(\''
			+menu[i][1]+'\')">'+menu[i][0]+'</a>'
			+'</li>');
	}
	$('#content').empty().append($ul);
	console.log($ul.html());
	$('#header').html('<a href="javascript:showPage(\''
		+'mainmenu\')">Menu</a>');
	$('#footer').empty();
}