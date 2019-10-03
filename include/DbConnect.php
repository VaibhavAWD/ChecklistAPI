<?php

class DbConnect {

    private $conn;

    function connect() {
        include_once dirname(__FILE__) . '/DbConfig.php';

        $this->conn = new mysqli(DB_HOST, DB_USERNAME, DB_PASSWORD, DB_NAME);

        if (mysqli_connect_errno()) {
            echo "Could not establish database connection due to: " . mysqli_connect_error();
            return null;
        }

        return $this->conn;
    }
}

?>