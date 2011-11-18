<?php
if (!Core_isAdmin()) {
	exit;
}

function Products_showDataField($df, $def) {
	if ($def['t']=='selected-image') {
		return;
	}
	echo '<tr><th>'.htmlspecialchars($def['n']).'</th><td>';
	switch ($def['t']) {
		case 'checkbox': // {
			echo '<input name="data_fields['.htmlspecialchars($def['n']).']" '
				.'type="checkbox"';
			if ($def['r']) {
				echo ' class="required"';
			}
			if ($df['v']) {
				echo ' checked="checked"';
			}
			echo ' />';
		break; // }
		case 'date': // {
			if (@$def['u']) {
				echo 'this will be entered by the user';
			}
			else {
				echo '<input class="date-human';
				if ($def['r']) {
					echo ' required';
				}
				echo '" name="data_fields['.htmlspecialchars($def['n']).']" value="'
					.htmlspecialchars($df['v']).'" />';
			}
		break; // }
		case 'selectbox': // {
			if (@$def['u']) {
				if ($df['v']=='') {
					$df['v']=$def['e'];
				}
				echo '<textarea class="selectbox-userdefined" '
					.'name="data_fields['.htmlspecialchars($def['n']).']">'
					.htmlspecialchars($df['v'])
					.'</textarea>';
			}
			else {
				$opts=explode("\n", $def['e']);
				echo '<select name="data_fields['.htmlspecialchars($def['n']).']">';
				foreach ($opts as $opt) {
					echo '<option';
					if ($opt==$df['v']) {
						echo ' selected="selected"';
					}
					echo '>'.htmlspecialchars($opt).'</option>';
				}
				echo '</select>';
			}
		break; // }
		case 'textarea': // {
			echo ckeditor('data_fields['.htmlspecialchars($def['n']).']', $df['v']);
		break; // }
		default: // { inputbox
			echo '<input name="data_fields['.htmlspecialchars($def['n']).']"';
			if ($def['r'] && !(@$def['u'])) {
				echo ' class="required"';
			}
			echo ' value="'.htmlspecialchars($df['v']).'" />';
			// }
	}
	echo '</td></tr>';
}
// { set up initial variables
if (isset($_REQUEST['id']) && is_numeric($_REQUEST['id'])) {
	$id=(int)$_REQUEST['id'];
}
else {
	$id=0;
}
$relations=dbAll(
	'select * from products_relation_types order by name'
);
// }
require_once $_SERVER['DOCUMENT_ROOT'].'/j/kfm/includes/directories.php';
if (isset($_REQUEST['action']) && $_REQUEST['action']='save') {
	Core_cacheClear('products');
	Core_cacheClear('pages');
	$errors=array();
	if (!isset($_REQUEST['name']) || $_REQUEST['name']=='') {
		$errors[]='You must fill in the <strong>Name</strong>.';
	}
	if (count($errors)) {
		echo '<em>'.join('<br />', $errors).'</em>';
	}
	else {
		// { Recreate the directory because for some reason it was looking
		//   in the old directory for the image files
		if (!is_dir(USERBASE.'f'.$_REQUEST['images_directory'])) {    
			if (!is_dir(USERBASE.'f/products/product-images')) {
				if (!is_dir(USERBASE.'f/products')) {
					echo 'Creating products directory ';
					$parent_id = kfm_api_getDirectoryId('f');
					_createDirectory($parent_id, 'products');
				}
				echo 'Creating image directory ';
				$parent_id = kfm_api_getDirectoryId('products');
				_createDirectory($parent_id, 'product-images');
			}
			$pos = strrpos($_REQUEST['images_directory'], '/');
			if ($pos===false) {
				$dname.= $_REQUEST['images_directory'];
			}
			else {
				$dname = substr($_REQUEST['images_directory'], $pos+1);
			}
			if (strlen($dname)==0) {
				$dname = rand().microtime();
			}
			$parent_id = kfm_api_getDirectoryId('products/product-images');
			$parent = kfmDirectory::getInstance($parent_id);
			$parent->createSubdir($dname);
		}
		// }
		// { save main data and data fields
		$sql='set name="'.addslashes($_REQUEST['name']).'"'
			.',user_id='.((int)@$_REQUEST['user_id'])
			.',stock_number="'.addslashes($_REQUEST['stock_number']).'"'
			.',activates_on="'.addslashes($_REQUEST['activates_on']).'"'
			.',expires_on="'.addslashes($_REQUEST['expires_on']).'"'
			.',product_type_id='.((int)$_REQUEST['product_type_id'])
			.',default_category='.((int)$_REQUEST['products_default_category'])
			.',enabled='.(int)$_REQUEST['enabled']
			.',images_directory="'.addslashes($_REQUEST['images_directory']).'"';
		// { add data fields to SQL
		$dfs=array();
		if (!isset($_REQUEST['data_fields'])) {
			$_REQUEST['data_fields']=array();
		}
		foreach ($_REQUEST['data_fields'] as $n=>$v) {
			$dfs[]=array(
				'n'=>$n,
				'v'=>$v
			);
		}
		$sql.=',data_fields="'.addslashes(json_encode($dfs)).'"';
		// }
		// { add online store data to SQL, if it exists
		if (isset($_REQUEST['online-store-fields'])) {
			$online_store_data = array();
			foreach ($_REQUEST['online-store-fields'] as $name=>$value) {
				$online_store_data[$name] = $value;
			}
			$online_store_data = json_encode($online_store_data);
			$sql.=',online_store_fields="'.addslashes($online_store_data).'"';
		}
		// }
		if ($id) {
			dbQuery("update products $sql where id=$id");
		}
		else {
			dbQuery("insert into products $sql,date_created=now()");
			$id=dbLastInsertId();
		}
		// }
		// { save categories
		dbQuery('delete from products_categories_products where product_id='.$id);
		if (!isset($_REQUEST['product_categories'])) {
			$_REQUEST['product_categories']=array('1'=>'on');
		}
		foreach ($_REQUEST['product_categories'] as $key=>$val) {
			dbQuery(
				'insert into products_categories_products set product_id='
				.$id.',category_id='.$key
			);
		}
		// }
		// { save product relations
		$rls=array();
		foreach ($relations as $r) {
			$rls[$r['id']]=$r;
			if ($r['one_way']) {
				dbQuery(
					'delete from products_relations where from_id='.$id
					.' and relation_id='.$r['id']
				);
			}
			else {
				dbQuery(
					'delete from products_relations where (from_id='.$id
					.' or to_id='.$id.') and relation_id='.$r['id']
				);
			}
		}
		if (isset($_REQUEST['product-relations-type'])) {
			foreach ($_REQUEST['product-relations-type'] as $k=>$v) {
				if ($v && $_REQUEST['products-relations-product'][$k]) {
					$rid=(int)$v;
					$pid=(int)$_REQUEST['products-relations-product'][$k];
					if ($pid==$id) { // don't relate a product to itself
						continue;
					}
					dbQuery(
						'delete from products_relations where from_id='.$id
						.' and to_id='.$pid.' and relation_id='.$rid
					);
					dbQuery(
						'insert into products_relations set from_id='.$id
						.',to_id='.$pid.',relation_id='.$rid
					);
					if (!$rls[$rid]['one_way']) {
						dbQuery(
							'delete from products_relations where from_id='.$pid
							.' and to_id='.$id.' and relation_id='.$rid
						);
						dbQuery(
							'insert into products_relations set from_id='.$pid
							.',to_id='.$id.',relation_id='.$rid
						);
					}
				}
			}
		}
		// }
		echo '<em>Product saved</em>';
		unset($DBVARS['cron-next']);
		Core_configRewrite();
	}
}

