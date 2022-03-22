<?php 
require_once(__DIR__ . "/base_table.php");

class EmailVerify extends BaseTable {
	
	public function __construct() {
		parent::__construct("email_verify");
	}

	
	
}