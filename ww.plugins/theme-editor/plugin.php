<?php
/**
	* definition file for the theme editor plugin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$plugin=array(
	'name'=>'Theme Editor',
	'description'=>'This plugin will let you edit your themes online.',
	'admin'=>array(
		'menu'=>array(
			'Site Options>Theme Editor'=>'plugin.php?_plugin=theme-editor&amp;_page=index'
		)
	)
);
