<?php
$smarty=smarty_setup(USERBASE.'/ww.cache/pages');
if (!isset($_SESSION['ab_testing']['p'.$page->id])) {
	if (!file_exists(USERBASE.'/ww.cache/pages/template_ab_'.$page->id.'_0')) {
		$vs=explode('<div>ABTESTINGDELIMITER</div>', $page->body);
		for ($i=0; $i<count($vs); ++$i) {
			file_put_contents(
				USERBASE.'/ww.cache/pages/template_ab_'.$page->id.'_'.$i,
				$vs[$i]
			);
		}
	}
	else {
		$i=0;
		do {
			$i++;
		} while (file_exists(USERBASE.'/ww.cache/pages/template_ab_'.$page->id.'_'.$i));
	}
	if (!isset($_SESSION['ab_testing'])) {
		$_SESSION['ab_testing']=array();
	}
	$_SESSION['ab_testing']['p'.$page->id]=rand(0, $i-1);
	if ($i>1) {
		if (!isset($_SESSION['ab_testing_targets'])) {
			$_SESSION['ab_testing_targets']=array();
		}
		$_SESSION['ab_testing_targets']['p'.$page->vars['abtesting-target']]=$page->id;
	}
}
$body=$smarty->fetch(USERBASE.'/ww.cache/pages/template_ab_'.$page->id.'_'.$_SESSION['ab_testing']['p'.$page->id]);
