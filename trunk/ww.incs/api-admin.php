<?php
/**
	* API for common admin WebME functions
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

function Core_adminCronGet() {
	return dbAll('select * from cron');
}
function Core_adminCronSave() {
	global $DBVARS;
	$id=(int)$_REQUEST['id'];
	$field=$_REQUEST['field'];
	$value=$_REQUEST['value'];
	dbQuery(
		'update cron set `'.addslashes($field).'`="'.addslashes($value)
		.'" where id='.$id
	);
	unset($DBVARS['cron-next']);
	Core_configRewrite();
	return array('ok'=>1);
}
function Core_adminDirectoriesGet() {
	function get_subdirs($base, $dir) {
		$arr=array();
		$D=new DirectoryIterator($base.$dir);
		$ds=array();
		foreach ($D as $dname) {
			if ($dname->isDot() || !$dname->isDir() 
				|| strpos($dname->getFilename(), '.')===0
			) {
				continue;
			}
			$ds[]=$dname->getFilename();
		}
		asort($ds);
		foreach ($ds as $d) {
			$arr[$dir.'/'.$d]=$dir.'/'.$d;
			$arr=array_merge($arr, get_subdirs($base, $dir.'/'.$d));
		}
		return $arr;
	}
	$arr=array_merge(array('/'=>'/'), get_subdirs(USERBASE.'f', ''));
	return $arr;
}
function Core_adminLanguagesAdd() {
	$name=$_REQUEST['name'];
	$code=$_REQUEST['code'];
	if (!$name || !$code) {
		return array(
			'error'=>'You must fill in Name and Code'
		);
	}
	if (dbOne(
		'select count(id) as ids from language_names where name="'
		.addslashes($name).'" or code="'.addslashes($code).'"',
		'ids'
	)) {
		return array(
			'error'=>'Either the Name or Code are already in use'
		);
	}
	dbQuery(
		'insert into language_names set name="'.addslashes($name).'"'
		.',code="'.addslashes($code).'",is_default=0'
	);
	return array('ok'=>1);
}
function Core_adminLanguagesDelete() {
	$id=(int)$_REQUEST['id'];
	dbQuery('delete from language_names where id='.$id);
	return array('ok'=>1);
}
function Core_adminLanguagesEdit() {
	$id=(int)$_REQUEST['id'];
	$name=$_REQUEST['name'];
	$code=$_REQUEST['code'];
	$is_default=(int)$_REQUEST['is_default'];
	if (!$name || !$code) {
		return array(
			'error'=>'You must fill in Name and Code'
		);
	}
	if ($is_default) {
		dbQuery('update language_names set is_default=0');
	}
	else {
		$r=dbRow('select * from language_names where id='.$id);
		if ($r['is_default']=='1') {
			$is_default=1; // cannot unset is_default. must set on a different lang
		}
	}
	dbQuery(
		'update language_names set name="'.addslashes($name).'"'
		.',code="'.addslashes($code).'",is_default='.$is_default
		.' where id='.$id
	);
	return array('ok'=>1);
}
function Core_adminPageChildnodes() {
	$pid=(int)preg_replace('/[^0-9]/', '', $_REQUEST['id']);
	$c=Core_cacheLoad('pages', 'adminmenu'.$pid);
	if ($c) {
		return $c;
	}
	$rs=dbAll(
		'select id,id as pid,special&2 as hide,type,name,'
		.'(select count(id) from pages where parent=pid) as children '
		.'from pages where parent='.$pid.' order by ord,name'
	);
	$data=array();
	foreach ($rs as $r) {
		$item=array(
			'data' => __FromJson($r['name'], true),
			'attr' => array(
				'id'   => 'page_'.$r['id']
			),
			'children'=>$r['children']?array():false
		);
		if ($r['type']!=='0') {
			$item['attr']['type']=$r['type'];
		}
		if ($r['hide']=='2') {
			$item['attr']['hide']='yes';
		}
		$data[]=$item;
	}
	Core_cacheSave('pages', 'adminmenu'.$pid, $data);
	return $data;
}
function Core_adminPageParentsList() {
	$id=isset($_REQUEST['other_GET_params'])?(int)$_REQUEST['other_GET_params']:-1;
	function selectkiddies($i=0, $n=1, $id=0) {
		$arr=array();
		$q=dbAll(
			'select name,id,alias from pages where parent="'.$i.'" and id!="'.$id
			.'" order by ord,name'
		);
		if (count($q)<1) {
			return $arr;
		}
		foreach ($q as $r) {
			if ($r['id']!='') {
				$arr[' '.$r['id']]=str_repeat('» ', $n).$r['name'];
				$arr=array_merge($arr, selectkiddies($r['id'], $n+1, $id));
			}
		}
		return $arr;
	}
	return array_merge(
		array(' 0'=>' -- none -- '),
		selectkiddies(0, 0, $id)
	);
}
function Core_adminPageTypesList() {
	$arr=array();
	global $pagetypes,$PLUGINS;
	foreach ($pagetypes as $a) {
		$arr[$a[0]]=$a[1];
	}
	foreach ($PLUGINS as $n=>$p) {
		if (isset($p['admin']['page_type'])) {
			if (is_array($p['admin']['page_type'])) {
				foreach ($p['admin']['page_type'] as $name=>$type) {
					$arr[$n.'|'.$name]=$name;
				}
			}
			else {
				$arr[$n.'|'.$n]=$n;
			}
		}
	}
	return $arr;
}
/**
  * create a copy of a page
  *
  * @return array status of the copy
  */
