<?php

class DbOperations {

    private $conn;

    function __construct() {
        include_once dirname(__FILE__) . '/DbConnect.php';
        $db = new DbConnect();
        $this->conn = $db->connect();
    }
    
    /* ------------- USERS TABLE OEPRATIONS ------------- */

    function registerUser($name, $email, $password) {
        // encrypt the password
        $password_hash = $this->getEncryptedPassword($password);

        // check for email conflict
        if ($this->isEmailRegistered($email)) {
            return USER_ALREADY_EXISTS;
        }

        // add user
        $stmt = $this->conn->prepare("INSERT INTO `users`(`name`, `email`, `password_hash`) VALUES(?, ?, ?)");
        $stmt->bind_param("sss", $name, $email, $password_hash);
        if ($stmt->execute()) {
            return USER_CREATED_SUCCESSFULLY;
        } else {
            return FAILED_TO_CREATE_USER;
        }
    }

    /**
     * Checks whether the given email id already exists in the users table or not.
     * @param String $email - User's email id
     */
    private function isEmailRegistered($email) {
        $stmt = $this->conn->prepare("SELECT `id` FROM `users` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        return $num_rows > 0;
    }

    private function getEncryptedPassword($password) {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    /* ------------- END USERS TABLE OEPRATIONS ------------- */

}

?>