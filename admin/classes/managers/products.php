<?php
require_once(__DIR__ . "/base_table.php");

class Products extends BaseTable
{
    public function __construct()
    {
        parent::__construct("products");
    }


    function get_total_pages()
    {
        $limit = 10;
        $rows = $this->get_all();
        return intval(round(count($rows) / $limit));
    }

    function get_products_on_page($page = 1)
    {
        if ($page < 1) $page = 1;
        $limit = 10;
        $offset = $limit * ($page - 1);
        if ($offset < 0) $offset = 0;

        $sql = "SELECT * FROM `products` limit $limit offset $offset";

        $conn = $this->get_con();
        if (!$conn) {
            return $this->set_last_error("Connection does not exists");
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();

        return $data;
    }

    function search($q)
    {
        $q = $this->clean_string($q);

        $sql = "SELECT * FROM `products` 
        WHERE gw_code LIKE '%$q%' OR 
        content LIKE '%$q%' OR customer_price_single like '%$q%'";

        $conn = $this->get_con();
        if (!$conn) {
            return $this->set_last_error("Connection does not exists");
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();

        return $data;
    }
	
	function get_json()
	{
		$rows = $this->get_all();
		return json_encode($rows);
	}
	
	function get_json_column($column)
	{
		$rows = $this->get_all();
		$data = [];
		foreach($rows as $row) 
		{
			$name = $this->deformat_html($row[$column]);
			array_push($data, $name);
		}
		
		return json_encode($data);
	}
}