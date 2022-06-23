<?php
session_start();

if (!isset($_SERVER['QUERY_STRING'])) {
	echo "We send you an email to verify your account.";
	exit();
}

require(__DIR__ . "/utils/crypting.php");
$decrypted_token = encrypt_decrypt($_SERVER['QUERY_STRING'], 'decrypt');
parse_str($decrypted_token, $query);
if (!isset($query["name"]) or !isset($query["email"]) or !isset($query["password"])) {	
	echo "Not able to verify email.";
	exit();
}

require(__DIR__ . "/../Model/manageDatabase.php");
$db = new ManageDatabase;
$ret = $db->createAccount($query['name'], $query['password'], $query['email']);
if (gettype($ret[0]) === "boolean" && $ret[0] === false) {
	echo "Problem occured while creating your account.<br>" .
		"Click <a href='http://localhost:8000/register.php'>here</a> to try again.";
	exit();
}
echo "Email verified successfully! ";
$_SESSION['account'] = $query['name'];
echo "<button onclick='window.location.href=`http://localhost:8000/profile.php`;'>Continue</button>";