if ($id) {
	$pdata=dbRow("select * from products where id=$id");
	if (!$pdata) {
		echo '<em>No product with that ID exists.</em>';
		return;
	}
}
else {
	$pdata=array(
		'id'=>0,
		'name'=>'',
		'product_type_id'=>0,
		'image_default'=>0,
		'enabled'=>1,
		'date_created'=>date('Y-m-d'),
		'data_fields'=>'{}',
		'images_directory'=>'',
		'online_store_fields'=>'{}'
	);
}
echo '<a href="plugin.php?_plugin=products&amp;_page=products-edit">Add a P'
	.'roduct</a>'
	.' <a href="plugin.php?_plugin=products&amp;_page=import">Import Products'
	.'</a>';
echo '<form id="products-form" action="'.$_url.'&amp;id='.$id.'" '
	.'method="post" onsubmit="products_getData();">';
echo '<input type="hidden" name="action" value="save" />';
echo '<div id="tabs"><ul>'
	.'<li><a href="#main-details">Main Details</a></li>'
	.'<li><a href="#data-fields">Data Fields</a></li>';
if (isset($PLUGINS['online-store'])) {
	$addOnlineStoreFields = $id
		?dbOne(
			'select is_for_sale from products_types where id ='
			.$pdata['product_type_id'],
			'is_for_sale'
		)
		:1;
	echo '<li class="products-online-store"';
	if (!$addOnlineStoreFields) {
		echo ' style="display:none";';
	}
	echo '><a href="#online-store-fields">Online Store</a></li>';
}
echo '<li><a href="#categories">Categories</a></li>';
if (count($relations)) {
	echo '<li><a href="#relations">Related Items</a></li>';
}
echo '</ul>';
// }
// { main details
echo '<div id="main-details"><table>';
// { name, type, manage images
echo '<tr>';
// { name
echo '<th><div class="help products/name"></div>Name</th><td>';
echo '<input class="not-empty" name="name" value="'
	.htmlspecialchars($pdata['name']).'" /></td>';
