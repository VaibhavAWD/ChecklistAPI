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
        $api_key = $this->generateApiKey();

        // check for email conflict
        if ($this->isEmailRegistered($email)) {
            return USER_ALREADY_EXISTS;
        }

        // add user
        $stmt = $this->conn->prepare("INSERT INTO `users`(`name`, `email`, `password_hash`, `api_key`) VALUES(?, ?, ?, ?)");
        $stmt->bind_param("ssss", $name, $email, $password_hash, $api_key);
        if ($stmt->execute()) {
            return USER_CREATED_SUCCESSFULLY;
        } else {
            return FAILED_TO_CREATE_USER;
        }
    }

    function loginUser($email, $password) {
        $stmt = $this->conn->prepare("SELECT `password_hash` FROM `users` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $stmt->bind_result($password_hash);
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        if ($num_rows > 0) {
            $stmt->fetch();
            if (password_verify($password, $password_hash)) {
                return USER_AUTHENTICATED;
            } else {
                return USER_AUTHENTICATION_FAILURE;
            }
        } else {
            return USER_NOT_FOUND;
        }
    }

    function getUserByEmail($email) {
        $stmt = $this->conn->prepare("SELECT * FROM `users` WHERE `email` = ?");
        $stmt->bind_param("s", $email);
        if ($stmt->execute()) {
            return $stmt->get_result()->fetch_assoc();
        } else {
            return null;
        }
    }

    function getUserById($user_id) {
        $stmt = $this->conn->prepare("SELECT `id` FROM `users` WHERE `id` = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        return $num_rows > 0;
    }

    /**
     * Get user id by api key
     * @param string $api_key User API key
     * @return int User Id
     */
    function getUserId($api_key) {
        $stmt = $this->conn->prepare("SELECT `id` FROM `users` WHERE `api_key` = ?");
        $stmt->bind_param("s", $api_key);
        if ($stmt->execute()) {
            $user_id = $stmt->get_result()->fetch_assoc();
            return $user_id;
        } else {
            return null;
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

    /**
     * Validate user api key
     * @param string $api_key User API key
     * @return boolean
     */
    function isValidApiKey($api_key) {
        $stmt = $this->conn->prepare("SELECT `id` FROM `users` WHERE `api_key` = ?");
        $stmt->bind_param("s", $api_key);
        $stmt->execute();
        $stmt->store_result();
        $num_rows = $stmt->num_rows;
        return $num_rows > 0;
    }

    /**
     * Generating random Unique MD5 String for user Api key
     */
    private function generateApiKey() {
        return md5(uniqid(rand(), true));
    }

    /* ------------- END USERS TABLE OEPRATIONS ------------- */

    /* ------------- ITEMS TABLE OEPRATIONS ------------- */

    function addItem($user_id, $item) {
        $stmt = $this->conn->prepare("INSERT INTO `items`(`user_id`, `item`) VALUES(?, ?)");
        $stmt->bind_param("is", $user_id, $item);
        if ($stmt->execute()) {
            return ITEM_ADDED_SUCCESSFULLY;
        } else {
            return FAILED_TO_ADD_ITEM;
        }
    }

    function getItem($user_id, $item_id) {
        $stmt = $this->conn->prepare("SELECT * FROM `items` WHERE `user_id` = ? AND `id` = ?");
        $stmt->bind_param("ii", $user_id, $item_id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    function getItems($user_id, $status) {
        if ($status == -1) {
            $stmt = $this->conn->prepare("SELECT * FROM `items` WHERE `user_id` = ? ORDER BY `created_at` DESC");
            $stmt->bind_param("i", $user_id);
        } else {
            $stmt = $this->conn->prepare("SELECT * FROM `items` WHERE `status` = ? AND `user_id` = ? ORDER BY `created_at` DESC");
            $stmt->bind_param("ii", $status, $user_id);
        }
        $stmt->execute();
        return $stmt->get_result();
    }

    function updateItem($user_id, $item_id, $item) {
        $stmt = $this->conn->prepare("UPDATE `items` SET `item` = ? WHERE `user_id` = ? AND `id` = ?");
        $stmt->bind_param("sii", $item, $user_id, $item_id);
        $stmt->execute();
        $stmt->store_result();
        $num_affected_rows = $stmt->affected_rows;
        return $num_affected_rows > 0;
    }

    function updateStatus($user_id, $item_id, $status) {
        $stmt = $this->conn->prepare("UPDATE `items` SET `status` = ? WHERE `user_id` = ? AND `id` = ?");
        $stmt->bind_param("iii", $status, $user_id, $item_id);
        $stmt->execute();
        $stmt->store_result();
        $num_affected_rows = $stmt->affected_rows;
        return $num_affected_rows > 0;
    }

    function deleteItem($user_id, $item_id) {
        $stmt = $this->conn->prepare("DELETE FROM `items` WHERE `user_id` = ? AND `id` = ?");
        $stmt->bind_param("ii", $user_id, $item_id);
        $stmt->execute();
        $stmt->store_result();
        $num_affected_rows = $stmt->affected_rows;
        return $num_affected_rows > 0;
    }

    function deleteItems($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM `items` WHERE `user_id` = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_affected_rows = $stmt->affected_rows;
        return $num_affected_rows > 0;
    }

    function deleteCompletedItems($user_id) {
        $stmt = $this->conn->prepare("DELETE FROM `items` WHERE `status` = 1 AND `user_id` = ?");
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $stmt->store_result();
        $num_affected_rows = $stmt->affected_rows;
        return $num_affected_rows > 0;
    }

    /* ------------- END ITEMS TABLE OEPRATIONS ------------- */

}

?>