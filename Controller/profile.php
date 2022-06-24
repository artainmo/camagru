<?php
    session_start();
    require(__DIR__ . "/../Model/manageDatabase.php");
    $db = new ManageDatabase;

    if (isset($_POST['nameSubmit'])) {
		$name = htmlspecialchars(trim($_POST['nameInput']));

		$ret = $db->updateAccount($_SESSION['account'], "username", $name);
		if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
        	if (substr($ret[1], 0, 15) === "SQLSTATE[23505]") {
		    	$nameAlert = "Name already in use.";
			} else { $error = "Internal server error occured:<br/>" . $ret[1]; }
		} else { $_SESSION['account'] = $name; }
	}
	if (isset($_POST['emailSubmit'])) {
		$email = htmlspecialchars(trim($_POST['emailInput']));
		$email = filter_var($email, FILTER_SANITIZE_EMAIL);

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $emailAlert = "Not a valid email.";
		} else {
			$ret = $db->updateAccount($_SESSION['account'], "email", $email);		
			if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
				$error = "Internal server error occured:<br/>" . $ret[1]; 
			}
		}
	}
	if (isset($_POST['passwordSubmit'])) {
		$password = htmlspecialchars(trim($_POST['passwordInput']));

		if (!preg_match('~[0-9]+~', $password) || !preg_match('~[a-z]+~', $password)
            || strlen($password) < 5) {
            $passwordAlert = "Password must have a minimal length of 5 characters,
                contain at least one number and lower case character.";
        } else {	
			$ret = $db->updateAccount($_SESSION['account'], "password", $password);		
			if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
				$error = "Internal server error occured:<br/>" . $ret[1]; 
			}
		}
	}
?>

<?php require(__DIR__ . "/../View/header/in-app-header.php"); ?>

<h3>Profile</h3>
<?php 
	$account = $db->getAccount($_SESSION['account'])[0]; 
	echo "Name: " . $account->username . "<br/>";
	echo "Email: " . $account->email . "<br/>";
	echo "Receive email notification when someone comments on your picture: " . 
		($account->picture_comment_email_notification ? 'Yes':'No') . 
		"<br/><br/><br/>";
?>

<form action="profile.php" method="POST">
    <label>Change Name </label>
	<input type="text" name="nameInput" maxlength="20" required/>
	<button type="submit" name="nameSubmit">submit</Button><br/>
    <?php if (isset($nameAlert)) {echo $nameAlert . "<br/>";} ?><br/>
</form>
<form action="profile.php" method="POST">
    <label>Change email</label>
    <input type="text" name="emailInput" maxlength="40" required/>
	<button type="submit" name="emailSubmit">submit</Button><br/>
	<?php if (isset($emailAlert)) {echo $emailAlert . "<br/>";} ?><br/>
</form>
<form action="profile.php" method="POST">
    <label>Change password</label>
    <input type="password" name="passwordInput" maxlength="20" required/>
	<button type="submit" name="passwordSubmit">submit</Button><br/>
	<?php if (isset($passwordAlert)) {echo $passwordAlert . "<br/>";} ?><br/><br/>
</form>
<?php if (isset($error)) {echo $error . "<br/>";} ?><br/>

<?php require(__DIR__ . "/../View/footer/footer.html"); ?>