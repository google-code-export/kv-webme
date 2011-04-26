<?php
$html='';
$widget_type=isset($vars->widget_type) && $vars->widget_type
	?$vars->widget_type
	:'Pie-Chart';
$diameter=isset($vars->diameter) && $vars->diameter?$vars->diameter:280;
$parent_cat=isset($vars->parent_cat)?((int)$vars->parent_cat):0;
$cats=dbAll(
	'select id,name,associated_colour as col from products_categories '
	.'where parent_id='.$parent_cat.' and enabled order by name'
);

switch ($widget_type) {
	case 'List Categories': // {
		$html='<ul>';
		foreach ($cats as $c) {
			$html.='<li><a href="/_r?type=products&product_cid='.$c['id'].'">'.$c['name'].'</a></li>';
		}
		$html.='</ul>';
	break; // }
	default: // { Pie Chart
		$id='products_categories_'.md5(rand());
		$html.='<div id="'.$id.'" class="products-widget" style="width:'.$diameter
			.'px;height:'.($diameter+30).'px">loading...</div>'
			.'<script>$(function(){'
			.'products_widget("'.$id.'",'.json_encode($cats).');'
			.'});</script>';
		$html.='<!--[if IE]><script src="/ww.plugins/products/frontend/excanvas.js">'
			.'</script><![endif]-->';
		WW_addScript('/ww.plugins/products/frontend/jquery.canvas.js');
		WW_addScript('/ww.plugins/products/frontend/widget.js');
	// }
}