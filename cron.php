<?php
include(__DIR__."/vendor/autoload.php");
include(__DIR__."/config/config.php");

$db = new \Easy\PDOW\PDOW();
$db->setTyp("mysql");
$db->setUsername($mysql["user"]);
$db->setServer($mysql["host"]);
$db->setDatabase($mysql["db"]);
$db->setPassword($mysql["pw"]);
unset($mysql);
$db->createConnection();
$db->createStatic();

$res = $db->query("SELECT * FROM `mails` WHERE `status` = ?", array("waiting"));
foreach($res as $r)
{
	$mail = new \Byte\Mailer\Mail($r["id"]);
	include("config/mailer.php");

	$mailer->addAddress($mail->getTo());               // Name is optional

	$mailer->isHTML(false);                                  // Set email format to HTML

	$mailer->Subject = $mail->getSubject();
	$mailer->Body    = $mail->getBody();
	#$mailer->AltBody = $mail->getBody();

	if(!$mailer->send()) {
	    echo 'Message could not be sent.';
	    echo 'Mailer Error: ' . $mailer->ErrorInfo;
	    $db->insert('UPDATE `mails` SET `status`="failed" WHERE `id` = ?', array($mail->getID()));
	} else {
	    echo 'Message has been sent';
	    $db->insert('UPDATE `mails` SET `status`="success" WHERE `id` = ?', array($mail->getID()));
	}
}
