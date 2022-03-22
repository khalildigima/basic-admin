<?php
require_once(__DIR__ . "/base_table.php");

class BannerSlides extends BaseTable
{

	public function __construct()
	{
		parent::__construct("banner_slides");
	}
	
	public function get_all_by_priority_desc()
    {
        $conn = $this->get_con();
        if (!$conn) return $this->set_last_error("Connection does not exists");

        $sql = "SELECT * FROM `{$this->get_table_name()}` ORDER BY `priority` DESC;";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }
}