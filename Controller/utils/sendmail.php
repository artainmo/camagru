<?php
/*
 * Install PHPmailer with two following shell commands:
 	* curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
 	* composer require phpmailer/phpmailer
 * If composer.json already exists, run in same directory as this file and composer.json 'composer install' and composer.lock and vendor will be created
 * Define PHPmailer with the following 3 'use' and 1 'require' statements
*/
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;
require 'vendor/autoload.php';


function sendMail($toMail, $toName, $subject, $content) {
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->Mailer = "smtp";
	//$mail->SMTPDebug  = 1;

	//hotmail is used because gmail and icloud do not accept application connection for security reasons
	$mail->Host = 'smtp-mail.outlook.com';
	$mail->SMTPSecure = 'tls';
	$mail->Port = 587;
	$mail->SMTPAuth = true;
	$mail->Username = 'camagru19@hotmail.com'; 
	$mail->Password = '$camagru123';

	$mail->AddAddress($toMail, $toName);
	$mail->SetFrom("camagru19@hotmail.com", "Camagru");
	$mail->Subject = $subject;
	$mail->IsHTML(true);
	$mail->Body = $content;

	if ($mail->send()){
    	return 'SUCCESS';
	} else {
    	return $mail->ErrorInfo;
	}
}

//Example -> echo sendMail("tainmontarthur@icloud.com", "tainmont arthur", "Test", "Test");
