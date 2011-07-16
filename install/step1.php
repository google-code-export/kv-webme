<?php
require 'header.php';

// get db variables
if(!isset($_SESSION['db_vars'])){ // set up dummy values
	$_SESSION['db_vars']=array(
		'username' => '',
		'password' => '',
		'hostname' => 'localhost',
		'db_name'  => '',
		'passed'   => 0
	);
}

if(isset($_REQUEST['action'])){
	$_SESSION['db_vars']=array(
		'username' => $_REQUEST['username'],
		'password' => $_REQUEST['password'],
		'hostname' => $_REQUEST['hostname'],
		'db_name'  => $_REQUEST['db_name'],
		'passed'   => 0
	);

	$mysql = mysql_connect($_SESSION['db_vars']['hostname'], $_SESSION['db_vars']['username'], $_SESSION['db_vars']['password']);

	if(!$mysql){
		printf("Connect failed: %s\n", mysql_error());
		echo '<p>Please check your values and try again.</p>';
	}
	else{
		// if database doesn't exist, try create it
		if (!mysql_select_db($_SESSION['db_vars']['db_name'])) {
			mysql_query('create database `'.addslashes($_REQUEST['db_name']).'`');
		}
		// if it still doesn't exist, fail
		if(!mysql_select_db($_SESSION['db_vars']['db_name'])){
			echo '<p>Please provide an existing database name.</p>';
		}
		else{
			$_SESSION['db_vars']['passed']=1;
			header( 'location: step2.php' );
		}
	}
}

/**
 * add form validation
 */
echo '
<script type="text/javascript">
	$( document ).ready( function( ){
		var options = { "username" : { "required" : true }, "hostname" : { "required" : true }, "db_name" : { "required" : true } };
		$( "#database-form" ).validate( options, error_handler );
	} );
</script>';

echo '<h3>Database Details</h3>';
echo '<p id="errors"></p>';
echo '<form method="post" id="database-form"><table>';
echo '<tr><th>Username</th><td><input type="text" name="username" value="'.htmlspecialchars($_SESSION['db_vars']['username']).'" /></td></tr>';
echo '<tr><th>Password</th><td><input type="text" name="password" value="'.htmlspecialchars($_SESSION['db_vars']['password']).'" /></td></tr>';
echo '<tr><th>HostName</th><td><input type="text" name="hostname" value="'.htmlspecialchars($_SESSION['db_vars']['hostname']).'" /></td></tr>';
echo '<tr><th>Database Name</th><td><input type="text" name="db_name" value="'.htmlspecialchars($_SESSION['db_vars']['db_name']).'" /></td></tr>';
echo '</table><input name="action" type="submit" value="Configure Database" /></form>';

require 'footer.php';
