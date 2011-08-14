<?php
if (!Core_isAdmin()) {
	exit;
}

// { links: add product, import products
echo '<a href="plugin.php?_plugin=products">List all products</a> | '
	.'<a href="plugin.php?_plugin=products&amp;_page=products-edit">'
	.'Add a Product</a> | '
	.'Import Products: '
	.'<a href="plugin.php?_plugin=products&amp;_page=import">CSV</a> / '
	.'<a href="plugin.php?_plugin=products&amp;_page=import-json">JSON</a>'
	;
// }

if (isset($_REQUEST['delete']) && is_numeric($_REQUEST['delete'])) {
	if (isset($_REQUEST['delete-images'])&&($_REQUEST['delete-images']==1)) {
		$imagesDir
			= dbOne(
				'select images_directory
				from products
				where id='.$_REQUEST['delete'],
				'images_directory'
			);
		$id = kfm_api_getDirectoryId($imagesDir);
		if ($id) {
			$dir = kfmDirectory::getInstance($id);
			if ($dir) {
				$dir->delete();
			}
		}
	}
	dbQuery('delete from products where id='.$_REQUEST['delete']);
	echo '<em>Product deleted.</em>';
}

$rs=dbAll('select id,name,enabled from products order by name');
if (!dbOne('select id from products_types limit 1','id')) {
	echo '<em>You can\'t create a product until you have created a type. '
		.'<a href="javascript:Core_screen(\'products\',\'js:Types\');">Click '
		.'here to create one</a></em>';
	return;
}
if(!count($rs)){
	echo '<em>No existing products. <a href="plugin.php?_plugin=products&amp;'
		.'_page=products-edit">Click here to create one</a>.'
		.' or import from '
		.'<a href="plugin.php?_plugin=products&amp;_page=import">CSV</a> or '
		.'<a href="plugin.php?_plugin=products&amp;_page=import-json">JSON</a>';
	return;
}

// { products list
echo '<div><table class="datatable"><thead><tr><th>Name</th><th>ID</th>'
	.'<th>Enabled</th><th>&nbsp;</th></tr></thead><tbody>';
foreach($rs as $r){
	/* do not delete the HTML comment in the next line - it's there
	 * for datatables magic. without it, sorting will not work. */
	$link='plugin.php?_plugin=products&amp;_page=products-edit&amp;id='.$r['id'];
	echo '<tr id="product-row-'.$r['id'].'">'
		.'<td class="edit-link"><!-- '.htmlspecialchars($r['name']).' -->'
		.'<a href="'.$link.'">'.htmlspecialchars($r['name']).'</td>'
		.'<td>'.$r['id'].'</td>'
		.'<td>'.($r['enabled']=='1'?'Yes':'No').'</td>'
		.'<td><a class="delete-product" href="javascript:;" title="delete">[x]</a>'
		.'</td></tr>';
}
echo '</tbody></table></div>';
// }

WW_addScript('/ww.plugins/products/admin/products.js');
