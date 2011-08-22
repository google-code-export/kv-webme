<?php
/**
	* definition file for SiteCredits plugin
	*
	* PHP version 5
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     None
	*/

// { define $plugin
$plugin=array(
	'name' => 'SiteCredits',
	'hide_from_admin'=>	true,
	'description' => 'admins can pay for credits, which are used to manage site subscriptions',
	'admin' => array(
		'menu' => array(
			'Credits>Buy Credits' => 'buy'
		)
	),
	'frontend' => array(
	),
	'triggers'      => array(
		'page-object-loaded'=>'SiteCredits_isActive'
	),
	'version'=>3
);
// }

function SiteCredits_isActive() {
	global $DBVARS;
	if (@$DBVARS['sitecredits-credits']<0) {
		echo '<p>Website Administrator attention needed.'
			.' Please log into your control panel.</p>';
		exit;
	}
}