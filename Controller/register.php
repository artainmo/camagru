<?php
	if (isset($_POST['register']) {	
		require(__DIR__ . "/../Model/manageDatabase.php");
		$db = new ManageDatabase;

		$name = filter_var($_POST['name'], FILTER_SANITIZE_STRING);
		$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
		$password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);
		if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
			
		}
	}
?>

<?php require(__DIR__ . "/../View/base-header.html"); ?>

<h4>Register</h4>
<form action="register.php" method="POST">
	<label>Name</label>
	<input type="text" name="name" required/>
	<label>Email</label>
	<input type="text" name="email" required/>
	<label>Password</label>
	<input type="text" name="password" required/>
	<button type="submit" name="register">Register</button>
</form>

<?php require(__DIR__ . "/../View/footer.html"); ?>
