<?php include 'userauth.php';

$auth->cookielogin();

if (isset($_GET['a']) && $_GET['a'] == "logout")
	$auth->logout();
if (isset($_POST['user'])) {
	$res = ($auth->login($_POST['user'], $_POST['pass'], true)) ? "Logged in!" : "Login failed.";
}	

 ?>
<h1>Demo of User Authentication system</h1><br />
<?php
	if ($auth->isAuthenticated()) {
		echo "You are currently logged in as " . $auth->uname() . ". Click <a href=\"?a=logout\">here</a> to log out.";
	} else {
?>
<form method="POST" action="demo.php">
Username: <input name="user" /> 
Password: <input type="password" name="pass" /> 
<input type="submit" value="Login" />
</form>
<?php } ?>

<h1>Create a new user</h1><br />
<?php
	if (isset($_POST['newuser'])) {
		echo ($auth->newUser($_POST['newuser'], $_POST['pass'])) ? "User created." : "Username taken.";
	}
?>
<form method="POST" action="demo.php">
Username: <input name="newuser" /> 
Password: <input name="pass" /> 
<input type="submit" value="Create user" />
</form>
