<?php
/**
	* panel for Page admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

echo '<h2>Privacy</h2>';

echo '<table id="privacy-options">';
// { page is visible only by logged-in users
echo '<tr><th>Page is viewable only by logged-in users:</th><td>';
echo '<select name="page_vars[privacy_require_login]">'
	.'<option value="">No</option><option value="1"';
if (@$page_vars['privacy_require_login']) {
	echo ' selected="selected"';
}
echo '>Yes</option></select></td></tr>';
// }
// { restrict access to members of these group
echo '<tr><th>Page is viewable only by members of these groups:</th><td>';
$rs=dbAll('select * from groups order by name');
$restrict_to=array();
if (isset($page_vars['restrict_to_groups'])
	&& $page_vars['restrict_to_groups']!=''
) {
	$restrict_to=json_decode($page_vars['restrict_to_groups']);
}
foreach ($rs as $r) {
	echo '<input type="checkbox" '
		.'name="page_vars[restrict_to_groups]['.$r['id'].']"';
	if (isset($restrict_to->$r['id'])) {
		echo ' checked="checked"';
	}
	echo ' />'.htmlspecialchars($r['name']).'<br />';
}
echo '</td></tr>';
// }
// { allow non-logged-in readers to view the page if they know a password
if (!isset($page_vars['privacy_password'])) {
	$page_vars['privacy_password']='';
}
echo '<tr><th>Allow non-logged-in readers to view the page if they enter '
	.'this password:</th>'
	.'<td><input name="page_vars[privacy_password]" value="'
	.htmlspecialchars($page_vars['privacy_password'])
	.'" /></td></tr>';
// }
echo '</table>';
WW_addScript('/ww.plugins/privacy/admin/privacy_show_page_panel.js');
