<?php
	if (isset($_POST['register'])) {
		$name = htmlspecialchars(trim($_POST['name']));
		$email = htmlspecialchars(trim($_POST['email']));
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);
		$password = htmlspecialchars(trim($_POST['password']));

		if (str_contains($name, ' ')) {
			$nameAlert = "Username cannot contain spaces.";
		} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$emailAlert = "Not a valid email.";
		} elseif (!preg_match('~[0-9]+~', $password) || !preg_match('~[a-z]+~', $password)
			|| strlen($password) < 5) {
			$passwordAlert = "Password must have a minimal length of 5 characters,<br/>
				contain at least one number and lower case character.";
		} else {
			require(__DIR__ . "/../Model/manageDatabase.php");
			$db = new ManageDatabase;
			$ret = $db->getAccount($name);
			if (isset($ret[0]) && gettype($ret[0]) === "boolean" && $ret[0] === false) {
				$error = "Internal server error occured:<br/>" . $ret[1];
			} elseif (count($ret) !== 0) {
				$nameAlert = "Name already in use.";
			} else {
				require(__DIR__ . "/utils/crypting.php");
				$token = encrypt_decrypt("name=${name}&email=${email}&password=${password}");
				require(__DIR__ . "/utils/sendmail.php");
				$ret = sendMail($email, $name, "Verify Camagru Account",
					"Click on the following button to verify your account: " .
					"<button><a href=" .
					"'http://localhost:8000/emailVerification.php?${token}'" .
					">Verify</a></button>");
				if ($ret === "SUCCESS") {
					header("Location: http://localhost:8000/emailVerification.php");
				} else { echo "Error sending verification mail: " . $ret; }
			}
		}
	}
?>

<?php require(__DIR__ . "/../View/header/connect-app-header.php"); ?>

<div class="centerForm">
	<h1>Register</h1><br><br>
	<form action="register.php" method="POST">
		<label>Username:</label><br/>
		<input type="text" name="name" maxlength="20" required/><br/>
		<?php if (isset($nameAlert)) {echo $nameAlert . "<br/>";} ?><br/>
		<label>Email:</label><br/>
		<input type="text" name="email" maxlength="40" required/><br/>
		<?php if (isset($emailAlert)) {echo $emailAlert . "<br/>";} ?><br/>
		<label>Password:</label><br/>
		<input type="password" name="password" maxlength="20" required/><br/>
		<?php if (isset($passwordAlert)) {echo $passwordAlert . "<br/>";} ?><br/>
		<button type="submit" name="register">Register</button><br/><br/>
		<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>
	</form>
</div>
