<?php
/**
	* publisher admin
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

if (!Core_isAdmin()) {
	die('access denied');
}

echo '<p>Please use this <i>rarely</i> - it uses quite a bit of resources.</p>'
	.'<p><a id="publisher-start" href="javascript:publisher_start()">click here'
	.'</a> to start generating the published version of the site.</p>'
	.'<style>#publisher-wrapper li{color:#999;}</style>'
	.'<div id="publisher-wrapper"></div>'
	.'<script src="/ww.plugins/publisher/admin/publish.js"></script>';
