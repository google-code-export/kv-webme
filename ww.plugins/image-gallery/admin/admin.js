function image_gallery_add_price(){
	ig_price_count++;
	$('<input class="medium" name="page_vars[image_gallery_pricedescs_'+ig_price_count+']" value="description" /><input class="ig_price small" name="page_vars[image_gallery_prices_'+ig_price_count+']" value="0" /><br />')
		.insertBefore('#ig_prices_more');
}
$('.image-gallery-delete-link').bind('click',function(){
	var $this=$(this);
	var id=$this[0].id.replace('image-gallery-dbtn-','');
	if(!$('#image-gallery-dchk-'+id+':checked').length){
		alert('you must tick the box before deleting');
		return;
	}
	$.get('/j/kfm/rpc.php?action=delete_file&id='+id,function(ret){
		$this.closest('div').remove();
	});
});
$('.image-gallery-caption-link').click(function() {
	var $this=$(this);
	var id=$this[0].id.replace('image-gallery-caption-link-', '');
	var caption=$this.attr('caption');
	var title='';
	if (caption==null || caption=='') {
		title='Add Caption';
	}
	else {
		title='Edit Caption';
	}
	var html='<div id="image-gallery-caption-dialog" title="'+title+'">';
	html+='Enter the new caption<br />';
	html+='<input id="image-gallery-caption" value="'+caption+'" />';
	$(html).dialog(
		{
			buttons:{
				'Edit': function () {
					var newCaption = $('#image-gallery-caption').val();
					$.post(
						'/j/kfm/rpc.php',
						{
							"action":'change_caption',
							"id":id,
							"caption":newCaption
						},
						update_image,
						"json"
					);
				},
				'Cancel': function () {
					$(this).remove();
				}
			}
		}
	);
});
$('#image_gallery_directory').remoteselectoptions({
	url:'/ww.plugins/image-gallery/admin/get-directories.php',
	always_retrieve:true
});
function update_image(data) {
	$('#image-gallery-caption-dialog').remove();
	var caption = data.caption;
	var id = data.id;
	var captionLink = $('#image-gallery-caption-link-'+id);
	$(captionLink).attr('caption', caption);
	if (caption==null || caption=='') {
		$(captionLink).text('Add Caption');
	}
	else {
		$(captionLink).text('Edit Caption');
	}
	$('#image-gallery-image'+id).attr('title', caption);
}
function imagegallery_showoverlay(){
	var $overlay = $('<div class="ui-widget-overlay"></div>').appendTo('body');
	setOverlayDimensionsToCurrentDocumentDimensions(); //remember to call this when the document dimensions change
	$(window).resize(function(){
		setOverlayDimensionsToCurrentDocumentDimensions();
	});
	function setOverlayDimensionsToCurrentDocumentDimensions() {
		$('.ui-widget-overlay').width($(document).width());
		$('.ui-widget-overlay').height($(document).height());
	}
	$('your images are uploading. please wait').dialog();
}
$(function(){
	var previous;
	$('#gallery-template-type').focus(function(){
		previous=this.value;
	}).change(function(){
		var content;
		if(previous=='custom')
			$(this).data('custom',{'value':CKEDITOR.instances['page_vars[gallery-template]'].getData()});
		var tpl=$(this).val();
		if(tpl=='custom'){
			content=($(this).data('custom'))?
				$(this).data('custom').value:
				'';
			CKEDITOR.instances['page_vars[gallery-template]'].setData(content);
		}
		else{
			$.get(
				'/ww.plugins/image-gallery/admin/types/'+tpl+'.tpl',
				function(html){
					CKEDITOR.instances['page_vars[gallery-template]'].setData(html);
				}
			);
		}
	});
});
