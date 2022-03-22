<?php

date_default_timezone_set("Asia/Kolkata");

// managers
include __DIR__ . "/../vendor/autoload.php";

require_once(__DIR__ . "/constants.php");
require_once(__DIR__ . "/user_types.php");
require_once(__DIR__ . "/users.php");

require_once(__DIR__ . "/email_verify.php");
require_once(__DIR__ . "/password_verify.php");
require_once(__DIR__ . "/reset_password.php");
require_once(__DIR__ . "/products.php");
require_once(__DIR__ . "/banner_slides.php");

$_SESSION["page"] = "";

// processors
require_once(__DIR__ . "/../processors/processes.php");

/* utitlity for checking permission */
function perm_check($arr)
{
    $type = $_SESSION["user_type_id"];
    if ($type == ADMIN) return true;

    foreach ($arr as $type_id) {
        if ($type == $type_id) return true;
    }

    return false;
}