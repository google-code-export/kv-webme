<?php
require 'header.php';
// create an admin

if(!$_SESSION['db_vars']['db_installed']){ // user shouldn't be here
	header('Location: /install/step2.php');
	exit;
}

$_SESSION['admin_created']=0;
if(isset($_REQUEST['action'])){
	$ok=1;
	$_SESSION['user']=array(
		'email'    => $_REQUEST['email'],
		'password' => $_REQUEST['password'],
		'name'     => $_REQUEST['name']
	);
	if($_REQUEST['password']!=$_REQUEST['password2'] || $_REQUEST['password']==''){
		echo '<p>Passwords do not match or are empty.</p>';
		$ok=0;
	}
	if(!filter_var($_REQUEST['email'],FILTER_VALIDATE_EMAIL)){
		echo '<p>Email not valid. Please try again.</p>';
		$ok=0;
	}
	if($ok){
		mysql_connect($_SESSION['db_vars']['hostname'], $_SESSION['db_vars']['username'], $_SESSION['db_vars']['password']);
		mysql_select_db($_SESSION['db_vars']['db_name']);
		mysql_query(
			'insert into user_accounts set id=1,'
			.'email="'.addslashes($_REQUEST['email']).'",'
			.'password="'.md5($_REQUEST['password']).'",'
			.'name="'.addslashes($_REQUEST['name']).'",active=1,'
			.'parent=0'
		);
		mysql_query("insert into groups (id,name) values(1,'administrators')");
		mysql_query("insert into users_groups values(1,1)");
		$_SESSION['admin_created']=1;
		header( 'location: step4.php' );
	}
}
if(!isset($_SESSION['user']))$_SESSION['user']=array('email'=>'','password'=>'','name'=>'');

/**
 * add form validation
 */
echo '
<script type="text/javascript">
        $( document ).ready( function( ){
                var options = { "name" : { "required" : true }, "email" : { "required" : true, "email" : true }, "password" : { "required" : true, "match" : "password2" } };
                $( "#user-form" ).validate( options, error_handler );
        } );
</script>';

echo '<h3>User Account</h3>';
echo '<p id="errors"></p>';
echo '<form method="post" id="user-form"><table>';
echo '<tr><th>Name</th><td><input type="text" name="name" value="'.htmlspecialchars($_SESSION['user']['name']).'" /></td></tr>';
echo '<tr><th>Email</th><td><input type="text" name="email" value="'.htmlspecialchars($_SESSION['user']['email']).'" /></td><td>You will log in with this. Please ensure it is correct. If you forget your password, it can be sent to this address.</td></tr>';
echo '<tr><th>Password</th><td><input type="password" name="password" value="" /></td></tr>';
echo '<tr><th>Repeat Password</th><td><input type="password" name="password2" value="" /></td></tr>';
echo '</table><input name="action" type="submit" value="Create Admin" /></form>';


require 'footer.php';
