<?php

/**
 * ww.admin/siteoptions/themes/personal.php, KV-Webme
 *
 * displays themes from the theme server
 *
 * @author     Conor Mac Aoidh <conormacaoidh@gmail.com>
 * @license    GPL 2.0
 * @version    1.0
 */

$themes = array( );
$theme_dir = USERBASE . 'themes-personal/';

/**
 * scan through theme dirs, gather information
 * on themes
 */
$files = scandir( $theme_dir );
foreach( $files as $file ){

	if( $file == '.' || $file == '..' )
		continue;

	if( is_dir( $theme_dir . $file ) ){

		$theme = array( 'name' => $file );

		/**
		 * get variants
		 */
		if( is_dir( $theme_dir . $file . '/cs' ) ){

			$variants = array( );
			$fs = scandir( $theme_dir . $file . '/cs' );

			foreach( $fs as $f ){

				if( $f == '.' || $f == '..' )
					continue;

		                /**
		                 * get file name and extention
		                 */
		                $fname = explode( '.', $f );
		                $ext = end( $fname );
		                $fname = reset( $fname );

		                /**
		                 * if css files are present, make sure they have
		                 * corresponding png files
		                 */
		                if( $ext == 'css' ){
		                        if( in_array( $fname . '.png', $fs ) ){
						array_push( $variants, $fname );
					}
		                }

			}

			$theme[ 'variants' ] = $variants;

		}

		array_push( $themes, $theme );

	}

}

WW_addScript('/ww.admin/siteoptions/themes/themes.js');

$notification = @$_GET[ 'uploaded' ];
if (@$_REQUEST['msg']) {
	$msg='<em>'.htmlspecialchars($_REQUEST['msg']).'</em>';
}
if( $notification == 'true' )
	$msg = '<em>Theme Uploaded Successfuly!</em>';
elseif( $notification == 'false' )
	$msg = '<em>There was an error uploading the theme. Please do not include any PHP files.</em>';

// { display theme
echo '<div id="preview-dialog" style="display:none">
<iframe src="javascript:;" id="preview-frame"></iframe>
</div>
<h2>Themes</h2>
<div id="tabs">
	<ul>
		<li><a href="#tabs-1">Personal</a></li>
		<li><a href="/ww.admin/siteoptions/themes/download.php">Download</a></li>
		<li><a href="/ww.admin/siteoptions/themes/upload.php">Upload</a></li>
	</ul>
	<div id="tabs-1">
	' . @$msg . '
<table id="themes-table"><tr>';
// { loop through themes, print them
for( $i = 0; $i < count( $themes ); ++$i ){

        if( $i % 3 == 0 )
                echo '</tr><tr>';

	$status = ( $DBVARS[ 'theme' ] == $themes[ $i ][ 'name' ] ) ? ' - Current Theme' : '' ;
	$current = ( $DBVARS[ 'theme' ] == $themes[ $i ][ 'name' ] ) ? ' style="background:#FCFFB2"' : '';

        $class = ( !( ( $i - 1 ) % 3 ) ) ? ' class="middle"' : '';

        echo '<td' . $class . $current . '>';

        echo '<div class="theme-container">
                <form action="/ww.admin/siteoptions.php?page=themes&action=install" method="post">
                <input type="hidden" value="' . $themes[ $i ][ 'name' ] . '" name="theme_name"/>
                <h3>' . $themes[ $i ][ 'name' ] . @$status . '</h3>
                <p><img src="/ww.skins/' . $themes[ $i ][ 'name' ] . '/screenshot.png" width="240px" height="172px"/></p>
                <p>Variant: <select name="theme_variant" class="theme_variant">';

                /**
                 * get all variants
                 */
                foreach( $themes[ $i ][ 'variants' ] as $variant ){
                        $cur = ( $DBVARS[ 'theme' ] == $themes[ $i ][ 'name' ] && $DBVARS['theme_variant'] == $variant ) ?
                                ' selected="selected"' :
                                '';
                        echo '<option screenshot="/ww.skins/' . $themes[ $i ][ 'name' ] . '/cs/' . $variant. '.png" ' . $cur . '>' . $variant . '</option>';
                }

        echo '</select></p>
        <p>
                <input type="submit" class="install-theme" name="install-theme" value="Install" />
                <input type="submit" class="install-theme" onclick="if (!confirm(\'are you sure you want to delete this theme?\')) return false;" name="delete-theme" value="Delete"/> 
                <a class="theme-preview theme-preview-personal" title="' . $themes[ $i ][ 'name' ] . '">Preview</a>
        </p></form></td>';

}
// }
echo '</tr></table><br style="clear:both"/>
</div>
</div>
';
// }
