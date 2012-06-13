$(function(){
	function addressPicker() {
		var tables='<div id="addresses-picker" class="align-left">'
			+'<table style="width:100%">';
		for (var i=0;i<userdata.address.length;++i) {
			var addr=userdata.address[i];
			if (i) {
				tables+='<tr><td colspan="4"><hr /></td></tr>';
			}
			tables+='<tr><th>Street</th><td>'
				+htmlspecialchars(addr.street)+'</td>'
				+'<th>Postcode</th><td>'+htmlspecialchars(addr.postcode)
				+'</td></tr>'
				+'<tr><th>Street 2</th><td>'+htmlspecialchars(addr.street2)
				+'</td>'
				+'<th>County</th><td>'+htmlspecialchars(addr.county)
				+'</td></tr>'
				+'<tr><th>Town</th><td>'+htmlspecialchars(addr.town)
				+'</td>'
				+'<th>Country</th><td>'+htmlspecialchars(addr.country)
				+'</td></tr>'
				+'<th>Phone</th><td>'+htmlspecialchars(addr.phone)
				+'</td></tr>'
				+'<tr><th colspan="2"><input type="checkbox" aid="'+i+'"'
				+(addr['default']=='yes'?' checked="checked"':'')
				+'/>'
				+'<span class="__" lang-context="core">default address</span>'
				+'</th><th colspan="2"><button aid="'+i+'" class="__"'
				+' lang-context="core">Choose Address</button></th></tr>'
		}
		tables+='</table></div>';
		var $table=$(tables).dialog({
			modal:true,
			width:400,
			close:function() {
				$table.remove();
			}
		});
		$table.find('input').change(function() {
			var $this=$(this);
			if (!$this.attr('checked')) {
				$this.attr('checked', true);
				return;
			}
			$table.find('input').attr('checked', false);
			$this.attr('checked', true);
			$.post('/a/f=userSetDefaultAddress/aid='+$this.attr('aid'));
		});
		$table.find('button').click(function() {
			var addr=userdata.address[+$(this).attr('aid')];
			$('#online-store-Street,input[name="Street"]').val(addr.street||'');
			$('#online-store-Street2,input[name="Street2"]').val(addr.street2||'');
			$('#online-store-Town,input[name="Town"]').val(addr.town||'');
			$('#online-store-Postcode,input[name="Postcode"]')
				.val(addr.postcode||'');
			$('#online-store-County,input[name="County"]').val(addr.county||'');
			$('#online-store-Country,select[name="Country"]').val(addr.country||'');
			$('#online-store-Phone,input[name="Phone"]').val(addr.phone||'');
			$table.remove();
			$('#online-store-Country').change();
		});
		return false;
	};
	function findMatchingAddress(address){
		function compare(obj1, obj2) {
			function size(obj) {
				var s=0, keyName;
				for (keyName in obj) {
					if (keyName != null) {
						s++;
					}
				}
				return s;
			}
			if (size(obj1) != size(obj2)) {
				return false;
			}
			for (var keyName in obj1) {
				var value1 = obj1[keyName];
				var value2 = obj2[keyName];
		
				if (typeof value1 != typeof value2) {
					return false;
				}
		
				// For jQuery objects:
				if (value1 && value1.length && (value1[0] !== undefined && value1[0].tagName)) {
					if(!value2 || value2.length != value1.length || !value2[0].tagName || value2[0].tagName != value1[0].tagName) {
						return false;
					}
				}
				else if (typeof value1 == 'function' || typeof value1 == 'object') {
					var equal = compare(value1, value2);
					if (!equal) {
						return equal;
					}
				}
				else if (value1 != value2) {
					return false;
				}
			}
			return true;
		}
		if (userdata.id==null) {
			return true;
		}
		for (var i in userdata.address){
			if (userdata.address[i]['default']) {
				address['default']=userdata.address[i]['default'];
			}
			if (compare(userdata.address[i],address)) {
				return true;
			}
		}
		return false;
	}
	function populate_delivery(address, bill){
		if (userdata.id==null) {
			return;
		}
		var current=null;
		$.each(userdata.address, function(i, v) {
			if (v['default']=='yes' && address==null) {
				current=v;
				return;
			}
			if (i==address) {
				current=v;
				return;
			}
		});
		if(current!=null){
			if (current.country=='' && os_post_vars.Country!='') {
				current={
					'street':os_post_vars.Street,
					'street2':os_post_vars.Street2,
					'postcode':os_post_vars.Postcode,
					'town':os_post_vars.Town,
					'county':os_post_vars.County,
					'country':os_post_vars.Country,
					'phone':os_post_vars.Phone
				};
			}
			$('input[name="'+bill+'Street"]').val(current.street);
			$('input[name="'+bill+'Street2"]').val(current.street2);
			$('input[name="'+bill+'Postcode"]').val(current.postcode);
			$('input[name="'+bill+'Town"]').val(current.town);
			$('input[name="'+bill+'County"]').val(current.county);
			$('input[name="'+bill+'Country"]').val(current.country);
			$('input[name="'+bill+'Phone"]').val(current.phone);
		}
	}
	function reloadPage(tabNum) {
		var $form=$('#online-store-form');
		$('<input type="hidden" name="tabNum" value="'+tabNum+'"/>')
			.appendTo($form);
		$form.submit();
	}

	if(userdata.id!=null){
		$.get('/a/f=getUserData',
			function(user){
				$.extend(userdata, user);		
				if (userdata.address && userdata.address.length) {
					var addressButton='<a class="__ ui-button address-picker" '
						+'lang-context="core" href="#">Choose Address</a>';
					html+='<tr><td colspan="2">'+addressButton+'</td></tr>';
					$('.shoppingcartCheckout tr:first').before(html);
					$('.address-picker').click(addressPicker);
					window.__langInit && __langInit();
				}
				var $email=$('input[name="Email"],input[name="Billing_Email"]'),
					$firstName=$('input[name="FirstName"],input[name="Billing_FirstName"]'),
					$lastName=$('input[name="Surname"],input[name="Billing_Surname"]');
				if ($email.val() || $firstName.val() || $lastName.val()) {
					return;
				}
				var name=userdata.name||' ';
				var components=name.split(' ');
				var firstname=components.shift();
				var lastname=components.join(' ');
				$firstName.val(firstname);
				$lastName.val(lastname);
				$('input[name="Phone"],input[name="Billing_Phone"]').val(userdata.phone);
				$email.val(userdata.email);
				populate_delivery(null, '');
				populate_delivery(null, 'Billing_');
			},
			'json'
		);
		$('input[name="action"]').click(function(){
			var address={		
				street:$('input[name="Street"]').val(),
				street2:$('input[name="Street2"]').val(),
				town:$('input[name="Town"]').val(),
				county:$('input[name="County"]').val(),
				country:$('input[name="Country"]').val(),
			};
			var result=findMatchingAddress(address);
			if(result==false){
				$('.shoppingcartCheckout')
					.append('<input type="hidden" name="save-address" value="1"/>');
			}
		});
		$('select[name="address"]').live('change',function(){
			var bill=($(this).hasClass('billing'))?'Billing_':'';
			populate_delivery($(this).val(),bill);
		});
	}
	switch(+os_post_vars._viewtype) {
		case 2: case 3: // { 5-step
			// { list of panels
			var tabs=[];
			tabs.push('Login');
			tabs.push('Delivery Address');
			tabs.push('Billing Address');
			if (os_post_vars._pandp) {
				tabs.push('Delivery Options');
			}
			tabs.push('Payment');
			// }
			// { setup
			var html='<div id="online-store-checkout-accordion-wrapper">';
			// { panels
			for (var i=0;i<tabs.length;++i) {
				html+='<h2 panel="'+tabs[i]+'" class="__" lang-context="core">'
					+'<a href="#">'+tabs[i]+'</a></h2>'
					+'<div>&nbsp;</div>';
			}
			// }
			html+='</div>';
			// { add form
			var inps=[
				'Billing_Country', 'Billing_County', 'Billing_Email',
				'Billing_FirstName', 'Billing_Phone', 'Billing_Postcode',
				'Billing_Street', 'Billing_Street2', 'Billing_Surname',
				'Billing_Town', 'Country', 'County', 'Email', 'FirstName', 'Phone',
				'Postcode', 'Street', 'Street2', 'Surname', 'Town',
				'_payment_method_type', 'action', 'os_no_submit', 'os_pandp',
				'os_voucher'
			];
			html+='<form id="online-store-form" method="post" action="'
				+document.location.toString().replace(/\?.*/, '')
				+'">'
				+'<input type="hidden" name="'
				+inps.join('"/><input type="hidden" name="', inps)
				+'"/></form>';
			// }
			$(html).appendTo('#online-store-wrapper');
			$('input[name=os_no_submit]').val(1);
			// }
			function showStep(ev, ui) {
				if (!ui.newHeader) {
					ui.newHeader=$('#online-store-wrapper>div>h2:first-child');
					ui.newContent=$('#online-store-wrapper>div>div:nth-child(2)');
				}
				var content=ui.newContent;
				var panel=ui.newHeader.attr('panel');
				$('#online-store-wrapper>div>div').empty();
				switch(panel) {
					case 'Login': // {
						if (userdata.id) {
							content.text('logged in as '+userdata.name);
							setTimeout(function() {
								$('#online-store-checkout-accordion-wrapper').accordion(
									'activate',
									'h2[panel="Delivery Address"]'
								);
							}, 500);
							return;
						}
						var table='<table style="width:100%"><tr>'
							// { login
							+'<td class="user-login">'
							+'<label class="email">'
							+'<span class="__" lang-context="core">Email</span>'
							+'<input type="email"/>'
							+'</label>'
							+'<label class="password">'
							+'<span class="__" lang-context="core">Password</span>'
							+'<input type="password"/>'
							+'</label>'
							+'<label>'
							+'<span>&nbsp;</span>'
							+'<button class="__" lang-context="core">Login</button>'
							+'</label>'
							+'</td>'
							// }
							// { or
							+'<td class="__ or" lang-context="core">or</td>'
							// }
							// { register
							+'<td class="user-register">'
							+'<label class="email">'
							+'<span class="__" lang-context="core">Email</span>'
							+'<input type="email"/>'
							+'</label>'
							+'<label>'
							+'<span>&nbsp;</span>'
							+'<button class="__" lang-context="core">Register</button>'
							+'</label>'
							+'</td>'
							// }
							// { or
							+'<td class="__ or" lang-context="core">or</td>'
							// }
							// { checkout as guest
							+'<td class="user-guest">'
							+'<label>'
							+'<span>&nbsp;</span>'
							+'<button class="__" lang-context="core">Checkout as guest'
							+'</button></label></td>'
							// }
							+'</tr></table>';
						content.html(table);
						var fnSubmit=function(ret) {
							if (ret.error) {
								return alert(ret.error);
							}
							reloadPage(0);
						};
						content.find('.user-login button').click(function() {
							var $form=$('#online-store-wrapper .user-login');
							var email=$form.find('.email input').val();
							var passw=$form.find('.password input').val();
							$.post('/a/f=login', {
								'email':email,
								'password':passw
							}, fnSubmit, 'json');
						});
						content.find('.user-register button').click(function() {
							var $form=$('#online-store-wrapper .user-register');
							var email=$form.find('.email input').val();
							if (os_userRegWithoutVerification) {
								$.post('/a/f=userGetUid', {
									'email':email
								}, function(ret) {
									if (+ret.uid) {
										return alert(
											'That user account already exists.'
											+' Please login instead'
										);
									}
									var $dialog=$('<div class="online-store">'
										+'<p>Please enter the password you want to use.</p>'
										+'<label class="password"><span class="__" '
										+'lang-context="core">Password</span>'
										+'<input type="password" id="form-pass1"/></label>'
										+'<label class="repeat-password"><span class="__" '
										+'lang-context="core">Repeat Password</span>'
										+'<input type="password" id="form-pass2"/></label>'
										+'</div>'
									).dialog({
										'modal': true,
										'width': 350,
										'close':function() {
											$dialog.remove();
										},
										'buttons':{
											'Register':function() {
												var pass1=$('#form-pass1').val(),
													pass2=$('#form-pass2').val();
												if (!pass1 || pass1!=pass2) {
													return alert('Passwords must be equal');
												}
												$.post('/a/p=online-store/f=userRegister', {
													'password':pass1,
													'email':email
												}, function(ret) {
													if (ret.error) {
														return alert(ret.error);
													}
													alert('Thank you. We are logging you in now.');
													$.post('/a/f=login', {
														'email':email,
														'password':pass1
													}, fnSubmit);
												});
											}
										}
									});
								}, 'json');
							}
							else {
								$.post('/a/f=sendRegistrationToken', {
									'email':email
								}, function(ret) {
									if (ret.error) {
										return alert(ret.error);
									}
									var $dialog=$('<div class="online-store">'
										+'<h2 class="__" lang-context="core">'
										+'Verify your email address</h2>'
										+'<p class="__" lang-context="core">We have send a 5-digit '
										+'token to your email address to verify it. Please check '
										+'your email, then enter the token below. Then enter a '
										+'password you want to use for this site.</p>'
										+'<label class="token"><span class="__" lang-context="core">'
										+'Token</span><input id="form-token"/></label>'
										+'<label class="password"><span class="__" '
										+'lang-context="core">Password</span>'
										+'<input type="password" id="form-pass1"/></label>'
										+'<label class="repeat-password"><span class="__" '
										+'lang-context="core">Repeat Password</span>'
										+'<input type="password" id="form-pass2"/></label>'
										+'</div>'
									).dialog({
										'modal': true,
										'width': 350,
										'close':function() {
											$dialog.remove();
										},
										'buttons':{
											'Register':function() {
												var token=$('#form-token').val(),
													pass1=$('#form-pass1').val(),
													pass2=$('#form-pass2').val();
												if (token.length!=5) {
													return alert('Token must be 5 digits in length');
												}
												if (!pass1 || pass1!=pass2) {
													return alert('Passwords must be equal');
												}
												$.post('/a/f=register', {
													'token':token,
													'password':pass1
												}, function(ret) {
													if (ret.error) {
														return alert(ret.error);
													}
													alert('Thank you. We are logging you in now.');
													$.post('/a/f=login', {
														'email':email,
														'password':pass1
													}, fnSubmit);
												});
											}
										}
									});
								}, 'json');
							}
						});
						content.find('.user-guest button').click(function() {
							$('#online-store-checkout-accordion-wrapper').accordion(
								'activate',
								'h2[panel="Delivery Address"]'
							);
						});
					break; // }
					case 'Delivery Address': // {
						var addressButton='';
						if (userdata.address) {
							addressButton='<button class="__ address-picker" '
								+'lang-context="core">Choose Address</button>';
						}
						var html=
							'<div id="online-store-delivery">'
							// { contact info
							+'<div id="online-store-delivery-personal">'
							+addressButton
							+'<label><span class="__" lang-context="core">First Name</span>'
							+'<input id="online-store-FirstName"/></label>'
							+'<label><span class="__" lang-context="core">Surname</span>'
							+'<input id="online-store-Surname"/></label>'
							+'<label><span class="__" lang-context="core">Phone</span>'
							+'<input id="online-store-Phone"/></label>'
							+'<label><span class="__" lang-context="core">Email</span>'
							+'<input id="online-store-Email"/></label>'
							+'</div>'
							// }
							// { address
							+'<div id="online-store-delivery-address">'
							+'<label><span class="__" lang-context="core">Street</span>'
							+'<input id="online-store-Street"/></label>'
							+'<label><span class="__" lang-context="core">Street 2</span>'
							+'<input id="online-store-Street2"/></label>'
							+'<label><span class="__" lang-context="core">Town</span>'
							+'<input id="online-store-Town"/></label>'
							+'<label><span class="__" lang-context="core">Postcode</span>'
							+'<input id="online-store-Postcode"/></label>'
							+'<label><span class="__" lang-context="core">County</span>'
							+'<input id="online-store-County"/></label>'
							+'<label><span class="__" lang-context="core">Country</span>'
							+'<select id="online-store-Country"><option/></select></label>'
							+'</div>'
							// }
							// { next
							+'<div id="online-store-delivery-next">'
							+'<button class="__ next" lang-context="core">Next</button>'
							+'</div>'
							// }
							+'</div>';
						content.html(html);
						$('#online-store-delivery input').each(function() {
							var $this=$(this),
								name=$this.attr('id').replace('online-store-', '');
							$this
								.val($('input[name='+name+']').val())
								.change(function() {
									$('input[name='+name+']').val($this.val());
								});
						});
						$('#online-store-delivery .address-picker').click(addressPicker);
						$.get('/a/p=online-store/f=getCountries/page_id='+pagedata.id,
							function(ret) {
								var $this=$('#online-store-Country');
								ret.sort();
								for (var i=0;i<ret.length;++i) {
									$('<option/>')
										.text(ret[i])
										.attr('value', ret[i])
										.appendTo($this);
								}
								$this.val($('input[name=Country]').val());
								$this.change(function() {
									$('input[name=Country]').val($this.val());
									reloadPage(1);
								});
							}
						);
						$('#online-store-delivery button.next').click(function() {
							if (checkDeliveryAddress()) {
								$accordion.accordion(
									'activate',
									'h2[panel="Billing Address"]'
								);
							}
						});
					break; // }
					case 'Billing Address': // {
						var html=
							'<div id="online-store-billing">'
							+'<div style="display:block;">'
							+'<input type="checkbox" id="badd-is-diff"/> Is billing address'
							+' different from delivery address?</div>'
							// { contact info
							+'<div id="online-store-billing-personal">'
							+'<label><span class="__" lang-context="core">First Name</span>'
							+'<input id="online-store-FirstName"/></label>'
							+'<label><span class="__" lang-context="core">Surname</span>'
							+'<input id="online-store-Surname"/></label>'
							+'<label><span class="__" lang-context="core">Phone</span>'
							+'<input id="online-store-Phone"/></label>'
							+'<label><span class="__" lang-context="core">Email</span>'
							+'<input id="online-store-Email"/></label>'
							+'</div>'
							// }
							// { address
							+'<div id="online-store-billing-address">'
							+'<label><span class="__" lang-context="core">Street</span>'
							+'<input id="online-store-Street"/></label>'
							+'<label><span class="__" lang-context="core">Street 2</span>'
							+'<input id="online-store-Street2"/></label>'
							+'<label><span class="__" lang-context="core">Town</span>'
							+'<input id="online-store-Town"/></label>'
							+'<label><span class="__" lang-context="core">Postcode</span>'
							+'<input id="online-store-Postcode"/></label>'
							+'<label><span class="__" lang-context="core">County</span>'
							+'<input id="online-store-County"/></label>'
							+'<label><span class="__" lang-context="core">Country</span>'
							+'<select id="online-store-Country"><option/></select></label>'
							+'</div>'
							// }
							// { next
							+'<div id="online-store-billing-next">'
							+'<button class="__" lang-context="core">Next</button>'
							+'</div>'
							// }
							+'</div>';
						content.html(html);
						var different=0;
						$('#online-store-billing input[type!=checkbox]').each(function() {
							var $this=$(this),
								del_name=$this.attr('id').replace('online-store-', ''),
								name='Billing_'+del_name,
								del_val=$('input[name='+del_name+']').val(),
								val=$('input[name='+name+']').val();
							$this
								.val(val)
								.change(function() {
									$('input[name='+name+']').val($this.val());
								});
							if (del_val!=val) {
								different=1;
							}
						});
						if (different) {
							$('#badd-is-diff').attr('checked', true);
						}
						$('#badd-is-diff')
							.change(function() {
								var checked=$(this).is(':checked');
								if (!checked) {
									$('#online-store-billing input[type!=checkbox]').each(function() {
										var $this=$(this),
											del_name=$this.attr('id').replace('online-store-', ''),
											del_val=$('input[name='+del_name+']').val();
										$this.val(del_val);
										$('input[name=Billing_'+del_name+']').val(del_val);
									});
									$('#online-store-billing select').each(function() {
										var $this=$(this),
											del_name=$this.attr('id').replace('online-store-', ''),
											del_val=$('input[name='+del_name+']').val();
										$this.val(del_val);
										$('input[name=Billing_'+del_name+']').val(del_val);
									});
								}
								$('#online-store-billing input[type!=checkbox],'
									+'#online-store-billing select').attr('disabled', !checked);
							})
							.change();
						$.get('/a/p=online-store/f=getCountries/page_id='+pagedata.id,
							function(ret) {
								ret.sort();
								var $this=$('#online-store-Country');
								for (var i=0;i<ret.length;++i) {
									$('<option/>')
										.text(ret[i])
										.attr('value', ret[i])
										.appendTo($this);
								}
								$this.val($('input[name=Billing_Country]').val());
								$this.change(function() {
									$('input[name=Billing_Country]').val($this.val());
								});
							}
						);
						$('#online-store-billing button').click(function() {
							if (checkBillingAddress()) {
								var next=$('h2[panel="Delivery Options"]').length
									?'Delivery Options':'Payment';
								$accordion.accordion(
									'activate',
									'h2[panel="'+next+'"]'
								);
							}
						});
						if (!checkDeliveryAddress()) {
							setTimeout(function() {
								$accordion.accordion(
									'activate',
									'h2[panel="Delivery Address"]'
								);
							}, 500);
						}
					break; // }
					case 'Delivery Options': // {
						content.html(
							'<div id="online-store-pandp"><select/>'
							+'<button class="__" lang-context="core">Next</button></div>'
						);
						$.get('/a/p=online-store/f=pandpGetList/page_id='+pagedata.id,
							function(ret) {
								var $this=$('#online-store-pandp select');
								for (var i=0;i<ret.length;++i) {
									$('<option/>')
										.text(ret[i])
										.attr('value', i)
										.appendTo($this);
								}
								$this.val($('input[name=os_pandp]').val());
								$this.change(function() {
									$('input[name=os_pandp]').val($this.val());
									reloadPage(3);
								});
							}
						);
						$('#online-store-pandp button').click(function() {
							$accordion.accordion(
								'activate',
								'h2[panel="Payment"]'
							);
						});
						if (!checkBillingAddress()) {
							setTimeout(function() {
								$accordion.accordion(
									'activate',
									'h2[panel="Billing Address"]'
								);
							}, 500);
						}
					break; // }
					case 'Payment': // {
						content.html(
							'<div id="online-store-payment-method"><select/>'
							+'<button class="__" lang-context="core">Proceed to Payment'
							+'</button></div>'
						);
						if (+os_post_vars._viewtype==3 && os_post_vars._hidebasket) {
							var $basket=$('#onlinestore-checkout');
							var $table=$('<table class="online-store-payment-due"/>')
								.append($basket.find('.os_basket_totals'))
								.insertBefore('#online-store-payment-method');
						}
						$.get('/a/p=online-store/f=paymentTypesList/page_id='+pagedata.id,
							function(ret) {
								if (ret.error) {
									return alert(ret.error);
								}
								var $this=$('#online-store-payment-method select');
								$.each(ret, function(k, v) {
									$('<option/>')
										.text(v)
										.attr('value', k)
										.appendTo($this);
								});
								$this.val($('input[name=_payment_method]').val());
								$this.change(function() {
									$('input[name=_payment_method]').val($this.val());
								});
								$('#online-store-payment-method button').click(function() {
									$('#online-store-form input[name=os_no_submit]').remove();
									$('#online-store-form')
										.append(
											'<input type="hidden" name="action" '
											+'value="Proceed to Payment" />'
										)
										.submit();
								});
							}
						);
						if (!checkBillingAddress()) {
							setTimeout(function() {
								$accordion.accordion(
									'activate',
									'h2[panel="Billing Address"]'
								);
							}, 500);
						}
					break; // }
				}
			}
			function checkBillingAddress() {
				var errs=[];
				if (!$('input[name=Billing_FirstName]').val()) {
					errs.push('You must fill in your first name');
				}
				if (!$('input[name=Billing_Surname]').val()) {
					errs.push('You must fill in your surname');
				}
				if (!$('input[name=Billing_Email]').val()) {
					errs.push('You must fill in your email address');
				}
				if (!$('input[name=Billing_Phone]').val()) {
					errs.push('You must fill in your phone');
				}
				if (!$('input[name=Billing_Street]').val()) {
					errs.push('You must fill in your street');
				}
				if (!$('input[name=Billing_Country]').val()) {
					errs.push('You must fill in your country');
				}
				if (errs.length) {
					alert(errs.join("\n"));
					return false;
				}
				return true;
			}
			function checkDeliveryAddress() {
				var errs=[];
				if (!$('input[name=FirstName]').val()) {
					errs.push('You must fill in your first name');
				}
				if (!$('input[name=Surname]').val()) {
					errs.push('You must fill in your surname');
				}
				if (!$('input[name=Email]').val()) {
					errs.push('You must fill in your email address');
				}
				if (!$('input[name=Phone]').val()) {
					errs.push('You must fill in your phone');
				}
				if (!$('input[name=Street]').val()) {
					errs.push('You must fill in your street');
				}
				if (!$('input[name=Country]').val()) {
					errs.push('You must fill in your country');
				}
				if (errs.length) {
					alert(errs.join("\n"));
					return false;
				}
				return true;
			}
			// { setup the accordion
			var tabNum=+os_post_vars.tabNum;
			var $accordion=$('#online-store-checkout-accordion-wrapper').accordion({
				'autoHeight':false,
				'create':showStep,
				'changestart':showStep
			});
			if (!tabNum) {
				tabNum=0;
			}
			setTimeout(function() {
				$accordion.accordion('activate', tabNum);
			}, 1);
			// }
			if (+os_post_vars._viewtype==3) {
				var $form=$('#online-store-form');
				if (os_post_vars._hidebasket) {
					$('#onlinestore-checkout').css('display', 'none');
					$('#online-store-wrapper').css('display', 'block');
					$('<input type="hidden" name="_hidebasket" value="1"/>')
						.appendTo($form);
				}
				else {
					$('#onlinestore-checkout').css('display', 'block');
					$('<button id="online-store-gotocheckout">Checkout</button>')
						.click(function() {
							$('<input type="hidden" name="_hidebasket" value="1"/>')
								.appendTo($form);
							$('<input type="hidden" name="os_no_submit" value="1"/>')
								.appendTo($form);
							$form.submit();
						})
						.insertAfter('#onlinestore-checkout');
					$('#online-store-wrapper').css({
						'display':'none'
					});
				}
			}
		break; // }
		default: // {
			$('select[name=Country]').change(function() {
				var $this=$(this);
				$('<input type="hidden" name="os_no_submit" value="1"/>')
					.insertAfter($this);
				$this.closest('form').submit();
			});
		// }
	}
	// { voucher handlers
	$('input[name=os_voucher]').change(function() {
		var $this=$(this);
		var code=$this.val();
		if (!code) {
			return;
		}
		var email=$('#ww-pagecontent input[name=Email]').val();
		$.post('/a/p=online-store/f=checkVoucher', {
			"email": email,
			"code" : code
		}, function(ret) {
			if (ret.error) {
				if (ret.error=='your email address is not associated with this voucher') {
					var $dialog=$('<div><p class="__" lang-context="core">'
						+'This voucher is for a specific email address.'
						+' Please enter the correct email address below.'
						+'<br/><input id="dialog-email"/></div>')
						.dialog({
							'modal':true,
							'buttons':{
								'Check':function() {
									$('#ww-pagecontent input[name=Email]')
										.val($('#dialog-email').val())
										.change();
									$('input[name=os_voucher]').change();
									$dialog.remove();
								}
							}
						});
					return;
				}
				$('<em>'+ret.error+'</em>').dialog({
					"modal": true
				});
				$this.val('');
				return;
			}
			var $form=$('#online-store-form');
			$this.appendTo($form);
			$('<input type="hidden" name="os_no_submit" value="1"/>')
				.insertAfter($this);
			$form.submit();
		}, 'json');
	});
	$('.online-store-voucher-remove')
		.css('cursor', 'pointer')
		.click(function() {
			var form=$('#online-store-form');
			$form.find('input[name="os_voucher"]').val('');
			$form.submit();
		});
	// }
	if (os_post_vars) {
		for (var i in os_post_vars) {
			$('input[name="'+i+'"],select[name="'+i+'"],textarea[name="'+i+'"]')
				.val(os_post_vars[i]);
		}
	}
});
