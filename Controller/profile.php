<?php
  session_start();
	if (!isset($_SESSION['account'])) { header('Location: http://localhost:8000/index.php'); }
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
	} elseif (isset($_POST['emailSubmit'])) {
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
	} elseif (isset($_POST['passwordSubmit'])) {
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
	} elseif ($_SERVER['REQUEST_METHOD'] === "POST") {
		if (isset($_POST['notif']) and $_POST['notif'] === "on") {
			$notif = "True";
		} else {//If notification wants to be deactivated by unclicking checkbox POST is sent without body
			$notif = "False";
		}
		$ret = $db->updateAccount($_SESSION['account'], "picture_comment_email_notification",
		   	$notif);
		if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
			$error = "Internal server error occured:<br/>" . $ret[1];
		}
	}
?>

<?php require(__DIR__ . "/../View/header/in-app-header.php"); ?>

<div class="centerForm">
<h1>Profile</h1><br><br>
<?php $account = $db->getAccount($_SESSION['account'])[0]; ?>
<form action="profile.php" method="POST">
    <label>Change your name '<?=$account->username?>'</label><br>
	<input type="text" name="nameInput" maxlength="20" required/>
	<button type="submit" name="nameSubmit">submit</Button><br>
    <?php if (isset($nameAlert)) {echo $nameAlert . "<br>";} ?><br>
</form>
<form action="profile.php" method="POST">
    <label>Change your email '<?=$account->email?>'</label><br>
    <input type="text" name="emailInput" maxlength="40" required/>
	<button type="submit" name="emailSubmit">submit</Button><br>
	<?php if (isset($emailAlert)) {echo $emailAlert . "<br>";} ?><br>
</form>
<form action="profile.php" method="POST">
    <label>Change your password</label><br>
    <input type="password" name="passwordInput" maxlength="20" required/>
	<button type="submit" name="passwordSubmit">submit</Button><br>
	<?php if (isset($passwordAlert)) {echo $passwordAlert . "<br>";} ?><br/>
</form>
<form action="profile.php" method="POST">
	<label>Email notification on comment: </label>
	<?php if ($account->picture_comment_email_notification) { ?>
		<input type="checkbox" name="notif" onChange="this.form.submit()" checked/>
    <br><br>
	<?php } else { ?>
		<input type="checkbox" name="notif" onChange="this.form.submit()"/>
    <br><br>
	<?php } ?>
</form>
<?php if (isset($error)) {echo $error . "<br>";} ?><br>
</div>

<?php require(__DIR__ . "/../View/footer/footer.html"); ?>
