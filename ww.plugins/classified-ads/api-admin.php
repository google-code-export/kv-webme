<?php
/**
	* admin API
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

// { ClassifiedAds_adminCategoryDelete

/**
	* delete a category
	*
	* @return status
	*/
function ClassifiedAds_adminCategoryDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from classifiedads_ad where category_id='.$id);
	dbQuery('delete from classifiedads_categories where id='.$id);
	return array('ok'=>1);
}

// }
// { ClassifiedAds_adminCategoryName

/**
	* rename a category
	*
	* @return status
	*/
function ClassifiedAds_adminCategoryRename() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	dbQuery(
		'update classifiedads_categories set name="'.addslashes($name).'"'
		.' where id='.$id
	);
	return array('ok'=>1);
}

// }
// { ClassifiedAds_adminCategoryUpdate

/**
	* edit a category
	*
	* @return status
	*/
function ClassifiedAds_adminCategoryUpdate() {
	$id=(int)$_REQUEST['id'];
	$parent=(int)$_REQUEST['parent'];
	$name=$_REQUEST['name'];
	$icon=$_REQUEST['icon'];
	$sql=' set parent='.$parent.', name="'.addslashes($name).'"'
		.', icon="'.addslashes($icon).'"';
	if ($id) {
		dbQuery('update classifiedads_categories'.$sql.' where id='.$id);
	}
	else {
		dbQuery('insert into classifiedads_categories'.$sql);
	}
	return array('ok'=>1);
}

// }
// { ClassifiedAds_adminCategoryMove

/**
	* ClassifiedAds_adminCategoryMove
	*
	* @return status
	*/
function ClassifiedAds_adminCategoryMove() {
	$id=(int)$_REQUEST['id'];
	$parent=(int)$_REQUEST['parent'];
	dbQuery(
		'update classifiedads_categories set parent='.$parent.' where id='.$id
	);
	return array('ok'=>1);
}

// }
// { ClassifiedAds_adminTypeGet

/**
	* get an ad type's details
	*
	* @return array of details
	*/
function ClassifiedAds_adminTypeGet() {
	return dbRow(
		'select * from classifiedads_types where id='.((int)$_REQUEST['id'])
	);
}

// }
// { ClassifiedAds_adminTypeEdit

/**
	* edit an ad type
	*
	* @return status
	*/
function ClassifiedAds_adminTypeEdit() {
	$sql='classifiedads_types set maxchars='.((int)$_REQUEST['maxchars'])
		.', price_per_day='.((float)$_REQUEST['price_per_day'])
		.', minimum_number_of_days='.((int)$_REQUEST['minimum_number_of_days'])
		.', number_of_images='.((int)$_REQUEST['number_of_images'])
		.', name="'.addslashes($_REQUEST['name']).'"';
	$id=(int)$_REQUEST['id'];
	if ($id) {
		dbQuery('update '.$sql.' where id='.$id);
	}
	else {
		dbQuery('insert into '.$sql);
		$id=dbLastInsertId();
	}
	return array(
		'id'=>$id,
		'opts'=>dbAll('select id, name from classifiedads_types order by name')
	);
}

// }
// { ClassifiedAds_adminAdsGetDT

/**
	* get a list of products in datatables format
	*
	* @return array products list
	*/
function ClassifiedAds_adminAdsGetDT() {
	$start=(int)$_REQUEST['iDisplayStart'];
	$length=(int)$_REQUEST['iDisplayLength'];
	$search=$_REQUEST['sSearch'];
	$orderby=(int)$_REQUEST['iSortCol_0'];
	$orderdesc=$_REQUEST['sSortDir_0']=='desc'?'desc':'asc';
	switch ($orderby) {
		case 2:
			$orderby='expiry_date';
		break;
		case 3:
			$orderby='user_id';
		break;
		case 4:
			$orderby='cost';
		break;
		default:
			$orderby='expiry_date';
	}
	$filters=array();
	if ($search) {
		$filters[]='expiry_date like "%'.addslashes($search).'%"'
			.' or cost like "%'.addslashes($search).'%"';
	}
	$filter='';
	if (count($filters)) {
		$filter='where '.join(' and ', $filters);
	}
	$rs=dbAll(
		'select id,user_id, creation_date, expiry_date,cost,status'
		.' from classifiedads_ad '.$filter
		.' order by '.$orderby.' '.$orderdesc
		.' limit '.$start.','.$length
	);
	$result=array();
	$result['sEcho']=intval($_GET['sEcho']);
	$result['iTotalRecords']=dbOne(
		'select count(id) as ids from classifiedads_ad', 'ids'
	);
	$result['iTotalDisplayRecords']=dbOne(
		'select count(id) as ids from classifiedads_ad '.$filter,
		'ids'
	);
	$arr=array();
	foreach ($rs as $r) {
		$row=array($r['id']);
		$row[]=$r['creation_date'];
		$row[]=$r['expiry_date'];
		// { user
		$user=User::getInstance($r['user_id'], false, false);
		$row[]=$r['user_id'].'|'.($user?$user->get('name'):'unknown owner');
		// }
		// { cost
		$row[]=$r['cost'];
		// }
		// { paid
		$row[]=$r['status']=='1'?'Yes':'No';
		// }
		$row[]='';
		$arr[]=$row;
	}
	$result['aaData']=$arr;
	return $result;
}

// }
// { ClassifiedAds_adminAdGet

/**
	* retrieve an ad
	*
	* @return array ad details
	*/
function ClassifiedAds_adminAdGet() {
	$id=(int)$_REQUEST['id'];
	return dbRow('select * from classifiedads_ad where id='.$id);
}

// }
// { ClassifiedAds_adminCategoryUploadImage

/**
	* upload a new category image
	*
	* @return null
	*/
function ClassifiedAds_adminCategoryUploadImage() {
	$id=(int)$_REQUEST['id'];
	if (!file_exists(USERBASE.'/f/classified-ads/categories/'.$id)) {
		mkdir(USERBASE.'/f/classified-ads/categories/'.$id, 0777, true);
	}
	$imgs=new DirectoryIterator(USERBASE.'/f/classified-ads/categories/'.$id);
	foreach ($imgs as $img) {
		if ($img->isDot()) {
			continue;
		}
		unlink($img->getPathname());
	}
	$from=$_FILES['Filedata']['tmp_name'];
	$ext=preg_replace('/.*\./', '', $_FILES['Filedata']['name']);
	$url='/classified-ads/categories/'.$id.'/icon.'.$ext;
	$to=USERBASE.'/f'.$url;
	move_uploaded_file($from, $to);
	dbQuery(
		'update classifiedads_categories set'
		.' icon="'.$url.'" where id='.$id
	);
	Core_cacheClear();
	echo $url;
	Core_quit();
}

// }
