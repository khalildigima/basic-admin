<?php 
require_once(__DIR__ . "/base_table.php");

class PasswordVerify extends BaseTable {
	
	public function __construct() {
		parent::__construct("password_verify");
	}

	
	
}