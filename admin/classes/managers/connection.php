<?php
require_once(__DIR__ . "/base.php");

class Connection extends Base {
    private $deploy         = null;
    private $development    = null;
    private $production     = null;
    private $con            = null;
    private $error          = null;

    private $debug          = true;
    
    public function __construct() {
        // set to true if using online hosting and false when local hosting
        $this->deploy = false;
        $this->debug = true;

        // for development or local hosting
        $this->development = array(
            "host" => "localhost",
            "username" => "root",
            "password" => "",
            "dbname" => "database"
        );
		

        // for production or online hosting
        $this->production = array(
            "host" => "localhost",
            "username" => "username",
            "password" => "Abcd@1234",
            "dbname" => "database"
        );

        $this->create_connection();
        
        return $this;

    }

    private function create_connection() {

        try {
            if(!$this->deploy) {

                // connection for offline or development
                $this->con = new PDO(
                    'mysql:host=' . $this->development["host"] .';dbname=' . $this->development["dbname"],
                    $this->development["username"], 
                    $this->development["password"]
                );
                
            } else {

                // connection for online or production
                $this->con = new PDO(
                    'mysql:host=' . $this->production["host"] .';dbname=' . $this->production["dbname"],
                    $this->production["username"], 
                    $this->production["password"]
                );
            }

            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        } catch(PDOException $e) {
            if(!$this->con) $this->set_last_error("Failed to connect: " . $e->getMessage());
        }

    }

    public function get_con() { return $this->con; }

    public function set_last_error($error) { $this->error = $error; return false; }

    public function get_last_error() { 
        if(!$this->debug) return "";
        
        return $this->error; 
    }

    /* extremely insecure and dangerous */
    public function raw_query($sql) {
        $conn = $this->get_con();
        if(!$conn) return $this->set_last_error("Connection does not exists");
        
        $stmt = $conn->prepare($sql);

        try {
            $stmt->execute();
            return $stmt;
        } catch(PDOException $e) {
            return $this->set_last_error("Failed to execute raw sql: " . $e->getMessage());
        }
    }
}