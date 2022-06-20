<?php
	if (isset($_POST['register'])) {	
		$name = htmlspecialchars(trim($_POST['name']));
		$email = htmlspecialchars(trim($_POST['email']));
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		$password = htmlspecialchars(trim($_POST['password']));

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$emailAlert = "Not a valid email.";	
		} elseif (!preg_match('~[0-9]+~', $password) || !preg_match('~[a-z]+~', $password) 
			|| strlen($password) < 5) {
			$passwordAlert = "Password must have a minimal length of 5 characters, 
				contain at least one number and lower case character.";
		} else {	
			require(__DIR__ . "/../Model/manageDatabase.php");
			$db = new ManageDatabase;
			$ret = $db->createAccount($name, $password, $email);
			if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
				if (substr($ret[1], 0, 15) === "SQLSTATE[23505]") {
					$nameAlert = "Name already in use.";
				} else { $error = "Internal server error occured:<br/>" . $ret[1]; }
			}
		}
	}
?>

<?php require(__DIR__ . "/../View/base-header.html"); ?>

<h3>Register</h3>
<form action="register.php" method="POST">
	<label>Name</label><br/>
	<input type="text" name="name" maxlength="20" required/><br/>
	<?php if (isset($nameAlert)) {echo $nameAlert . "<br/>";} ?><br/>
	<label>Email</label><br/>
	<input type="text" name="email" maxlength="40" required/><br/>
	<?php if (isset($emailAlert)) {echo $emailAlert . "<br/>";} ?><br/>
	<label>Password</label><br/>
	<input type="password" name="password" maxlength="20" required/><br/>
	<?php if (isset($passwordAlert)) {echo $passwordAlert . "<br/>";} ?><br/>
	<button type="submit" name="register">Register</button><br/><br/>
	<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>
</form>

<?php require(__DIR__ . "/../View/footer.html"); ?>
