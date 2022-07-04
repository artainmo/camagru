<?php require(__DIR__ . "/../View/header/base-header.html"); ?>

<?php
session_start();

if (isset($_POST['nameSubmit'])) {
	$name = htmlspecialchars(trim($_POST['nameInput']));

	require(__DIR__ . "/../Model/manageDatabase.php");
	$db = new ManageDatabase;
	$ret = $db->getAccount($name);
	if (isset($ret[0]) && gettype($ret[0]) === "boolean" && $ret[0] === false) {
		$error = "Internal server error occured:<br/>" . $ret[1];
	} elseif (count($ret) === 0) {
		$nameAlert = "This username does not exist.";
	} else {
		require(__DIR__ . "/utils/crypting.php");
        $token = encrypt_decrypt("name=${name}&email={$ret[0]->email}&password={$ret[0]->password}");
		require(__DIR__ . "/utils/sendmail.php");
		sendMail($ret[0]->email, $name, "Reset Camagru Password",
			"Click on the following button to reset your password: " .
			"<button><a href=" .
			"'http://localhost:8000/passwordReset.php?${token}'" .
			">Verify</a></button>");
		echo "<p class='title'>We send you an email to reset your password.</p>";
		exit();
	}
}

if (isset($_SERVER['QUERY_STRING'])) {
	require(__DIR__ . "/utils/crypting.php");
	$decrypted_token = encrypt_decrypt($_SERVER['QUERY_STRING'], 'decrypt');
	parse_str($decrypted_token, $query);
	if (!isset($query["name"]) or !isset($query["email"]) or !isset($query["password"])) {
    	echo "Not able to reset your password.";
    	exit();
	}
	require(__DIR__ . "/../Model/manageDatabase.php");
    $db = new ManageDatabase;
    $ret = $db->getAccount($query['name']);
    if (isset($ret[0]) && gettype($ret[0]) === "boolean" && $ret[0] === false) {
			echo "Internal server error occured:<br/>" . $ret[1];
			exit();
    } elseif (count($ret) === 0) {
			echo "Not able to reset your password.";
			exit();
		} else if ($ret[0]->email !== $query['email'] or $ret[0]->password !== $query['password']) {
			echo "Not able to reset your password.";
			exit();
		} else {
			$_SESSION['account'] = $query['name'];
	}
}

if (isset($_POST['newPasswordSubmit'])) {
	$password = htmlspecialchars(trim($_POST['newPasswordInput']));

    if (!preg_match('~[0-9]+~', $password) || !preg_match('~[a-z]+~', $password)
		 		|| strlen($password) < 5) {
    	$passwordAlert = "Password must have a minimal length of 5 characters,
                contain at least one number and lower case character.";
    } else {
		require(__DIR__ . "/../Model/manageDatabase.php");
    	$db = new ManageDatabase;
    	$ret = $db->updateAccount($_SESSION['account'], "password", $password);
    	if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
        	$error = "Internal server error occured:<br/>" . $ret[1];
		} else { header("Location: http://localhost:8000/login.php"); }
	}
}

?>

<div class="centerForm">
	<h1>Reset password</h1><br><br>
	<?php if (!isset($_SERVER['QUERY_STRING'])) { ?>
	<form action="passwordReset.php" method="POST">
		<label>Username:</label><br/>
		<input type="text" name="nameInput" maxlength="20" required/><br/>
		<?php if (isset($nameAlert)) {echo $nameAlert . "<br/>";} ?><br/>
		<button type="submit" name="nameSubmit">Submit</button><br/><br/>
		<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>
	</form>
	<?php } else { ?>
	<form action="passwordReset.php" method="POST">
		<label>New password:</label><br/>
		<input type="password" name="newPasswordInput" maxlength="20" required/><br/>
		<?php if (isset($passwordAlert)) {echo $passwordAlert . "<br/>";} ?><br/>
		<button type="submit" name="newPasswordSubmit">Submit</button><br/><br/>
		<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>
	</form>
	<?php } ?>
</div>
