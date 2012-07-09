$(function() {
	$('#meetings').dataTable({
		'bJQueryUI':true
	});
	$('#meetings').on('click', '.edit', function() {
		var id=$(this).closest('tr').attr('id').replace('meeting-', '');
		$.post('/a/p=meetings/f=adminMeetingGet', {
			'id':id
		}, meeting_edit);
	});
	$('#meetings').on('click', '.delete', function() {
		if (!confirm('Are you sure you want to cancel this meeting?')) {
			return;
		}
		var id=$(this).closest('tr').attr('id').replace('meeting-', '');
		$.post('/a/p=meetings/f=adminMeetingDelete', {
			'id':id
		}, function() {
			document.location=
				"/ww.admin/plugin.php?_plugin=meetings&_page=meetings";
		});
	});
	$('#meetings-create').click(function() {
		meeting_edit({
			'id':0,
			'meeting_time':'',
			'user_id':0,
			'customer_id':0,
			'form_id':0
		});
		return false;
	});
	function meeting_edit(m) {
		// { $dialog
		var html='<table>'
			+'<tr><th>Meeting Time</th><td><input id="meeting-time"'
			+' class="datetime"/></td></tr>'
			+'<tr><th>Who</th><td><select id="meeting-user_id"/></td></tr>'
			+'<tr><th>Is Meeting Who</th><td><select id="meeting-customer_id"/>'
			+'</td></tr>'
			+'<tr><th>Question List</th><td><select id="meeting-form_id"/></td></tr>'
			+'</table>';
		// }
		var $dialog=$(html).dialog({
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'buttons':{
				'Save':function() {
					var meeting_time=$('#meeting-time').val(),
						user_id=+$('#meeting-user_id').val(),
						customer_id=+$('#meeting-customer_id').val(),
						form_id=+$('#meeting-form_id').val();
					if (!meeting_time || user_id<1 || form_id<1 || customer_id<1) {
						return alert('you must fill in the whole form');
					}
					$.post('/a/p=meetings/f=adminMeetingEdit', {
						'id':m.id,
						'meeting_time':meeting_time,
						'user_id':user_id,
						'customer_id':customer_id,
						'form_id':form_id
					}, function() {
						document.location=
							"/ww.admin/plugin.php?_plugin=meetings&_page=meetings";
					});
				}
			}
		});
		$.post('/a/p=meetings/f=adminFormsList', function(ret) {
			var opts='<option value="-1"> -- please choose -- </option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option value="'+ret[i].id+'">'+ret[i].name+'</option>';
			}
			opts+='<option value="0"> -- Add New -- </option>';
			$('#meeting-form_id').html(opts)
				.val(m.form_id||-1)
				.change(function() {
					if ($(this).val()=='0') {
						$dialog.remove();
						form_edit({
							'id':0,
							'name':'Name of Questions Form',
							'fields':[]
						});
					}
				});
		});
		$.post('/a/p=meetings/f=adminCustomersList', function(ret) {
			var opts='<option value="-1"> -- please choose -- </option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option value="'+ret[i].id+'">'+ret[i].name+'</option>';
			}
			opts+='<option value="0"> -- Add New -- </option>';
			$('#meeting-customer_id').html(opts)
				.val(m.customer_id||-1)
				.change(function() {
					if ($(this).val()=='0') {
						var name=prompt("what is the customer's name");
						if (!name) {
							$('#meeting-customer_id').val('-1');
							return;
						}
						$.post('/a/p=meetings/f=adminCustomerCreate', {
							'name':name
						}, function(ret) {
							$('#meeting-customer_id')
								.append('<option value="'+ret.id+'">'+name+'</option>')
								.val(ret.id);
						});
					}
				});
		});
		$.post('/a/p=meetings/f=adminEmployeesList', function(ret) {
			var opts='<option value="-1"> -- please choose -- </option>';
			for (var i=0;i<ret.length;++i) {
				opts+='<option value="'+ret[i].id+'">'+ret[i].name+'</option>';
			}
			opts+='<option value="0"> -- Add New -- </option>';
			$('#meeting-user_id').html(opts)
				.val(m.user_id||-1)
				.change(function() {
					if ($(this).val()=='0') {
						var name=prompt("what is the employee's name");
						if (!name) {
							$('#meeting-user_id').val('-1');
							return;
						}
						$.post('/a/p=meetings/f=adminEmployeeCreate', {
							'name':name
						}, function(ret) {
							$('#meeting-user_id')
								.append('<option value="'+ret.id+'">'+name+'</option>')
								.val(ret.id);
						});
					}
				});
		});
		$('#meeting-time')
			.val(m.meeting_time)
			.blur()
			.datetimepicker({
				dateFormat: 'yy-mm-dd',
				timeFormat: 'hh:mm',
				onClose: function(dateText, inst){
				}
			});
	}
	function form_edit(f) {
		// { $dialog
		var html='<table>'
			+'<tr><th>Name</th><td><input id="dialog-name"/></td></tr>'
			+'<tr><th>Questions</th><td><table id="dialog-questions">'
			+'<thead><tr><th>Question</th><th>Type</th><th></th></tr></thead>'
			+'<tbody/></table></td></tr>'
			+'</table>';
		var $dialog=$(html).dialog({
			'modal':true,
			'close':function() {
				$dialog.remove();
			},
			'width':600,
			'buttons':{
				'Save':function() {
					var fields=[];
					var $rows=$fieldsTable.find('tbody tr');
					$rows.each(function() {
						var $this=$(this);
						var name=$this.find('input').val(),
							type=$this.find('select').val();
						var extras=[];
						switch(type) {
							case 'select':
								extras={
									'values':$this.find('textarea').val()
								};
							break;
						}
						if (name) {
							fields.push({
								'name':name,
								'type':type,
								'extras':extras
							});
						}
					});
					$.post('/a/p=meetings/f=adminFormEdit', {
						'id':f.id,
						'name':$('#dialog-name').val(),
						'fields':fields
					}, function() {
						$dialog.remove();
					});
				}
			}
		});
		// }
		var $fieldsTable=$('#dialog-questions');
		for (var i=0;i<f.fields.length;++i) {
			var r=f.fields[i];
			addRow(r.name, r.type);
		}
		function addRow(name, type, extras) {
			var types={
				'input':'single line of text',
				'textarea':'multiple lines of text',
				'select':'select from a list'
			};
			var thtml='<select>';
			$.each(types, function(k, v) {
				thtml+='<option value="'+k+'">'+v+'</option>';
			});
			thtml+='</select>';
			var $row=$(
				'<tr><th><input/></th><td>'+thtml+'</td><td class="extras"/></tr>')
				.appendTo($fieldsTable);
			$row.find('input').val(name);
			$row.find('select')
				.val(type)
				.change(function() {
					switch ($(this).val()) {
						case 'select':
							$('<textarea/>').appendTo($row.find('.extras')).val(extras);
						break;
						default:
							$row.find('.extras').empty();
						break;
					}
				})
				.change();
		}
		$fieldsTable.on('blur', 'input,select', function() {
			var $rows=$fieldsTable.find('tbody tr');
			$rows.each(function() {
				var $this=$(this);
				if (!$this.find('input').val()) {
					$this.remove();
				}
			});
			addRow('', '');
		});
		$('#dialog-name').val(f.name);
		addRow('', '');
	}
});