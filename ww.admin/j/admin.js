function convert_date_to_human_readable(){
	var $this=$(this);
	var	id='date-input-'+Math.random().toString().replace(/\./,'');
	var dparts=$this.val().split(/-/);
	$this
		.datepicker({
			dateFormat:  'yy-mm-dd',
			modal:       true,
			altField:    '#'+id,
			altFormat:   'DD, d MM, yy',
			changeMonth: true,
			changeYear:  true,
			onSelect:    function(dateText,inst){
				this.value=dateText;
			}
		});
	var $wrapper=$this.wrap('<div style="position:relative" />');
	var $input=$('<input id="'+id+'" class="date-human-readable" value="'+date_m2h($this.val())+'" />');
	$input.insertAfter($this);
	$this.css({
		'position':'absolute',
		'opacity':0
	});
	$this
		.datepicker(
			'setDate', new Date(dparts[0],dparts[1]-1,dparts[2])
		);
}
function Core_sidemenu(links, plugin, currentpage) {
	var html='<ul>';
	for (var i=0;i<links.length;++i) {
		html+='<li><a href="javascript:Core_screen(\''
			+plugin+'\', \''+(links[i].replace(/[^a-zA-Z]/g, ''))+'\')"';
		if (links[i]==currentpage) {
			html+=' class="current-page"';
		}
		html+='>'+links[i]+'</a></li>';
	}
	$('#sidebar1').html(html+'</ul>');
}
function Core_screen(plugin, page) {
	var fname=plugin.charAt(0).toUpperCase()+plugin.slice(1)+'_screen';
	if (window[fname]) {
		if (!$('#sidebar1').length) {
			$('#wrapper').html('<div id="sidebar1"/><div id="content"/>');
		}
		return window[fname](page.replace(/^js:/, ''));
	}
	$('head')
		.append('<link rel="stylesheet" href="/ww.plugins/'+plugin+'/admin.css"/>');
	$.getScript('/ww.plugins/'+plugin+'/admin.js?'+(new Date()).getTime(), function(){
		if (!window[fname]) {
			return;
		}
		Core_screen(plugin, page);
	});
}
$(function(){
	function keepAlive(){
		setTimeout(keepAlive,1700000);
		$.get('/ww.admin/keepalive.php');
	}
	$('.datatable').each(function(){
		var $this=$(this);
		if ($this.hasClass('desc')) {
			$this.dataTable({"aaSorting": [[0,'desc']]});
		}
		else {
			$this.dataTable();
		}
	});
	$('input.date-human').each(convert_date_to_human_readable);
	$('#menu-top>ul>li>a').each(function(){
		if(!(/#/.test(this.href.toString())))return; // only apply menu to links with '#' in them
		$(this).fgmenu({
			content: $(this).next().html(),
			flyOut:true,
			showSpeed: 400,
			callerOnState: '',
			loadingState: '',
			linkHover: '',
			linkHoverSecondary: '',
			flyOutOnState: ''
		});
	});
	if($('.help').length){
		$('<div id="help-opener"></div>')
			.appendTo('#header')
			.toggle(function(){
				$('.help').css('display','block');
			},
			function(){
				$('.help').css('display','none');
			});
		a=$('.help');
		a.each(function(){
			var hpages=this.className.split(' ')[1].split('/');
			if (hpages.length==1) {
				this.rel='/ww.help/'+hpages[0]+'.html';
			}
			if (hpages.length==2) {
				this.rel='/ww.plugins/'+hpages[0]+'/h/'+hpages[1]+'.html';
			}
			if (!this.title) {
				this.title=$(this).text();
			}
		});
		$('.help').cluetip();
	}
	setTimeout(keepAlive,1700000);
});
