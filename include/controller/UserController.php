<?php

/**
 * This class handles the users related operations.
 */
class UserController {

    /**
     * Register new user in the database.
     */
    function register($request, $response) {
        // check required params
        if (!hasRequiredParams(array('name', 'email', 'password'), $response)) {
            return;
        }

        // reading post params
        $request_data = $request->getParams();
        $name = $request_data['name'];
        $email = $request_data['email'];
        $password = $request_data['password'];

        // check for invalid email address
        if (!isValidEmail($email, $response)) {
            return;
        }

        // register user
        $db = new DbOperations();
        $result = $db->registerUser($name, $email, $password);

        if ($result == USER_CREATED_SUCCESSFULLY) {
            $message['error'] = false;
            $message['message'] = "User registered successfully";
            return buildResponse(201, $message, $response);
        } else if ($result == FAILED_TO_CREATE_USER) {
            $message['error'] = true;
            $message['message'] = "Problem registering user. Please try again later";
            return buildResponse(200, $message, $response);
        } else {
            $message['error'] = true;
            $message['message'] = "User with this email address already exists. Please try again";
            return buildResponse(409, $message, $response);
        }
    }

    /**
     * Authenticates the user by verifying email and password match in the database.
     */
    function login($request, $response, $args) {
        // check required params
        if (!hasRequiredParams(array('email', 'password'), $response)) {
            return;
        }
    
        // reading post params
        $request_data = $request->getParams();
        $email = $request_data['email'];
        $password = $request_data['password'];
    
        // authenticate user
        $db = new DbOperations();
        $result = $db->loginUser($email, $password);
    
        if ($result == USER_AUTHENTICATED) {
            // get user details
            $user = $db->getUserByEmail($email);
            if ($user != null) {
                $user_details = array();
                $user_details['id'] = $user['id'];
                $user_details['name'] = $user['name'];
                $user_details['email'] = $user['email'];
                $user_details['password_hash'] = $user['password_hash'];
                $user_details['api_key'] = $user['api_key'];
                $user_details['created_at'] = $user['created_at'];
                
                $message['error'] = false;
                $message['user'] = $user_details;
                return buildResponse(200, $message, $response);
            } else {
                $message['error'] = true;
                $message['message'] = "User not found";
                return buildResponse(404, $message, $response);
            }
        } else if ($result == USER_AUTHENTICATION_FAILURE) {
            $message['error'] = true;
            $message['message'] = "Failed to authenticate user due to wrong credentials. Please try again";
            return buildResponse(401, $message, $response);
        } else {
            $message['error'] = true;
            $message['message'] = "User not found";
            return buildResponse(404, $message, $response);
        }
    }

}

?>