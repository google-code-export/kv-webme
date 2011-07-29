<?php
/**
	* admin login page
	*
	* PHP version 5.2
	*
	* @category None
	* @package  None
	* @author   Kae Verens <kae@kvsites.ie>
	* @license  GPL 2.0
	* @link     http://kvsites.ie/
	*/

$existing_accounts=dbOne('select count(id) as ids from user_accounts', 'ids');
if (@$_REQUEST['action']=='remind') {
	$email=$_REQUEST['email'];
	if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$u=dbRow("SELECT * FROM user_accounts WHERE email='$email'");
		if (count($u)) {
			$passwd=Password::getNew();
			dbQuery(
				"UPDATE user_accounts SET password=md5('$passwd') WHERE email='$email'"
			);
			mail(
				$email, '['.$sitedomain.'] admin password reset',
				'Your new password is "'.$passwd
				.'". Please log into the admin area and change it to something else.',
				"Reply-to: $email\nFrom: $email"
			);
		}
	}
}
if (!$existing_accounts
	&& isset($_REQUEST['email'])
	&& isset($_REQUEST['password'])
) {
	$email=$_REQUEST['email'];
	$password=md5($_REQUEST['password']);
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$message='Please make sure to use a valid email address';
	}
	else {
		dbQuery(
			'insert into user_accounts set id=1,email="'.addslashes($email).'",'
			.'name="Administrator",password="'.$password.'",active=1,parent=0,'
			.'date_created=now()'
		);
		dbQuery("insert into groups values(1,'administrators',0)");
		dbQuery("insert into users_groups values(1,1)");
		$message='User account created. Please login now (press F5 and choose '
			.'to resubmit the login data)';
	}
}
?>
<html>
	<head>
		<title><?php echo 'Login'; ?></title>
<?php
	require_once $_SERVER['DOCUMENT_ROOT'].'/ww.incs/common.php';
	echo Core_getJQueryScripts();
?>
		<link rel="stylesheet" type="text/css" href="/ww.admin/theme/login.css" />
		<script>
			$(function() {
				$('#login-tabs').tabs();
			});
		</script>
	</head>
 <body onload="document.getElementById('email').focus();">
 	<div id="wrapper">
	
	<div id="header"><div id="topImage"></div></div>
	
	<div id="mainContent">
	<div class="paragraph">
		<p>
<?php
if (!$existing_accounts) {
	echo '<em><strong>No user accounts exist yet</strong>. Please log in usin'
		.'g your email address and a password of your choice. This will become '
		.'the first admin user account.</em>';
}
else {
	echo 'To access the administrative features of your website, you will need'
		.' to enter the username and password below and click "login".';
}
if (@$message!='') {
	echo '<br /><br /><strong>'.$message.'</strong>';
}
?>
		</p>
	</div>
	<div id="login-tabs" style="width:40%;margin:0 auto;">
		<ul>
			<li><a href="#admin-login">Login</a></li>
			<li><a href="#admin-reminder">Reminder</a></li>
		</ul>
		<div id="admin-login">
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				<table cols="3">
			   	<tr>
						<th colspan="1">email</th>
						<td colspan="2"><input id="email" name="email" /></td>
					</tr>
			   	<tr>
						<th colspan="1">password</th>
						<td colspan="2"><input type="password" name="password" /></td>
					</tr>
					<tr>
						<th colspan="3" align="right">
							<input name="action" type="submit" value="login" class="login" />
						</th>
					</tr>
				</table>
	   	</form>
		</div>
		<div id="admin-reminder">
	   	<form method="post" action="<?php echo $_SERVER['PHP_SELF'];?>">
				<table cols="3">
			   	<tr>
						<th colspan="1">email</th>
						<td colspan="2"><input id="email" type="text" name="email" /></td>
					</tr>
					<tr>
						<th colspan="3" align="right">
							<input name="action" type="submit" value="remind" class="login" />
						</th>
					</tr>
				</table>
				<p>Use this form to create a new password for yourself.</p>
	   	</form>
		</div>
	</div>
	</div>
 </body>
</html>
