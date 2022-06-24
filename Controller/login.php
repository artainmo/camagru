<?php
	session_start();

	if (isset($_POST['login'])) {	
		$name = htmlspecialchars(trim($_POST['name']));
		$password = htmlspecialchars(trim($_POST['password']));

		require(__DIR__ . "/../Model/manageDatabase.php");
		$db = new ManageDatabase;
		if (!$db->verifyPasswordAccount($name, $password)) {
			$error = "Wrong name or password.";
		} else {
			$_SESSION['account'] = $name;
			header('Location: http://localhost:8000/profile.php');
		}
	}
?>

<?php require(__DIR__ . "/../View/header/connect-app-header.php"); ?>

<h3>Login</h3>
<form action="login.php" method="POST">
	<label>Name</label><br/>
	<input type="text" name="name" maxlength="20" required/><br/><br/>
	<label>Password</label><br/>
	<input type="password" name="password" maxlength="20" required/><br/>
	<?php if (isset($error)) {echo 
	"<a href='passwordReset.php'>You forgot your password?</a><br/>";}?>
	<br/>
	<button type="submit" name="login">Login</button><br/><br/>
	<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>
</form>

<?php require(__DIR__ . "/../View/footer/footer.html"); ?>