function Core_adminPageCopy() {
	$id=(int)$_REQUEST['id'];
	if (!$id) {
		return array('error'=>'no ID provided');
	}
	$p=dbRow('select * from pages where id='.$id);
	$name=$p['name'];
	$parts=array();
	foreach ($p as $k=>$v) {
		if ($k=='id') {
			continue;
		}
		$parts[]=$k.'="'.addslashes($v).'"';
	}
	dbQuery('insert into pages set '.join(',', $parts));
	$id=dbLastInsertId();
	dbQuery('update pages set name="'.addslashes($name).'_'.$id.'" where id='.$id);
	Core_cacheClear();
	return array('name'=>$name.'_'.$id, 'id'=>$id, 'pid'=>$p['parent']);
}

/**
  * delete a page
  *
  * @return array status of the deletion
  */
function Core_adminPageDelete() {
	$id=(int)$_REQUEST['id'];
	if (!$id) {
		return array('error'=>'no ID provided');
	}
	$r=dbRow("SELECT COUNT(id) AS pagecount FROM pages");
	if ($r['pagecount']<2) {
		return array('error'=>'there must always be at least one page.');
	}
	$q=dbQuery('select parent from pages where id="'.$id.'"');
	if ($q->rowCount()) {
		$r=dbRow('select parent from pages where id="'.$id.'"');
		dbQuery('delete from page_vars where page_id="'.$id.'"');
		dbQuery('delete from pages where id="'.$id.'"');
		dbQuery(
			'update pages set parent="'.$r['parent'].'" where parent="'.$id.'"'
		);
		Core_cacheClear();
		dbQuery('update page_summaries set rss=""');
		return array('ok'=>1);
	}
	return array('error'=>'page does not exist');
}

/**
  * move a page
  *
  * @return array status of the move
  */
function Core_adminPageMove() {
	$id=(int)$_REQUEST['id'];
	$to=(int)$_REQUEST['parent_id'];
	$order=$_REQUEST['order'];
	dbQuery("update pages set parent=$to where id=$id");
	for ($i=0;$i<count($order);++$i) {
		$pid=(int)$order[$i];
		dbQuery("update pages set ord=$i where id=$pid");
	}
	Core_cacheClear();
	dbQuery('update page_summaries set rss=""');
	return array('ok'=>1);
}

/**
	* save a session variable
	*
	* @return array status of save
	*/
function Core_adminSaveJSVar() {
	if (!isset($_SESSION['js'])) {
		$_SESSION['js']=array();
	}
	foreach ($_REQUEST as $k=>$v) {
		if (in_array($k, array('a', 'p', 'f', '_remainder'))) {
			continue;
		}
		$_SESSION['js'][$k]=$v;
	}
	return array('ok'=>1);
}

/**
	* save a session variable
	*
	* @return array status of save
	*/
function Core_adminLoadJSVars() {
	if (!isset($_SESSION['js'])) {
		$_SESSION['js']=array();
	}
	return $_SESSION['js'];
}
function Core_adminUserNamesGet() {
	$names=array();
	foreach(
		dbAll('select id,name,email from user_accounts order by name') as $r
	) {
		if (!$r['name']) {
			$r['name']=$r['email'];
		}
		$names[$r['id']]=$r['name'];
	}
	return $names;
}