// }
// { type
echo '<th><div class="help products/type"></div>Type</th><td>';
$ptypes=dbAll('select id,name from products_types order by name');
if ($ptypes===false) {
	echo '<em>No product types created yet. '
		.'Please <a href="plugin.php?_plugin=products&amp;_page=types-edit">'
		.'create one</a> before you go any further!</em>';
}
else {
	if (!$pdata['product_type_id']) {
		$pdata['product_type_id']=$ptypes[0]['id'];
	}
	echo '<select id="product_type_id" name="product_type_id" 
		product="'.$pdata['id'].'">';
	foreach ($ptypes as $ptype) {
		echo '<option value="'.$ptype['id'].'"';
		if ($ptype['id']==$pdata['product_type_id']) {
			echo ' selected="selected"';
		}
		echo '>'.htmlspecialchars($ptype['name']).'</option>';
	}
	echo '</select>';
}
echo '</td>';
// }
// { enable/disable dates
// { enable date
if (@!$pdata['activates_on']) {
	@$pdata['activates_on']=date('Y-m-d').' 00:00:00';
}
echo '<td>Enable Date<br /><input class="datetime" name="activates_on" '
	.'value="'.$pdata['activates_on'].'"/></td>';
// }
// { disable date
if (@!$pdata['expires_on']) {
	@$pdata['expires_on']='2100-01-01 00:00:00';
}
echo '<td>Disable Date<br /><input class="datetime" name="expires_on" '
	.'value="'.$pdata['expires_on'].'"/></td>';
// }
// }
echo '</tr>';
// }
echo '<tr>';
// { stock_number
echo '<th><div class="help products/stock-number"></div>Stock Number</th><td>';
echo '<input class="not-empty" name="stock_number" value="'
	.htmlspecialchars(@$pdata['stock_number']).'" /></td>';
// }
// { enabled
echo '<th><div class="help products/enabled"></div>Enabled</th>'
	.'<td><select name="enabled">'
	.'<option value="1">Yes</option>'
	.'<option value="0"';
