<?php
session_start();

class Base {

    public function clean_string($str) {
        $s = "";
        $s = htmlspecialchars($str, ENT_QUOTES);
        return $s;
    }

    public function clean_array($arr) {
        foreach($arr as $key=>$value) {
            $arr[$key] = $this->clean_string($value);
        }
        
        return $arr;
    }

    public function set_success($message) { $_SESSION["success"] = $this->clean_string($message); }
    public function set_failure($message) { $_SESSION["failure"] = $this->clean_string($message); }

	public function get_success() {
		if(isset($_SESSION["success"]))
			return ($_SESSION["success"]);
			
		return null;
	}
	
	public function get_failure() {
		if(isset($_SESSION["failure"])) return($_SESSION["failure"]);
		
		return null;
	}
	
    public function unset_success() {
        if(isset($_SESSION["success"])) unset($_SESSION["success"]);
    }

    public function unset_failure() {
        if(isset($_SESSION["failure"])) unset($_SESSION["failure"]);
    }
	
	public function format_date($date, $format = "d M Y")
	{
		$date_created = date_create($date);
		return date_format($date_created, $format);
	}
	
	// DANGEROUS FUNCTION
	public function deformat_html($text)
	{
		$text = str_replace("&lt;", "<", $text);
		$text = str_replace("&gt;", ">", $text);
		$text = str_replace("&amp;", "&", $text);
		
		return $text;	
		
	}
}