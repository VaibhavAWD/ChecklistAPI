<?php

class DbOperations {

    private $conn;

    function __construct() {
        include_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    
    /* ------------- implement your database operation functions here ------------- */

}

?>