if (!$pdata['enabled']) {
	echo ' selected="selected"';
}
echo '>No</option></select></td>';
// }
// { page link
if ($id) {
	echo '<td><strong>Page:</strong> <span id="product_table_link_holder">';
	$pageid = dbOne(
		'select page_id 
		from page_vars 
		where name=\'products_product_to_show\' and value ='.$id,
		'page_id'
	);
	if (!$pageid) {
		echo '<a href="javascript:;" id="page_create_link" 
			onClick=
				"createPopup(
					\''.htmlspecialchars($pdata['name']).'\','.
					$id.','.
					'3'.
				');"'.
			'>';
		echo 'click to create</a>';
	}
	else {
		$dir= dirname(__FILE__);
		require_once $dir.'/../frontend/show.php';
		$page= Page::getInstance($pageid);
		$url= $page->getRelativeUrl();
		echo '<a href="'.$url.'" target="_blank" id="view_this_product">'
			.htmlspecialchars($url).'</a> '
			.'[<a title="delete the product\'s page" href="javascript:;" pid="'
			.$id.'" class="delete-product-page">x</a>]';
	}
	echo '</span></td>';
}
else {
	echo '<td>&nbsp;</td>';
}
// }
// { owner
$user_id=(int)$pdata['user_id'];
if (!$user_id) {
	$user_id=(int)$_SESSION['userdata']['id'];
}
$user_name=dbOne(
	'select name from user_accounts where id='.$user_id,
	'name'
);
echo '<td><strong>Owner:</strong> <select name="user_id"><option value="'
	.$user_id.'">'.$user_name.'</option></select></td>';
// }
echo '</tr>';
// { images
if (!isset($pdata['images_directory']) 
	|| !$pdata['images_directory'] 
	|| $pdata['images_directory']=='/'
	|| !is_dir(USERBASE.'f/'.$pdata['images_directory'])
) {
	if (!is_dir(USERBASE.'f/products/product-images')) {
		mkdir(USERBASE.'f/products/product-images', 0777, true);
	}
	$pdata['images_directory']='/products/product-images/'
		.md5(rand().microtime());
	mkdir(USERBASE.'f'.$pdata['images_directory']);
}
if (!is_dir(USERBASE.'f'.$pdata['images_directory'])) {    
	$parent_id = kfm_api_getDirectoryId('products/product-images');
	$pos = strrpos($pdata['images_directory'], '/');
	$dname='';
	if ($pos===false) {
		$dname = $pdata['images_directory'];
	}
	elseif (isset($_REQUEST['images_directory'])) {
		$dname = substr($_REQUEST['images_directory'], $pos);
	}
	if ($dname!='') {
		_createDirectory($parent_id, $dname);
	}
}
echo '<tr><th><input type="hidden" name="images_directory" value="'
	.$pdata['images_directory'].'" /><div class="help products/images"></div>'
	.'Images</th><td colspan="4">';
$dir_id=kfm_api_getDirectoryId(
	preg_replace('/^\//', '', $pdata['images_directory'])
);
$images=kfm_loadFiles($dir_id);
$images=$images['files'];
$n=count($images);
echo '<iframe src="/ww.plugins/products/admin/uploader.php?images_directory='
	.urlencode($pdata['images_directory'])
	.'" style="width:400px;height:50px;border:0;overflow:hidden"></iframe>';
echo '<script>window.kfm={alert:function(){}};window.kfm_vars={};'
	.'function x_kfm_loadFiles(){}'
	.'function kfm_dir_openNode(){$("#products-form").submit();}'
	.'var product_id='.$id.';</script>';
