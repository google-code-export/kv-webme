$(function(){
	var $form=$('#userauthentication-widget');
	$form.find('button,img').click(function(){
		var $this=$(this), btn='';
		if ($this.text()=='email and password') {
			btn='email and password';
		}
		if ($this[0].className=='facebook') {
			btn='facebook';
		}
		switch (btn) {
			case 'email and password': // {
				$('<table id="userauthentication-email-and-password">'
					+'<tr><th>email</th><td><input type="email" /></td></tr>'
					+'<tr><th>password</th><td><input type="password" /></td></tr>'
					+'</table>'
				).dialog({
					modal:true,
					buttons:{
						'log in':function(){
							var $table=$('#userauthentication-email-and-password');
							var email=$table.find('input[type=email]').val(),
								password=$table.find('input[type=password]').val();
							$.post('/ww.plugins/privacy/frontend/widget-login-email-and-password.php', {
									email:email,
									password:password
								}, function(ret) {
									if (ret.error) {
										return alert(ret.error);
									}
									document.location=ret.redirect_url;
								}, 'json'
							);
						}
					},
					close:function(){
						$('#userauthentication-email-and-password').remove();
					}
				});
			break; // }
			case 'facebook': // {
				var fbappid=$this.attr('appid');
				var widget_id=$form.attr('widget-id');
				document.location='https://www.facebook.com/dialog/oauth?'
					+'client_id='+fbappid+'&scope=email&type=web_server'
					+'&redirect_uri='
					+document.location.toString().replace(
						/(https?:\/\/[^\/]*\/).*/,
						'$1ww.plugins/privacy/frontend/widget-login-facebook.php'
					)+'/widget-id='+widget_id+'/';
			break; // }
		}
	});
});
