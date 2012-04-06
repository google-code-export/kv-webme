<?php
/**
  * Blog plugin main contents
  *
  * PHP Version 5
  *
  * @category   None
  * @package    None
  * @subpackage None
  * @author     Kae Verens <kae@kvsites.ie>
  * @license    GPL Version 2
  * @link       www.kvweb.me
 */

$bits=explode('/', $unused_uri);
$user_id=(int)$bits[0];
$date=$bits[1];
$titlelike=str_replace('-', '_', $bits[2]);
$sql='select * from blog_entry where user_id='.$user_id
	.' and cast(cdate as date)'
	.'="'.addslashes($date).'" and title like "'.addslashes($titlelike).'"';
$r=dbRow($sql);
if (!$r) {
	$c='<div class="blog-article-error">Error: article not found.</div>';
	return;
}
$c='<div class="blog-article-wrapper" id="blog-entry-'.$r['id'].'">';
$c.='<h1 class="blog-header">'.htmlspecialchars($r['title']).'</h1>';
$user=User::getInstance($r['user_id']);
$name=$user?$user->name:'unknown';
$c.='<span class="blog-author" data-uid="'.$r['user_id'].'">'.$name.'</span> ~ '
	.'<span class="blog-date-published">'.Core_dateM2H($r['cdate']).'</span>';
$c.='<div class="blog-body">'.$r['body'].'</div>';
$date=preg_replace('/ .*/', '', $r['cdate']);
$c.='</div>';
WW_addScript('blog');
WW_addInlineScript('window.blog_comments='.$r['allow_comments'].';');
