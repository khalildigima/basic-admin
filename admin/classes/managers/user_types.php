<?php
require_once(__DIR__ . "/base_table.php");

class UserTypes extends BaseTable
{

	public function __construct()
	{
		parent::__construct("user_types");
	}

	public function get_user_type($user_type_id)
	{
		return $this->get_by("id", $user_type_id);
	}

	public function get_users_by_type($user_type_id)
	{
		$usersModel = new Users();
		return $this->get_all_by("type_id", $user_type_id);
	}
}