<?php 
require_once(__DIR__ . "/base_table.php");

class ResetPassword extends BaseTable {
	
	public function __construct() {
		parent::__construct("reset_password");
	}	
}