if ($n) {
	echo '<div id="product-images-wrapper">';
	for ($i=0;$i<$n;$i++) {
		if (!isset($images[$i]['caption'])) {
			$images[$i]['caption']='';
		}
		$default=($images[$i]['id']==$pdata['image_default'])?' class="default"':'';
		echo '<div'.$default.'>';
		echo '<img id="products-img-'.$images[$i]['id']
			.'" src="/kfmget/'.$images[$i]['id']
			.',width=64,height=64" title="'
			.str_replace('\\\\n', '<br />', $images[$i]['caption'])
			.'" /><br /><input type="checkbox" id="products-dchk-'
			.$images[$i]['id'].'" />'
			.'<a class="delete" href="javascript:;" id="products-dbtn-'
			.$images[$i]['id'].'">delete</a><br />'
			.'<a class="caption" href="javascript:;" id="products-cbtn-'
			.$images[$i]['id'].'">edit caption</a><br />'
			.'<a class="mark-as-default" href="javascript:;" '
			.'id="products-dfbtn-'.$images[$i]['id'].'">set default</a></div>';
	}
	echo '</div>';
} 
else {
	echo '<em>no images yet. please upload some.</em>';
}
echo '<a style="background:#ff0;font-weight:bold;color:red;display:block;'
	.'text-align:center;" href="#page_vars[images_directory]" '
	.'onclick="javascript:window.open(\'/j/kfm/'
	.'?startup_folder='.addslashes($pdata['images_directory']).'\'+'
	.'\'kfm\',\'modal,width=800,height=600\');">Manage Images</a></td></tr>';
// }
echo '</table></div>';
// }
// { data fields
echo '<div id="data-fields"><table id="data-fields-table">';
$dfs=json_decode($pdata['data_fields'], true);
$dfjson=dbOne(
	'select data_fields from products_types '
	.'where id='.$pdata['product_type_id'],
	'data_fields'
);
if ($dfjson=='') {
	$dfjson='[]';
}
$dfjson=str_replace(array("\n", "\r"), array('\n', ''), $dfjson);
$dfjson=json_decode($dfjson, true);
$dfdefs=array();
if (@$dfjson) {
	foreach ($dfjson as $d) {
		$dfdefs[$d['n']]=$d;
	}
	foreach ($dfs as $df) {
		if (isset($df['n']) && isset($dfdefs[$df['n']])) {
			$def=$dfdefs[$df['n']];
			unset($dfdefs[$df['n']]);
			Products_showDataField($df, $def);
		}
	}
	foreach ($dfdefs as $def) {
		Products_showDataField(array('v'=>''), $def);
	}
}
else {
	echo '<p><i>No datafields defined in Product Type</i></p>';
}
echo '</table></div>';
// }
// { Online Store
if (isset($PLUGINS['online-store'])) {
	// { set up fields
	$online_store_fields 
		= array (
			'_price' => 'Price',
			'_trade_price' => 'Trade Price',
			'_sale_price' => 'Sale Price',
			'_bulk_price' => 'Bulk Price',
			'_bulk_amount' => 'Bulk Amount',
			'_weight(kg)' => 'Weight (kg)',
			'_vatfree'  
				=> array (
					'VAT-free', 
					'Options' 
						=>array(
							'No',
							'Yes'
						)
				),
			'_custom_vat_amount' => 'Custom VAT Amount',
			'_deliver_free' => array(
				'Free Delivery', 'Options'=>array('No', 'Yes')
			),
			'_not_discountable' => array(
				'Not Discountable', 'Options'=>array('No', 'Yes')
			),
			'_sold_amt' => 'Amount Sold',
			'_stock_amt' => 'Amount in Stock'
		);
	if (dbOne(
		'select is_voucher from products_types where id='.$pdata['product_type_id'],
		'is_voucher'
	)=='1') {
		$online_store_fields['_voucher_value']='Voucher Value';
	}
	// }
	$online_store_data = json_decode($pdata['online_store_fields']);
	echo '<div id="online-store-fields" class="products-online-store"';
	if (!isset($addOnlineStoreFields)||!$addOnlineStoreFields) {
		echo ' style="display:none';
	}
	echo '>';
	echo '<table>';
	foreach ($online_store_fields as $internal=>$display) {
		echo '<tr><th>';
		if (is_array($display)) {
			echo $display[0];
		}
		else {
			echo $display;
		}
		echo '</th>';
		echo '<td>';
		if (!is_array($display)) {
			echo '<input class="small" type="number" name="online-store-fields['.$internal.']"';
			if (isset($online_store_data->$internal)) {
				echo ' value="'.$online_store_data->$internal.'"';
			}
			echo ' />';
		}
		else {
			echo '<select name="online-store-fields['.$internal.']">';
			for ($i=0; $i<count($display['Options']); ++$i) {
				echo '<option value="'.$i.'"';
				if ($i==@$online_store_data->$internal) {
					echo 'selected="selected"';
				}
				echo '>'.$display['Options'][$i]
					.'</option>';
			}
			echo '</select>';
		}
		echo '</td>';
	}
	echo '</table></div>';
}
// }
// { categories
echo '<div id="categories"><p>At least one category must be chosen.</p>';
// { build array of categories
$rs=dbAll('select id,name,parent_id from products_categories');
$cats=array();
foreach ($rs as $r) {
	$cats[$r['id']]=$r;
}
// }
// { add selected categories to the list
$rs=dbAll('select * from products_categories_products where product_id='.$id);
foreach ($rs as $r) {
	$cats[$r['category_id']]['selected']=true;
}
// }
function show_sub_cats($parent) {
	global $cats;
	$found=array();
	foreach ($cats as $id=>$cat) {
		if (isset($cat['parent_id'])
			&& $cat['parent_id']==$parent
			&& isset($cat['name'])
		) {
			$l='<li><input type="checkbox" name="product_categories['.$id.']"';
			if (isset($cat['selected'])) {
				$l.=' checked="checked"';
			}
			$l.='>'.htmlspecialchars($cat['name']);
			$l.=show_sub_cats($id);
			$found[]=$l;
		}
	}
	return '<ul>'.join('', $found).'</ul>';
}
echo show_sub_cats(0);
$cid=((int)$pdata['default_category']);
if (!$cid) {
	$cid=1;
}
echo '<label>Default Category: <select name="products_default_category">'
	.'<option value="'.((int)$pdata['products_default_category']).'">'
	.dbOne(
		'select name from products_categories where id='.$cid,
		'name'
	)
	.'</option></select></label>';
