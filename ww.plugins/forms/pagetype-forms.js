function Forms_Pagetype_forms() {
	function updateValues() {
		switch(activeTab) {
			case 0: // { main - not needed
				return;
				// }
			case 1: // { form fields
				return updateFormFields();
				// }
			case 2: // { header/footer
				return updateHeaderFooter(true);
				// }
			case 3: // { template
				return updateTemplate();
				// }
		}
	}
	function showMain(panel) {
		// { draw the table
		$('<table class="wide">'
			+'<tr><th>Send as Email</th><td id="tc1"><select id="tc2">'
			+'<option value="0">No</option><option value="1">Yes</option>'
			+'</select></td></tr>'
			+'<tr><th>Record in DB</th><td id="tc6"><select id="tc5">'
			+'<option value="0">No</option><option value="1">Yes</option>'
			+'</select></td></tr>'
			+'<tr><th>Use Captcha</th><td id="tc9"><select id="tc10">'
			+'<option value="0">No</option><option value="1">Yes</option>'
			+'</select></td></tr>'
			+'<tr><th>Show Help</th><td id="tc11"><select id="tc13">'
			+'<option value="0">using tooltips</option>'
			+'<option value="1">using a CSS selector</option>'
			+'</select><span id="tc14"/></td></tr>'
			+'</table>')
			.appendTo($('#t0').empty());
		// }
		// { send as email
		function tc2Change(e) {
			if (e!=='noupdate') {
				updateMain();
			}
			var f=+$('#tc2').val();
			page_vars.forms_send_as_email=f;
			function tc3Change() {
				var v=$('#tc3').val();
				if (v=='') {
					v='info@'+document.location.toString()
						.replace(/https?:\/\/(www\.)?([^\/]*)\/.*/, '$2');
					$('#tc3').val(v);
				}
				page_vars.forms_recipient=v;
			}
			if (f) {
				$('<span id="tc4"> to <input type="email" id="tc3"/></span>')
					.appendTo('#tc1');
				$('#tc3')
					.val(page_vars.forms_recipient)
					.change(tc3Change);
				tc3Change();
			}
			else {
				$('#tc4').remove();
			}
		}
		$('#tc2')
			.val(page_vars.forms_send_as_email==='0'?0:1)
			.change(tc2Change);
		tc2Change('noupdate');
		// }
		// { record in db
		function tc5Change() {
			var f=+$('#tc5').val();
			page_vars.forms_record_in_db=f;
			if (f) {
				$('<span id="tc7"> to export recorded data, <a href="javascript:;" '
					+'id="tc8">click here</a></span>')
					.appendTo('#tc6');
				$('#tc8').click(function(){
					alert('TODO');
				});
			}
			else {
				$('#tc7').remove();
			}
		}
		$('#tc5')
			.val(+page_vars.forms_record_in_db)
			.change(tc5Change);
		tc5Change();
		// }
		// { captcha
		function tc10Change() {
			var f=$('#tc10').val();
			page_vars.forms_captcha_required=f;
			if (f==='0') {
				$('<span id="tc12">If you disable the captcha, you risk receiving '
					+'spam</span>').appendTo('#tc9');
			}
			else {
				$('#tc12').remove();
			}
		}
		$('#tc10')
			.val(page_vars.forms_captcha_required==='0'?0:1)
			.change(tc10Change);
		tc10Change();
		// }
		// { help
		function tc13Change() {
			var f=+$('#tc13').val();
			updateMain();
			if (!f) {
				$('#tc14').html('if help text is supplied, it will appear when the '
					+'mouse is over the inputs');
			}
			else {
				$('<input id="tc15"/>')
					.val(page_vars.forms_helpSelector||'')
					.appendTo($('#tc14').empty())
					.change(updateMain);
			}
		}
		$('#tc13')
			.val(+page_vars.forms_helpType?1:0)
			.change(tc13Change);
		tc13Change();
		// }
	}
	function showFormFields(panel, index) {
		function showExtrasSelectbox(e) {
			function addRow(val) {
				var $row=$('<tr/>').appendTo('#pfp-type-specific-table');
				$('<td/>')
					.append($('<input class="wide"/>').val(val).change(checkRows))
					.appendTo($row);
			}
			function checkRows() {
				var empty=0;
				$('#pfp-type-specific-table input').each(function() {
					if ($(this).val()=='') {
						empty=1;
					}
				});
				if (!empty) {
					addRow('');
				}
			}
			$('<p>Enter the list of options to choose from</p><table id="pfp-type-specific-table" class="wide tight"/>')
				.appendTo('#pfp-type-specific');
			var rows=e.split("\n");
			for (var i=0;i<rows.length;++i) {
				addRow(rows[i]);
			}
			checkRows();
		}
		function showType() {
			var type=$('.pfp-type select').val();
			$('#pfp-type-specific').empty();
			switch (type) {
				case 'email': // {
					$('#pfp-type-specific')
						.append(
							'<input type="checkbox" id="ffts"/> tick this if you want '
							+'the viewer to verify their email address before being '
							+'allowed to submit the form<br /><input type="checkbox" '
							+'id="fftt"/> use this as the reply-to on the form'
						);
					$('#ffts').attr('checked', field.extra=='1');
					$('#fftt')
						.attr(
							'checked',
							field.name==page_vars.forms_replyto || !page_vars.forms_replyto
						) 
						.change(function() {
							var $t=$(this);
							if ($t.is(':checked')) {
								console.log($t, $t.closest('.ui-accordion-content'), $t.closest('.ui-accordion-content').prev(), $t.closest('.ui-accordion-content').prev().find('a'), $t.closest('.ui-accordion-content').prev().find('a').text());
								page_vars.forms_replyto=$t
									.closest('.ui-accordion-content').prev().find('a').text();
							}
						});
					$('#fftt').change();
					return;
					// }
				case 'date': // {
					$('<p>What format should the date be in? '
						+'<a href="http://docs.jquery.com/UI/Datepicker/formatDate" '
						+'target="_blank">examples</a></p>')
						.appendTo('#pfp-type-specific');
					return $('<input/>')
						.val(field.extra||'yy-mm-dd')
						.appendTo('#pfp-type-specific');
					// }
				case 'selectbox': // {
					return showExtrasSelectbox(field.extra||'');
					// }
				case 'html-block': // {
					return $('<textarea>')
						.val(field.extra||'')
						.appendTo('#pfp-type-specific')
						.ckeditor();
					// }
			}
		}
		$(panel).empty();
		var field=false;
		var fields=page_vars.forms_fields;
		var html='<div id="df1">';
		for (var i=0;i<fields.length;++i) {
			html+='<h3 id="f'+i+'"><a href="#">'+htmlspecialchars(fields[i].name)
				+'</a></h3><div/>';
		}
		var types={'email':'email', 'input box':'single line of text',
			'textarea':'multiple lines of text', 'date': 'date',
			'checkbox':'checkbox', 'selectbox':'selectbox',
			'hidden':'hidden message', 'ccdate':'credit card expiry date',
			'html-block':'html block', 'page-next':'next page link',
			'page-previous':'previous page link', 'page-break':'page break',
			'file': 'file-upload'
		};
		$(html+'</div>')
			.appendTo(panel)
			.accordion({
				'changestart':function(e, ui) {
					updateFormFields();
					$('.form-field-panel').remove();
					if (!ui.newHeader.context) {
						return;
					}
					var index=+ui.newHeader.context.id.replace(/f/, '');
					field=fields[index];
					var $wrapper=$(ui.newContent.context).next();
					$wrapper
						.append('<table class="form-field-panel wide">'
							+'<tr><th>Name</th><td class="pfp-name"></td>'
							+'<td rowspan="5" id="pfp-type-specific"></td></tr>'
							+'<tr><th>Type</th><td class="pfp-type"></td></tr>'
							+'<tr><th>Required</th><td class="pfp-required"></td></tr>'
							+'<tr><th>Help text</th><td class="pfp-help"></td></tr>'
							+'<tr><td colspan="2"><a href="javascript:;" id="pfp-delete"'
							+' title="delete">[x]</a></td></tr>'
							+'</table>'
						);
					$('<input/>').val(field.name||'').appendTo('.pfp-name', $wrapper);
					// { required
					$('<select><option value="0">No</option>'
						+'<option value="1">Yes</option></select>'
					)
						.val(field.isrequired).appendTo('.pfp-required', $wrapper);
					// }
					// { type
					var opts=[];
					$.each(types, function(k, v) {
						opts.push('<option value="'+k+'">'+v+'</option>');
					});
					$('<select>'+opts.join()+'</select>')
						.val(field.type)
						.change(showType)
						.appendTo('.pfp-type', $wrapper);
					// }
					// { help
					var $help=$('<textarea style="width:100%;height:1em"/>')
						.val(field.help||'')
						.appendTo('.pfp-help', $wrapper);
					setTimeout(function(){$help.autoGrow();}, 1);
					// }
					// { delete button
					$('#pfp-delete').click(function() {
						if (!confirm('are you sure you want to remove this?')) {
							return;
						}
						var dfs=[];
						for (var i=0;i<fields.length;++i) {
							if (i!=index) {
								dfs.push(fields[i]);
							}
						}
						page_vars.forms_fields=dfs;
						showFormFields(panel, -1);
					});
					// }
					showType();
					$wrapper.find('input,textarea,select').change(updateFormFields);
				},
				'active':false,
				'autoHeight':false,
				'animated':false,
				'collapsible':true,
				'create':function() {
					if (index) {
						$('#df1').accordion('activate', index);
					}
				}
			});
		$('<button>add field</button>')
			.click(function() {
				var name=prompt('What do you want to name this field?', 'fieldname');
				if (name===false) {
					return;
				}
				page_vars.forms_fields.push({
					'name':name,
					'isrequired':0,
					'type':'inputbox'
				});
				showFormFields(panel, page_vars.forms_fields.length-1);
			})
			.appendTo(panel);
	}
	function showHeaderFooter(panel) {
		$('<h3>Header</h3>').appendTo(panel);
		var header=$('<textarea id="tc1"/>')
			.appendTo(panel)
			.val(page_vars._body||'')
			.ckeditor(function(){
				this.on('change', updateHeaderFooter);
			});
		$('<h3>Footer</h3>').appendTo(panel);
		var footer=$('<textarea id="tc2"/>')
			.appendTo(panel)
			.val(page_vars.footer||'')
			.ckeditor(function(){
				this.on('change', updateHeaderFooter);
			});
	}
	function showTemplate(panel) {
		var select='<select id="tc1">'
			+'<option value="table">auto-template, using a table</option>'
			+'<option value="div">auto-template, using divs</option>'
			+'<option value="template">specify your own template</option>'
			+'</select>';
		$(select)
			.appendTo(panel)
			.val(page_vars.forms_template
				?'template'
				:(page_vars.forms_htmltype||'table')
			)
			.change(function() {
				$('#tc2').remove();
				var tc2=$('<div id="tc2"/>').appendTo(panel);
				if (page_vars.forms_template || $(this).val()=='template') {
					$('<textarea id="tc3"/>')
						.val(page_vars.forms_template||'')
						.appendTo(tc2)
						.ckeditor();
				}
			})
			.change();
	}
	function updateFormFields() {
		var $panel=$('#t1>div>div.ui-accordion-content-active');
		var index=$panel.index('#t1>div>div');
		if (index<0) {
			return;
		}
		page_vars.forms_fields[index].name=$('.pfp-name input').val();
		page_vars.forms_fields[index].isrequired=$('.pfp-required select').val();
		page_vars.forms_fields[index].type=$('.pfp-type select').val();
		switch (page_vars.forms_fields[index].type) {
			case 'date': // {
				page_vars.forms_fields[index].extra=$('#pfp-type-specific input')
					.val()||'yy-mm-dd';
				break; // }
			case 'email': // {
				page_vars.forms_fields[index].extra=$('#ffts:checked').length;
				break; // }
			case 'html-block': // {
				page_vars.forms_fields[index].extra=$('#pfp-type-specific textarea')
					.val();
				break; // }
			case 'selectbox': // {
				var e=[];
				$('#pfp-type-specific tr').each(function() {
					var $inps=$(this).find('input');
					if ($inps.length && $inps[0].value!='') {
						e.push($inps[0].value);
					}
				});
				page_vars.forms_fields[index].extra=e.join("\n");
				break; // }
		}
		page_vars.forms_fields[index].help=$('.pfp-help textarea').val();
	}
	function updateMain() {
		page_vars.forms_helpType=$('#tc13').val();
		if (page_vars.forms_helpType=='1' && $('#tc15').length) {
			page_vars.forms_helpSelector=$('#tc15').val();
		}
		page_vars.forms_send_as_email=''+$('#tc2').val();
	}
	function updateHeaderFooter() {
		page_vars._body=CKEDITOR.instances['tc1'].getData();
		page_vars.footer=CKEDITOR.instances['tc2'].getData();
		if (del===true) {
			CKEDITOR.remove(CKEDITOR.instances['tc1']);
			CKEDITOR.remove(CKEDITOR.instances['tc2']);
		}
	}
	function updateTemplate() {
		page_vars.forms_htmltype=$('#tc1').val();
		if (page_vars.forms_htmltype=='template') {
			page_vars.forms_template=CKEDITOR.instances['tc3'].getData();
			CKEDITOR.remove(CKEDITOR.instances['tc3']);
		}
	}
	// { initiate the form
	var activeTab=-1;
	if (!page_vars.forms_fields || $.type(page_vars.forms_fields)!=='array') {
		page_vars.forms_fields=$.parseJSON(page_vars.forms_fields||'[]');
	}
	window.pageForm='forms';
	var $content=$('#body-wrapper');
	$('<div id="product-types-edit-form"><ul>'
		+'<li><a href="#t0">Main Details</a></li>'
		+'<li><a href="#t1">Form Fields</a></li>'
		+'<li><a href="#t2">Header/Footer</a></li>'
		+'<li><a href="#t3">Template</a></li>'
		+'</ul><div id="t0"/><div id="t1"/><div id="t2"/><div id="t3"/></div>'
	)
		.appendTo($content)
		.tabs({
			'select':updateValues,
			'show':function(e, ui) {
				$('#product-types-edit-form>div').empty();
				activeTab=ui.index;
				switch (ui.index) {
					case 0: // { main
						return showMain(ui.panel);
						// }
					case 1: // { form fields
						return showFormFields(ui.panel);
						// }
					case 2: // { header
						return showHeaderFooter(ui.panel);
						// }
					case 3: // { template
						return showTemplate(ui.panel);
						// }
				}
			}
		});
	// }
	// { make sure autogrow is loaded
	var $t=$('<textarea/>');
	if (!$t.autoGrow) {
		$.getScript('/j/jquery.autogrowtextarea.js');
	}
	// }
}
