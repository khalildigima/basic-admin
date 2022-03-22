<?php 

Class BaseProcess
{
    function response_message($status, $message)
    {
        if($status == "failure")
        {
            $_SESSION["failure"] = $message;
        }
        else if($status == "success")
        {
            $_SESSION["success"] = $message;
        }
        return array("status" => $status, "message" => $message);
    }
}