echo '</div>';
// }
// { related items
if (count($relations)) {
	echo '<div id="relations">'
		.'<table id="product-relations"><tr><th>Relation Type</th><th>Related P'
		.'roduct</th></tr>';
	foreach ($relations as $relation) {
		$ps=dbAll(
			'select * from products_relations where relation_id='.$relation['id']
			.' and from_id='.$id
		);
		$options='<option value=""> -- please choose -- </option>';
		foreach ($relations as $r) {
			$options.='<option value="'.$r['id'].'"';
			if ($r['id']==$relation['id']) {
				$options.=' selected="selected"';
			}
			$options.='>'
				.htmlspecialchars($r['name'])
				.'</option>';
		}
		foreach ($ps as $p) {
			echo '<tr><td><select name="product-relations-type[]">'
				.$options.'</select></td><td><select class="products-relations-product"'
		    .' name="products-relations-product[]">'
				.'<option value="'.$p['to_id'].'">';
			echo htmlspecialchars(
				dbOne('select name from products where id='.$p['to_id'], 'name')
			)
				.'</option></select></td></tr>';
		}
	}
	echo '<tr><td><select name="product-relations-type[]">'
		.'<option value=""> -- please choose -- </option>';
	foreach ($relations as $relation) {
		echo '<option value="'.$relation['id'].'">'
			.htmlspecialchars($relation['name'])
			.'</option>';
	}
	echo '</select></td>'
		.'<td><select class="products-relations-product"'
		.' name="products-relations-product[]">'
		.'<option value=""> -- please choose -- </option>';
	WW_addScript('/ww.plugins/products/admin/products-edit-related.js');
	echo '</td></tr></table></div>';
}
// }
echo '</div><input type="submit" value="Save" /></form>';
WW_addScript('/ww.plugins/products/admin/products-edit.js');
WW_addScript('/ww.plugins/products/admin/create-page.js');
