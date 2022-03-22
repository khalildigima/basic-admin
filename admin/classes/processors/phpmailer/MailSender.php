<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

require_once __DIR__ .  '/Exception.php';
require_once __DIR__ .  '/PHPMailer.php';
require_once __DIR__ .  '/SMTP.php';


class MailSender
{
	private $from;
	private $to;
	private $message;
	private $subject;
	private $attachments = [];
	private $have_attachments = false;
	
	function __construct($from, $to, $subject, $message, $have_attachments = false, $attachments = [])
	{
		$this->from = $from;
		$this->to = $to;
		$this->subject = $subject;
		$this->message = $message;
		
		
		if($have_attachments)
		{
			$this->have_attachments = true;
			$this->attachments = $attachments;
		}
		
		return $this;
	}
	
	function send()
	{
		$mail = new PHPMailer(true);
		
		try {
			//Server settings
			$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
			$mail->isSMTP();                                            // Send using SMTP
			$mail->Host       = 'localhost';                 // Set the SMTP server to send through
			$mail->SMTPAuth = false;                           // Enable SMTP authentication
			//$mail->Username   = 'noreply@janaushadhalay.com';               // SMTP username
			//$mail->Password   = '?xs0wioGMTEB';                             // SMTP password
			$mail->SMTPAutoTLS = false;
			//$mail->SMTPSecure = 'ssl'; //PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` encouraged
			//$mail->Port       = 25;                                    // TCP port to connect to, use 465 for `PHPMailer::ENCRYPTION_SMTPS` above

			//Recipients
			$mail->setFrom('noreply@janaushadhalay.com', 'Jan Aushadhalay');
			$mail->addAddress($this->to);

			// Content
			//$mail->isHTML(true);                                  // Set email format to HTML
			$mail->Subject = $this->subject;
			$mail->Body    = $this->message;
			$mail->AltBody = 'HTML Email format not supported';

			// attachments
			if($this->have_attachments)
			{
				foreach($this->attachments as $att)
				{
					$mail->AddAttachment($att);
				}
			}
			$mail->send();
			//echo 'Message has been sent';
		} catch (Exception $e) {
			echo "Message could not be sent. Mailer Error: {$mail->ErrorInfo}";
		}
	}
	
	function old_send()
	{
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: <' . $this->from . '>' . "\r\n";
		return mail($this->to, $this->subject, $this->message, $headers);
	}
	
	function send_verification_mail($email)
	{
		$this->from = "contact@janaushadhalay.com";
		$this->to = $email;
		$this->subject = "Jan Aushadhalay - Email verification";
		
		$this->message = $this->email_verification_message($email);
		
		$this->send();
	}
	
	function email_verification_message($email)
	{
		$model = new EmailVerify();
		$row = $model->get_by("email", $email);
		
		if($row)
		{
			$model->delete($row["id"]);
		}
		
		$token = md5(time() . $email . "Se1as@asd");
		$arr = [];
		$arr["email"] = $email;
		$arr["token"] = $token;
		$model->insert($arr);
		
		$link = 'http:/' . '/janaushadhalay.com/verify_email.php?email=' . $email . '&token=' . $token;
		
		return 'Please click on the given link to verify your email. <a href="' . $link . '">' . $link . '</a>';
		
	}
	
	function password_recovery_mail($email)
	{
		$this->from = "noreply@janaushadhalay.com";
		$this->to = $email;
		$this->subject = "Jan Aushadhalay - Password Reset";
		
		$this->message = $this->password_reset_message($email);
		
		$this->send();
	}
	
	function password_reset_message($email)
	{
		$model = new PasswordVerify();
		$row = $model->get_by("email", $email);
		
		if($row)
		{
			$model->delete($row["id"]);
		}
		
		$token = md5(time() . $email . "Se1as@asd");
		$arr = [];
		$arr["email"] = $email;
		$arr["token"] = $token;
		$model->insert($arr);
		
		$link = 'http:/' . '/janaushadhalay.com/password_reset.php?email=' . $email .  '&token=' . $token;
		return 'Please click or paste given link in your browser to reset your password. <a href="' . $link . '">' . $link . '</a>';
		
	}
	
}