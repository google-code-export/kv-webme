function products_form_validate(){
	var errors=[];
	var req=$('#products-form .required');
	req.each(function(){
		if(!this.value)errors.push('The '+this.name+' field must be filled in.');
	});
	if(!errors.length)return true;
	alert(errors.join("\n"));
	return false;
}
function toggle_remove_associated_files() {
	$('#new_line').remove();
	var addString = '<div id="remove_wrapper">';
	addString += 'Remove associated files? ';
	addString += '<input type="checkbox" id="remove_associated_files"';
	addString += ' name="remove_associated_files" /></div>';
	var newLineString = '<div id="new_line"></div>';
	switch ($('#clear_database').attr('checked')) {
		case true: // {
			$(addString).insertAfter('#clear_database');
			$('#remove_associated_files').attr('checked', true); 
		break; // }
		case false : // {
			$('#remove_wrapper').remove();
			$(newLineString).insertAfter('#clear_database');
		break; // }
	}
}
function show_hide_cat_options(catsToChange) {
	switch($('#clear_categories_database').attr('checked')) {
		case true: // {
			for (var i=0; i<catsToChange.length; i++) {
				$('#cat_options option[value='+catsToChange[i].id+']').remove();
			}
		break; // }
		case false: // {
			for (var i=0; i<catsToChange.length; i++) {
				$('#cat_options')
					.append(
						'<option value='+catsToChange[i].id
						+'>'+catsToChange[i].name+'</option>'
					);
			}
		break; // }
	}
}
function update_data_fields(data) {
	if (data.message) {
		return alert(data.message);
	}
	html = '<table id="data-fields-table">'; 
	for (i=0; i<data.type.length; ++i) {
		var value='';
		if (data.oldType != null) {
			for (j=0; j<data.oldType.length; ++j) {	
				if (data.oldType[j].n==data.type[i].n) {
					if (data.oldType[j].t=='checkbox'&&data.type[i].t!='checkbox') {
						if (data.product[j]!=null) {
							value='Yes';
						}
						else {
							value='No';
						}
					}
					else if (data.product[j]!=null) {
						value=data.product[j].v;
					}
				}
			}
		}
		html+= '<tr><th>'+htmlspecialchars(data.type[i].n)+'</th><td>';
		var name = 'data_fields['+htmlspecialchars(data.type[i].n)+']';
		switch(data.type[i].t) {
			case 'checkbox': // {
				html+= '<input name='+name+'" ';
				html+= 'type="checkbox"';
				if (data.type[i].r) {
					html+= ' class="required"';
				}
				if (value!='') {
					html+= ' checked="checked"';
				}
				html+= ' />';
			break; // }
			case 'date': // {
				html+= '<input name="'+name+'" ';
				html+= 'class="date-human';
				if (data.type[i].r) {
					html+= ' required';
				}
				html+= '" value="'+value+'"';
				html+= ' />';
			break; // }
			case 'textarea': // {
				html+= '<textarea name="'+name+'" id="'+name+'" '
					+ 'style="display:none">';
				html+= '</textarea>';
				html+= '<div name="textfor'+name+'" class="ckeditor">';
				html+= value;
				html+= '</div>';
			break; // }
			case 'selectbox': // {
				var opts = data.type[i].e.split("\n");
				html += '<select name="'+name+'">';
				for (j=0; j<opts.length; j++) {
					html+= '<option';
					if (value==opts[j]) {
						html+= ' selected="selected"';
					}
					html+= '>'+htmlspecialchars(opts[j])+'</option>';
				}
				html+= '</select>';
			break; // }
			default: // { An inputbox
				html+= '<input type="text" ';
				html+= 'name="'+name+'" ';
				html+= 'type="text"';
				if (data.type[i].r) {
					html+= ' class="required"';
				}
				if (value!='') {
					html+= ' value="'+value+'"';
				}
				html+= ' />';
			break; // }
		}
		html+= '</td></tr>';
	}
	html+= '</table>';
	$('#data-fields').append(html);
	var editors = document.getElementsByTagName('div');
	for (i=0; i<editors.length; ++i) {
		if (editors[i].className=='ckeditor') {
			var editor 
				= CKEDITOR.replace(
					editors[i], 
					CKEditor_config
				);
			editor.name = editors[i].getAttribute('name');
			CKEDITOR.add(editor);
		}
	}
	if (data.isForSale==1) {
		$('.products-online-store').show();
	}
	else {
		$('.products-online-store').hide();
	}
}
function products_getData () {
	var elements = document.getElementsByTagName('div');
	for (i=0; i<elements.length; ++i) {
		if (elements[i].className=='ckeditor') { // It's a CKEDITOR
			var name = elements[i].getAttribute('name');
			var textAreaName = name.replace('textfor', '');
			var data = CKEDITOR.instances[name].getData();
			var textAreas = document.getElementsByName(textAreaName);
			for (j=0; j<textAreas.length; ++j) {
				$(textAreas[j]).val(data);
			}
		}
	}
}
$(function(){
	$('#product-images-wrapper a.mark-as-default').bind('click',function(){
		var $this=$(this);
		var id=$this[0].id.replace('products-dfbtn-','');
		$.get('/ww.plugins/products/admin/set-default-image.php?product_id='+product_id+'&id='+id,function(ret){
			$('div.default').removeClass('default');
			$this.closest('div').addClass('default');
		});
	});
	$('#product-images-wrapper a.delete').bind('click',function(){
		var $this=$(this);
		var id=$this[0].id.replace('products-dbtn-','');
		if(!$('#products-dchk-'+id+':checked').length){
			alert('you must tick the box before deleting');
			return;
		}
		$.get('/j/kfm/rpc.php?action=delete_file&id='+id,function(ret){
			$this.closest('div').remove();
		});
	});
	$('#product-images-wrapper a.caption').click(function() {
		var $this=$(this);
		var id=$this[0].id.replace('products-cbtn-','');
		var caption=$('#products-img-'+id).attr('title');
		var title='';
		if (caption==null || caption=='') {
			title='Add Caption';
		}
		else {
			title='Edit Caption';
		}
		var $html=$('<div id="product-caption-dialog" title="'+title+'">'
			+'Enter the new caption<br />'
			+'<textarea id="product-caption">'+htmlspecialchars(caption)+'</textarea>'
		).dialog({
			buttons:{
				'Edit': function () {
					var newCaption = $('#product-caption').val();
					$.post('/j/kfm/rpc.php',
						{
							"action":'change_caption',
							"id":id,
							"caption":newCaption
						},
						function(){
							$('#products-img-'+id).attr('title', newCaption);
							$html.remove();
						},
						"json"
					);
				},
				'Cancel': function () {
					$html.remove();
				}
			},
			close:function(){
				$html.remove();
			},
			modal:true
		});
	});
	$("#tabs").tabs();
	$('#products-form').submit(products_form_validate);
	$('a.delete-product-page').click(function(){
		if (!confirm('are you sure you want to delete the product page?')) {
			return;
		}
		var pid=$(this).attr('pid');
		$.post('/a/p=products/f=adminPageDelete/pid='+pid, function() {
			$('#product_table_link_holder').empty();
		});
	});
	$('textarea.selectbox-userdefined').each(function() {
		var $textarea=$(this);
		$textarea.css('display','none');
		var $table=$(
			'<table><thead><tr><th>Option Name</th><th>£$€</th></tr></thead>'
			+'<tbody/></table>'
		).insertAfter($textarea);
		var rows=$textarea.val().split("\n");
		function addRow(var1, var2) {
			var $row=$('<tr><td><input/></td><td><input class="number"/></td></tr>')
				.appendTo($table);
			$row.find('td:first-child input').val(var1);
			$row.find('td:last-child input').val(var2);
			$row.find('input').change(checkRows);
		}
		function checkRows() {
			var $inputs=$table.find('input');
			var emptyrow=0;
			var text='';
			for (var i=0;i<$inputs.length;i+=2) {
				var $inp1=$($inputs[i]), $inp2=$($inputs[i+1]);
				if ($inp1.val()=='') {
					emptyrow=1;
					continue;
				}
				text+=$inp1.val()+'|'+$inp2.val()+"\n";
			}
			$textarea.val(text);
			if (!emptyrow) {
				addRow('', 0);
			}
		}
		for (var i=0;i<rows.length;++i) {
			var row=rows[i];
			if (row=='') {
				continue;
			}
			var bits=row.split('|');
			addRow(bits[0], bits[1]||0);
		}
	});
});
