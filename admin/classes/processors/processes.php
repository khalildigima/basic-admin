<?php 

function response_message($status, $message)
{
    return array("status" => $status, "message" => $message);
}

require_once(__DIR__ . "/phpmailer/MailSender.php");
require_once(__DIR__ . "/file_upload_process.php");
require_once(__DIR__ . "/requirement_process.php");