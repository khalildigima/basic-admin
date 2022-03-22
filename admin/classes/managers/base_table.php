<?php
require_once(__DIR__ . "/connection.php");

class BaseTable extends Connection {
    private $table_name = null;

    public function __construct($table_name) {
        parent::__construct();
        $this->table_name = $table_name;
    }
	
	public function get_table_name() { return $this->table_name; }
	
    public function get_all() {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $sql = "SELECT * FROM `{$this->get_table_name()}`;";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }

    public function get_all_by($column_name, $value) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $column_name = $this->clean_string($column_name);
        $value = $this->clean_string($value);

        $sql = "SELECT * FROM `{$this->get_table_name()}` WHERE `{$column_name}`=:value;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }

    public function get_all_desc() {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $sql = "SELECT * FROM `{$this->get_table_name()}` ORDER BY `id` DESC;";
        
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }

    public function get_all_by_desc($column_name, $value) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $column_name = $this->clean_string($column_name);
        $value = $this->clean_string($value);

        $sql = "SELECT * FROM `{$this->get_table_name()}` WHERE `{$column_name}`=:value ORDER BY `id` DESC;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();
    }

    public function get_by($column_name, $value) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $column_name = $this->clean_string($column_name);
        $value = $this->clean_string($value);

        $sql = "SELECT * FROM `{$this->get_table_name()}` WHERE `{$column_name}`=:value;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        $data = $stmt->fetchAll();
        if(count($data) < 1) return null;

        return $data[0];

    }

    public function get_by_id($id) {
        return $this->get_by("id", $id);
    }

    public function get_number_of_pages($limit) {
        if($limit < 1) $limit = 1;
        $data = $this->get_all();
        $total = count($data);
        return ceil($total / $limit);
    }

    public function get_page($page, $limit = 10) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        if($page < 1) $page = 1;
        if($limit < 1) $limit = 1;
        $total_pages = $this->get_number_of_pages($limit);
        if($page > $total_pages) $page = $total_pages;

        $offset = ( $page - 1 ) * $limit;

        $sql = "SELECT * FROM `{$this->get_table_name()}` LIMIT {$limit} OFFSET {$offset};";

        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $stmt->setFetchMode(PDO::FETCH_ASSOC);

        return $stmt->fetchAll();

    }

    public function insert($arr) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $arr = $this->clean_array($arr);

        $sql = "INSERT INTO `{$this->get_table_name()}`";
        $sql = $this->get_proper_sql_for_data_insertion($sql, $arr);

        $stmt = $conn->prepare($sql);

        foreach($arr as $key=>$value) {
            $stmt->bindParam(':' . $key, $arr[$key]);
        }

        try {
            $stmt->execute();
			return $conn->lastInsertId();
        } catch(PDOException $e) {
            return $this->set_last_error("Failed to insert data: " . $e->getMessage());
        }

    }

    private function get_proper_sql_for_data_insertion($sql, $arr) {
        $column_names = "";
        $column_params = "";

        foreach($arr as $key=>$value) {
            $column_names .= "`" . $key . "`, "; // `column_n`, 
            $column_params .= ":" . $key . ", "; // :column_n, 
        }

        // (`column_1`, `column_2`, ..., `column_n`)
        $column_names = substr($column_names, 0, strlen($column_names) - 2);
        $column_names = '(' . $column_names . ')';

        // (:column_1, :column_2, ..., :column_n)
        $column_params = substr($column_params, 0, strlen($column_params) - 2);
        $column_params = '(' . $column_params . ')';

        // INSERT INTO `table_name` (`column_1`, `column_2`, ..., `column_n`) VALUES (:column_1, :column_2, ..., :column_n)
        $sql = $sql . " " . $column_names . " VALUES " . $column_params . ";";

        return $sql;
    }

    public function update_by($column_name, $column_value, $arr) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");
				
        $arr = $this->clean_array($arr);
        $column_name = $this->clean_string($column_name);
        $column_value = $this->clean_string($column_value);

        $sql = "UPDATE `{$this->get_table_name()}` SET ###data### WHERE `{$column_name}`=:column_value;";
        $sql = $this->get_proper_sql_for_updating_data($sql, $arr);
		
        $stmt = $conn->prepare($sql);

        foreach($arr as $key=>$value) {
            $stmt->bindParam(':' . $key, $arr[$key]);
        }
        $stmt->bindParam(':column_value', $column_value);

        try {
            return $stmt->execute();
        } catch(PDOException $e) {
            return $this->set_last_error("Failed to update data: " . $e->getMessage());
        }

    }

    private function get_proper_sql_for_updating_data($sql, $arr) {

        $data = "";
        foreach($arr as $key=>$value) {
            $data .= "`" . $key . "` = :" . $key . ", ";
        }

        $data = substr($data, 0, strlen($data) - 2);

        $sql = str_replace("###data###", $data, $sql);

        return $sql;
    }

    public function update($id, $arr) {
        return $this->update_by("id", $id, $arr);
    }

    public function delete($id) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $id = $this->clean_string($id);

        $sql = "DELETE FROM `{$this->get_table_name()}` WHERE `id`=:id;";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }

    public function delete_all_by($column_name, $value) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");

        $column_name = $this->clean_string($column_name);
        $value = $this->clean_string($value);

        $sql = "DELETE FROM `{$this->get_table_name()}` WHERE `{$column_name}`=:value";

        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':value', $value);
        $stmt->execute();

        return $stmt->rowCount() > 0;
    }
}