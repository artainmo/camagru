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
			$account = $db->getAccountByName($name)[0];
			$_SESSION['account'] = $account->id;
			header('Location: http://localhost:8000/gallery.php');
		}
	}
?>

<?php require(__DIR__ . "/../View/header/connect-app-header.php"); ?>

<div class="centerForm">
	<h1>Login</h1><br><br>
	<form action="login.php" method="POST">
		<label>Name:</label><br/>
		<input type="text" name="name" maxlength="20" required/><br/><br/>
		<label>Password:</label><br/>
		<input type="password" name="password" maxlength="20" required/><br/>
		<?php if (isset($error)) {echo
		"<a href='passwordReset.php'>You forgot your password?</a><br/>";}?>
		<br/>
		<button type="submit" name="login">Login</button><br/><br/>
		<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>
	</form>
</div>
