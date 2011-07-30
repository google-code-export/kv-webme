<?php
// { plugin configuration
$plugin=array(
	'name'=>'Panels',
	'description'=>'Allows content sections to be displayed throughout the site.',
	'admin'=>array(
		'menu'=>array(
			'Misc>Panels'=>'index'
		)
	),
	'frontend'=>array(
		'template_functions'=>array(
			'PANEL'=>array(
				'function' => 'panels_show'
			)
		)
	),
	'version'=>5
);
// }

function panels_show($vars) {
	$name=isset($vars['name'])?$vars['name']:'';
	// { load panel data
	$p=Core_cacheLoad('panels',md5($name));
	if($p===false){
		$p=dbRow('select id,visibility,hidden,disabled,body from panels where name="'.addslashes($name).'" limit 1');
		if(!is_array($p)){
			dbQuery("insert into panels (name,body) values('".addslashes($name)."','{\"widgets\":[]}')");
			return '';
		}
		Core_cacheSave('panels',md5($name),$p);
	}
	// }
	// { is the panel visible?
	if ($p['disabled']) { // if the panel is disabled, it's not visible anywhere
		return '';
	}
	// { is the panel explicitly only to be shown on certain pages?
	if ($p['visibility'] && $p['visibility']!='[]') {
		$visibility=json_decode($p['visibility']);
		if(!in_array($GLOBALS['PAGEDATA']->id, $visibility)) {
			return '';
		}
	}
	// }
	// { is the panel explicitly not allowed on certain pages?
	if ($p['hidden'] && $p['hidden']!='[]') {
		$hidden=json_decode($p['hidden']);
		if(in_array($GLOBALS['PAGEDATA']->id, $hidden)) {
			return '';
		}
	}
	// }
	// }
	// { get the panel content
	$widgets=json_decode($p['body']);
	if (!count($widgets->widgets)) {
		return '';
	}
	// }
	// { show the panel content
	$h='';
	global $PLUGINS;
	foreach($widgets->widgets as $widget){
		if (isset($widget->disabled) && $widget->disabled) {
			continue;
		}
		if (isset($widget->visibility) && count($widget->visibility)
			&& !in_array($GLOBALS['PAGEDATA']->id, $widget->visibility)
		) {
			continue;
		}
		if (isset($widget->hidden) && count($widget->hidden)
			&& in_array($GLOBALS['PAGEDATA']->id, $widget->hidden)
		) {
			continue;
		}
		$h.='<div class="panel-widget panel-widget-'.$widget->type.'">';
		if (isset($widget->header_visibility) && $widget->header_visibility) {
			$h.='<h2 class="panel-widget-header '.preg_replace('/[^a-z0-9A-Z\-]/','',$widget->name).'">'.htmlspecialchars($widget->name).'</h2>';
		}
		if (isset($PLUGINS[$widget->type])) {
			if (isset($PLUGINS[$widget->type]['frontend']['widget'])) {
				$h.=$PLUGINS[$widget->type]['frontend']['widget']($widget, $p['id']);
			}
			else {
				$h.='<em>plugin "'.htmlspecialchars($widget->type).'" does not have a widget interface.</em>';
			}
		}
		else {
			$h.='<em>missing plugin "'.htmlspecialchars($widget->type).'".</em>';
		}
		$h.='</div>';
	}
	// }
	if($h=='')return '';
	$name=preg_replace('/[^a-z0-9\-]/','-',$name);
	return '<div class="panel panel-'.$name.'">'.$h.'</div>';
}
