function sms_edit(id){
	if(id){
		$.post('/ww.plugins/sms/admin/addressbooks-get.php',{
			"id":id
		},sms_edit_showform,'json');
	}
	else sms_edit_showform();
}
function sms_edit_showform(res){
	if(!res){
		res={
			"id":0,
			"name":'[insert addressbook name]',
			"subscribers":[]
		};
	}
	$('#sms_wrapper').html('<table>'
		+'<tr><th>Name</th><td><input id="sms_name" /></td></tr>'
		+'<tr id="sms_subscribers"><th>Subscribers</th><td><input type="hidden" /><span></span> <a href="javascript:sms_edit_subscribers()">edit</a></td></tr>'
		+'<tr><th colspan="2"><button>Save</button></th></tr>'
		+'</table>'
	);
	$('#sms_name').val(res.name);
	$('#sms_subscribers input').val(res.subscribers);
	$('#sms_subscribers span').text(res.subscribers.length+' subscribers.');
	$('#sms_wrapper button').click(sms_save);
	window.sms_currently_editing=res;
}
function sms_edit_subscribers(){
	var res=window.sms_currently_editing;
	$.post('/ww.plugins/sms/admin/addressbooks-get-subscribers.php',{
		"id":res.id
	},sms_show_subscribers,'json');
}
function sms_show_subscribers(res){
	var html='<div id="sms-show-subscribers">';
	for(var i=0;i<res.length;++i){
		var s=res[i];
		html+='<input type="checkbox" id="subscriber-'+s.id+'"'
			+(s.c?' checked="checked"':'')
			+' />'
			+htmlspecialchars(s.name+' ('+s.phone+')')
			+'<br />';
	}
	html+='</div>';
	$(html).dialog({
		"modal":true,
		"buttons":{
			"save":function(){
				var ids=[];
				var $this=$(this);
				$('#sms-show-subscribers input:checked').each(function(){
					ids.push(this.id.replace(/subscriber-/,''));
				});
				$.post('/a/p=sms/f=adminAddressbookSave',{
					"aid":window.sms_currently_editing.id,
					"subscribers":ids.join(',')
				},function(){
					sms_edit(window.sms_currently_editing.id);
					$this.remove();
				},'json');
			}
		}
	});
}
function sms_save(){
	var res=window.sms_currently_editing;
	res.name=$('#sms_name').val();
	res.subscribers=$('#sms_subscribers input').val();
	$.post('/ww.plugins/sms/admin/addressbooks-save.php',{
		"id":res.id,
		"name":res.name,
		"subscribers":res.subscribers
	},function(res){
		if(res.err)alert('error saving addressbook\nplease check your values');
		else document.location="/ww.admin/plugin.php?_plugin=sms&_page=addressbooks";
	},'json');
}
function sms_delete(id){
	if(!confirm('are you sure you want to delete this addressbook?'))return;
	$.post('/ww.plugins/sms/admin/addressbooks-delete.php',{
		"id":id
	},function(res){
		$('#sms_row_'+res.id).fadeOut(400,function(){
			$(this).remove();
		});
	},'json');
}
