<?php
$scripts=array();
$scripts_inline=array();
// { built-in page types
$pagetypes=array(
	array(0, 'normal'),
	array(1, 'redirect'),
	array(9, 'table of contents'),
	array(5, 'search results'),
	array(4, 'page summaries')
);
// }
function addMenuItem(&$arr,$file,$nav){
	if(ereg('>',$nav)){
		return;
		$bits=explode(' > ',$nav);
		if(!isset($arr[$bits[0]]))$arr[$bits[0]]=array();
		addMenuItem($arr[$bits[0]],$file,str_replace($bits[0].' > ','',$nav));
	}else{
		$arr[$nav]=$file;
	}
}
function admin_menu($list,$this=''){
	$arr=array();
	foreach($list as $key=>$val){
		if($val==$this)$arr[]='<a href="'.$val.'" class="thispage">'.$key.'</a>';
		else $arr[]='<a href="'.$val.'">'.$key.'</a>';
	}
	return '<div class="left-menu">'.join('',$arr).'</div>';
}
function admin_verifypage($validlist,$default,$val){
	foreach($validlist as $v)if($v==$val)return $val;
	return $default;
}
function html_fixImageResizes($src){
	// checks for image resizes done with HTML parameters or inline CSS
	//   and redirects those images to pre-resized versions held elsewhere

	preg_match_all('/<img [^>]*>/im',$src,$matches);
	if(!count($matches))return $src;
	foreach($matches[0] as $match){
		$width=0;
		$height=0;
		if(preg_match('#width="[0-9]*"#i',$match) && preg_match('/height="[0-9]*"/i',$match)){
			$width=preg_replace('#.*width="([0-9]*)".*#i','\1',$match);
			$height=preg_replace('#.*height="([0-9]*)".*#i','\1',$match);
		}
		else if(preg_match('/style="[^"]*width: *[0-9]*px/i',$match) && preg_match('/style="[^"]*height: *[0-9]*px/i',$match)){
			$width= preg_replace('#.*style="([^"]*[^-]width|width): *([0-9]*)px.*#i','\2',$match);
			$height=preg_replace('#.*style="([^"]*[^-]height|height): *([0-9]*)px.*#i','\2',$match);
		}
		if(!$width || !$height)continue;
		$imgsrc=preg_replace('#.*src="([^"]*)".*#i','\1',$match);
		$dir=str_replace('/','@_@',$imgsrc);

		// get absolute address of img (naive, but will work for most cases)
		if (!preg_match('/^http/i',$imgsrc)) {
			$imgsrc=USERBASE.'/'.$imgsrc;
		}

		if (!file_exists($imgsrc)) {
			continue;
		}
		list($x, $y)=getimagesize($imgsrc);
		if (!$x || !$y || ($x==$width && $y==$height)) {
			continue;
		}

		// create address of resized image and update HTML
		$ext=strtolower(preg_replace('/.*\./', '', $imgsrc));
		$newURL=WORKURL_IMAGERESIZES.$dir.'/'.$width.'x'.$height.($ext=='png'||$ext=='gif'?'.png':'.jpg');
		$newImgHTML=preg_replace('/(.*src=")[^"]*(".*)/i',"$1$newURL$2",$match);
		$src=str_replace($match,$newImgHTML,$src);

		// create cached image
		$imgdir=WORKDIR_IMAGERESIZES.$dir;
		if (!file_exists(WORKDIR_IMAGERESIZES)) {
			mkdir(WORKDIR_IMAGERESIZES);
		}
		if (!file_exists($imgdir)) {
			mkdir($imgdir);
		}
		$imgfile=$imgdir.'/'.$width.'x'.$height.($ext=='png'||$ext=='gif'?'.png':'.jpg');
		if(file_exists($imgfile))continue;
		$str='convert "'.addslashes($imgsrc).'" -geometry '.$width.'x'.$height.' "'.$imgfile.'"';
		exec($str);
	}

	return $src;
}
function html_unfixImageResizes($src){
	// replace resized images with their originals
	$count=preg_match_all('#/f/.files/image_resizes/(@_@[^"]*)(/[^"]*)"#',$src,$matches);
	if(!$count)return $src;
	foreach($matches[1] as $key=>$match){
		$src=str_replace('/f/.files/image_resizes/'.$match.$matches[2][$key],str_replace('@_@','/',$match),$src);
	}
	return $src;
}
function wInput($name,$type='text',$value='',$class=''){
	switch($type){
		case 'checkbox': {
			$tmp=($value)?' checked="checked"':'';
			return '<input name="'.$name.'" type="checkbox"'.$tmp.' />';
		}
		case 'select': {
			$ret='';
			foreach($value as $key=>$val){
				$selected=($key==$class)?' selected="selected"':'';
				$ret.='<option value="'.$key.'"'.$selected.'>'.htmlspecialchars($val).'</option>';
			}
			return '<select name="'.$name.'">'.$ret.'</select>';
		}
		case 'textarea': {
			$tmp=($class!='')?' class="'.$class.'"':'';
			return '<textarea name="'.$name.'"'.$tmp.'>'.$value.'</textarea>';
		}
		default: {
			$tmp=($value!='')?' value="'.$value.'"':'';
			return '<input name="'.$name.'" id="'.$name.'" type="'.$type.'"'.$tmp.' class="'.$class.'" />';
		}
	}
}
function wFormRow($title,$input){
	echo '<tr><th>';
	if(is_array($title)){
		echo htmlspecialchars($title[0]);
	}else{
		echo htmlspecialchars($title);
	}
	echo '</th><td>';
	if(is_array($input)){
		for($i=0;$i<4;++$i)if(!isset($input[$i]))$input[$i]=null;
		echo wInput($input[0],$input[1],$input[2],$input[3]);
	}else{
		echo $input;
	}
	echo '</td></tr>';
}
function WW_addCSS($url){
	global $css_urls;
	if (!is_array($css_urls)) {
		$css_urls=array();
	}
	if (in_array($url,$css_urls)) {
		return;
	}
	$css_urls[]=$url;
}
function WW_addScript($url){
	global $scripts;
	if(in_array($url,$scripts))return;
	$scripts[]=$url;
}
function WW_addInlineScript($script){
	global $scripts_inline;
	$script=preg_replace('/\s+/',' ',str_replace(array("\n","\r"),' ',$script));
	if(in_array($script,$scripts_inline))return;
	$scripts_inline[]=$script;
}
function WW_getCSS(){
	global $css_urls;
	if (!is_array($css_urls)) {
		return;
	}
	$url='/css/';
	foreach($css_urls as $s)$url.='|'.$s;
	return '<link rel="stylesheet" href="'.htmlspecialchars($url).'" />';
}
function WW_getScripts() {
	global $scripts,$scripts_inline;
	if(!count($scripts))return '';
	$inline=count($scripts_inline)
		?'<script>'.join('', $scripts_inline).'</script>':'';
	return '<script src="'.join('"></script><script src="', $scripts).'"></script>'
		.$inline;
}
function drawMenu($menuArray){
	$c='';
	foreach($menuArray as $name=>$item){
		if(is_array($item)){
			$c.='<a href="#">'.htmlspecialchars($name).'</a>';
			$c.='<ul>'.drawMenu($item).'</ul>';
		}else{
			$c.='<a href="'.$item.'">'.htmlspecialchars($name).'</a>';
		}
	}
	return $c;
}
function ckeditor($name,$value='',$height=250){
	return '<textarea style="width:100%;height:'.$height.'px" name="'.addslashes($name).'">'.htmlspecialchars($value).'</textarea>'
		."<script>//<![CDATA[\n"
		.'$(function(){window.ckeditor_'.preg_replace('/[^a-zA-Z_]/','',$name)
		.'=CKEDITOR.replace("'
		.str_replace(array('[',']'),array('\[','\]'),addslashes($name))
		.'",{filebrowserBrowseUrl:"/j/kfm/",menu:"WebME",scayt_autoStartup:false});});'
		."//]]></script>";
}
function sanitise_html_essential($original_html) {
	$original_html = str_replace("\n", '{{CARRIAGERETURN}}', $original_html);
	$original_html = str_replace("\r", '{{LINERETURN}}', $original_html);
	do{
		$html = $original_html;
		// { clean old fckeditor stuff
		$html = preg_replace('#<link href="[^"]*editor/css/fck_editorarea.css" rel="stylesheet" type="text/css" />#', '', $html);
		$html = preg_replace('#<style _fcktemp="true" type="text/css">[^<]*</style>#', '', $html);
		$html = preg_replace('#<link _fcktemp="true" href="[^"]*editor/editor/css/fck_internal.css" rel="stylesheet" type="text/css" />#', '', $html);
		$html = preg_replace('#_fcksavedurl="[^"]*"#', '', $html);
		$html = str_replace('class="FCK__ShowTableBorders"', '', $html);
		// }
		// { clean skype crap from page
		$html = str_replace('<span class="skype_pnh_left_span" skypeaction="skype_dropdown">&nbsp;&nbsp;</span>','',$html);
		$html = str_replace('<span class="skype_pnh_dropart_flag_span" skypeaction="skype_dropdown" style="background-position: -1999px 1px ! important;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>','',$html);
		$html = str_replace('<span class="skype_pnh_dropart_span" skypeaction="skype_dropdown" title="Skype actions">&nbsp;&nbsp;&nbsp;</span>','',$html);
		$html = str_replace('<span class="skype_pnh_right_span">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</span>','',$html);
		$html = preg_replace('#<span class="skype_pnh_print_container">([^<]*)</span>#','\1',$html);
		$html = preg_replace('#<span class="skype_pnh_text_span">([^<]*)</span>#','\1',$html);
		$html = preg_replace('#<span class="skype_pnh_mark">[^<]*</span>#','',$html);
		$html = preg_replace('#<span class="skype_pnh_textarea_span">([^<]*)</span>#','\1',$html);
		$html = preg_replace('#<span class="skype_pnh_highlighting_inactive_common" dir="ltr"[^>]*>([^<]*)</span>#','\1',$html);
		$html = preg_replace('#<span class="skype_pnh_container"[^>]*>([^<]*)</span>#','\1',$html);
		$html = preg_replace('#<span class="skype_pnh_text_span">([^<]*)</span>#','\1',$html);
		$html = preg_replace('#<span class="skype_pnh_print_container">([^<]*)</span>#','\1',$html);
		// }
		// { remove empty elements and parameters
		$html = preg_replace('#<style[^>]*>\s*</style>#','',$html);
		$html = preg_replace('#<span>\s*</span>#', '', $html);
		$html = preg_replace('#<meta[^>]*>\s*</meta>#','',$html);
		$html = str_replace(' alt=""','',$html);
		// }
		// { clean up Word crap
		$html = preg_replace('#<link [^>]*href="file[^>]*>#','',$html);
		$html = preg_replace('#<m:[^>]*/>#','',$html);
		$html = preg_replace('#<m:mathPr>\s*</m:mathPr>#','',$html);
		$html = preg_replace('#<xml>.*?</xml>#','',$html);
		$html = preg_replace('#<!--\[if gte mso 10\].*?<!\[endif\]-->#','',$html);
		$html = preg_replace('#<!--\[if gte mso 9\].*?<!\[endif\]-->#','',$html);
		$html = preg_replace('#<!--\[if gte vml 1\]>.*?<!\[endif\]-->#','',$html);
		$html = preg_replace('#<object classid="clsid:38481807-CA0E-42D2-BF39-B33AF135CC4D" id=[^>]*></object>#','',$html);
		$html = preg_replace('#<style>\s[a-z0-9]*.:[^<]*</style>#','',$html);
		$html = preg_replace('#<!--\[if !mso\][^<]*<!\[endif\]-->#','',$html);
		// }
		$html=str_replace('&quot;','"',$html);
		$html=str_replace('&#39;',"'",$html);
		$has_changed=$html!=$original_html;
		$original_html=$html;
	}while($has_changed);
	$html = str_replace('{{CARRIAGERETURN}}', "\n", $html);
	$html = str_replace('{{LINERETURN}}', "\r", $html);
	return $html;
}
function sanitise_html($original_html) {
	/**
		* this function cleans up the crud that gets inserted by programs such as Word or CKeditor, or Skype
		*/
	$original_html = sanitise_html_essential($original_html);
	$original_html = html_fixImageResizes($original_html);
	$original_html = str_replace("\n", '{{CARRIAGERETURN}}', $original_html);
	$original_html = str_replace("\r", '{{LINERETURN}}', $original_html);
	do{
		$html = $original_html;
		// { clean white-space
		$html = str_replace('{{LINERETURN}}{{CARRIAGERETURN}}', "{{CARRIAGERETURN}}", $html);
		$html = str_replace('>{{CARRIAGERETURN}}','>',$html);
		$html = str_replace('{{CARRIAGERETURN}}{{CARRIAGERETURN}}', '{{CARRIAGERETURN}}', $html);
	//	$html = preg_replace('/\s+/',' ',$html);
		$html = preg_replace("/<p>\s*/",'<p>',$html);
		$html = preg_replace("#\s*<br( ?/)?>\s*#",'<br />',$html);
		$html = preg_replace("#\s*<li>\s*#",'<li>',$html);
		$html = str_replace(">\t",'>',$html);
		$html = preg_replace('#<p([^>]*)>\s*\&nbsp;\s*</p>#','<p\1></p>',$html);
		// }
		// { remove empty elements and parameters
		$html = preg_replace('/<!--[^>]*-->/','',$html);
		// }
		// { combine nested elements
		$html = preg_replace('#<span style="([^"]*?);?">(\s*)<span style="([^"]*)">([^<]*|<img[^>]*>)</span>(\s*)</span>#', '\2<span style="\1;\3">\4</span>\5', $html);
		$html = preg_replace('#<a href="([^"]*)">(\s*)<span style="([^"]*)">([^<]*|<img[^>]*>)</span>(\s*)</a>#', '\2<a href="\1" style="\3">\4</a>\5', $html);
		$html = preg_replace('#<strong>(\s*)<span style="([^"]*)">([^<]*)</span>(\s*)</strong>#', '<strong style="\2">\1\3\4</strong>', $html);
		$html = preg_replace('#<b>(\s*)<span style="([^"]*)">([^<]*)</span>(\s*)</b>#', '<b style="\2">\1\3\4</b>', $html);
		$html = preg_replace('#<li>(\s*)<span style="([^"]*)">([^<]*)</span>(\s*)</li>#', '<li style="\2">\1\3\4</li>', $html);
		$html = preg_replace('#<p>(\s*)<span style="([^"]*)">([^<]*)</span>(\s*)</p>#', '<p style="\2">\1\3\4</p>', $html);
		$html = preg_replace('#<span style="([^"]*)">(\s*)<strong>([^<]*)</strong>(\s*)</span>#', '\2<strong style="\1">\3</strong>\4', $html);
		$html = preg_replace('#<span style="([^"]*?);?">(\s*)<strong style="([^"]*)">([^<]*)</strong>(\s*)</span>#', '\2<strong style="\1;\3">\4</strong>\5', $html);
		$html = preg_replace("/<p>\s*(<img[^>]*>)\s*<\/p>/",'\1',$html);
		$html = preg_replace('/<span( style="font-[^:]*:[^"]*")?>\s*(<img[^>]*>)\s*<\/span>/','\2',$html);
		$html = preg_replace("/<strong>\s*(<img[^>]*>)\s*<\/strong>/",'\1',$html);
		// }
		// { remove unnecessary elements
		$html = preg_replace('#<meta [^>]*>(.*?)</meta>#','\1',$html);
		// }
		// { strip repeated CSS inline elements (TODO: make this more efficient...)
		$html=str_replace('font-size: large;font-size: large','font-size: large',$html);
		// }
		// { strip useless CSS
		$sillystuff=' style="([^"]*)(color:[^;"]*|font-size:[^;"]*|font-family:[^;"]*|line-height:[^;"]*);([^"]*)"';
		$html=preg_replace('#\s*<span'.$sillystuff.'>\s*</span>\s*#','<span style="\1\3"></span>',$html);
		$html=str_replace('<span style=""></span>','<span></span>',$html);
		$html=preg_replace('#\s*<p'.$sillystuff.'>\s*</p>\s*#','<p style="\1\3"></p>',$html);
		$html=str_replace('<p style=""></p>','<p></p>',$html);
		// }
		$has_changed=$html!=$original_html;
		$original_html=$html;
	}while($has_changed);
	// { old-style tabs
	if (strpos($html, '%TABPAGE%')) {
		$rand=md5(mt_rand());
		$test=preg_replace('/<p>[^<]*(%TAB[^%]*%)[^<]*<\/p>/', '\1', $html);
		$test=str_replace('%TABEND%', '</div></div><script>$(function(){$("#'.$rand.'").tabs();});</script>', $test);
		$parts=preg_split('/%TAB[^%]*%/', $test);
		$headings=array();
		for ($i=1; $i<count($parts); ++$i) {
			$headings[]=preg_replace('/<[^>]*>/', '', preg_replace('/^[^<]*<h2[^>]*>(.*?)<\/h2>.*/', '\1', $parts[$i]));

			$replacement=($i>1?'</div>':'').'<div id="'.$rand.'-'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $headings[$i-1])).'">';
			$parts[$i]=preg_replace('/^[^<]*<h2[^>]*>(.*?)<\/h2>/', $replacement, $parts[$i]);
		}
		$menu='<div id="'.$rand.'" class="tabs"><ul>';
		foreach ($headings as $h) {
			$menu.='<li><a href="#'.$rand.'-'.strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $h)).'">'.htmlspecialchars($h).'</a></li>';
		}
		$parts[0].=$menu.'</ul>';
		$html=join('', $parts);
	}
	// }
	$html = str_replace('{{CARRIAGERETURN}}', "\n", $html);
	$html = str_replace('{{LINERETURN}}', "\r", $html);
	return $html;
}
function curl( $url ){
        $ch = curl_init( );
        curl_setopt( $ch, CURLOPT_URL, $url );
        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
        $response = curl_exec( $ch );
        curl_close( $ch );
        return $response;
}
function get_template_names( ){

	$themes = array( );
	$dir = USERBASE . 'themes-personal/';

        /**
         * loop through theme dir
         */
        $handler = opendir( $dir );
        while( $file = readdir( $handler ) ){

		if( $file == '.' || $file == '..' )
			continue;

		if( is_dir( $dir . $file ) )
			array_push( $themes, $file );

        }
        closedir( $handler );

	return $themes;
}
function recursive_dependencies_check($plugins){
	$new_plugs=array();
	foreach($plugins as $plug){
		if(!is_dir(SCRIPTBASE.'ww.plugins/'.$plug)
			||!file_exists(SCRIPTBASE.'ww.plugins/'.$plug.'/plugin.php'))
			// plugin doesn't exist
			return $plug;
		global $PLUGINS;
		if(isset($PLUGINS[$plug])) // if installed load from memory
			$plugin=$PLUGINS[$plug];
		else{ // else include plugin file
			// if already included then it must be
			// already on the list
			require_once SCRIPTBASE.'ww.plugins/'.$plug.'/plugin.php';
		}
		if(isset($plugin['dependencies'])){
			$dependencies=(strpos($plugin['dependencies'],',')===false)?
				array($plugin['dependencies']):
				explode(',',$plugin['dependencies']);
			foreach($dependencies as $dependency){
				if(!in_array($dependency,$plugins)
					&&!in_array($dependency,$new_plugs)){
					array_push($new_plugs,$dependency);
				}
			}
		}
		array_push($new_plugs,$plug);
		$plugin=array();
	}
	$diff=array_diff($new_plugs,$plugins);
	$new_plugs=array_merge($plugins,$new_plugs);
	if(is_array($diff)&&count($diff)!=0){
		$check=recursive_dependencies_check($diff);
		if(!is_array($check))
			return $check;
		$new_plugs=array_merge($new_plugs,$check);
	}
	return array_unique($new_plugs);
}
