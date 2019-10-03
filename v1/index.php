<?php
require '../libs/vendor/autoload.php';
require_once '../include/DbOperations.php';

$app = new Slim\App();

$message = array();

/**
 * Testing purpose endpoint
 * endpoint - /hello/{name}
 * method - POST
 * arguments - name
 * result - displays name with suffix hello
 */
$app->post('/hello/{name}', function($request, $response, $args) {
    return $response->write("Hello, " . $args["name"]);
});

/* ------------------- USERS TABLE API -------------------------- */

/**
 * Register new user
 * 
 * endpoint - /register
 * method - POST
 * params - name, email, password
 */
$app->post('/register', function($request, $response, $args) {
    // check required params
    if (!hasRequiredParams(array('name', 'email', 'password'), $response)) {
        return $response;
    }

    // reading post params
    $request_data = $request->getParams();
    $name = $request_data['name'];
    $email = $request_data['email'];
    $password = $request_data['password'];

    // check for invalid email address
    if (!isValidEmail($email, $response)) {
        return $response;
    }

    // register user
    $db = new DbOperations();
    $result = $db->registerUser($name, $email, $password);

    if ($result == USER_CREATED_SUCCESSFULLY) {
        $message['error'] = false;
        $message['message'] = "User registered successfully";
    } else if ($result == FAILED_TO_CREATE_USER) {
        $message['error'] = true;
        $message['message'] = "Problem registering user. Please try again later";
    } else {
        $message['error'] = true;
        $message['message'] = "User with this email address already exists. Please try again";
    }

    return buildResponse(200, $message, $response);
});

/**
 * Authenticate user
 * 
 * endpoint - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', function($request, $response, $args) {
    // check required params
    if (!hasRequiredParams(array('email', 'password'), $response)) {
        return $response;
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
            $user_details['created_at'] = $user['created_at'];
            
            $message['error'] = false;
            $message['user'] = $user_details;
        } else {
            $message['error'] = true;
            $message['message'] = "User not found";
        }
    } else if ($result == USER_AUTHENTICATION_FAILURE) {
        $message['error'] = true;
        $message['message'] = "Failed to authenticate user due to wrong credentials. Please try again";
    } else {
        $message['error'] = true;
        $message['message'] = "User not found";
    }

    return buildResponse(200, $message, $response);
});

/* ------------------- END USERS TABLE API -------------------------- */

/* ------------------- ITEMS TABLE API -------------------------- */

$app->post('/items', function($request, $response, $args) {
    // check required params
    if (!hasRequiredParams(array('user_id', 'item'), $response)) {
        return $response;
    }

    // reading post params
    $request_data = $request->getParams();
    $user_id = $request_data['user_id'];
    $item = $request_data['item'];

    // check user with this user_id exists
    $db = new DbOperations();
    if (!$db->getUserById($user_id)) {
        $message['error'] = true;
        $message['message'] = "User not found";
        return buildResponse(200, $message, $response);
    }

    // add item
    $result = $db->addItem($user_id, $item);

    if ($result == ITEM_ADDED_SUCCESSFULLY) {
        $message['error'] = false;
        $message['message'] = "Item added successfully";
    } else {
        $message['error'] = true;
        $message['message'] = "Failed to add item. Please try again";
    }

    return buildResponse(200, $message, $response);
});

$app->get('/items/{id}', function($request, $response, $args) {
    $item_id = $args['id'];

    // check required params
    if (!hasRequiredParams(array('user_id'), $response)) {
        return $response;
    }

    // reading post params
    $request_data = $request->getParams();
    $user_id = $request_data['user_id'];

    // check user with this user_id exists
    $db = new DbOperations();
    if (!$db->getUserById($user_id)) {
        $message['error'] = true;
        $message['message'] = "User not found";
        return buildResponse(404, $message, $response);
    }

    $result = $db->getItem($user_id, $item_id);

    if ($result != null) {
        $item_details = array();
        $item_details['id'] = $result['id'];
        $item_details['user_id'] = $result['user_id'];
        $item_details['item'] = $result['item'];
        $item_details['created_at'] = $result['created_at'];

        $message['error'] = false;
        $message['item'] = $item_details;
        return buildResponse(200, $message, $response);
    } else {
        $message['error'] = true;
        $message['message'] = "Requested item not found";
        return buildResponse(404, $message, $response);
    }
});

$app->get('/items', function($request, $response, $args) {
    // check required params
    if (!hasRequiredParams(array('user_id'), $response)) {
        return $response;
    }

    // reading post params
    $request_data = $request->getParams();
    $user_id = $request_data['user_id'];

    // check user with this user_id exists
    $db = new DbOperations();
    if (!$db->getUserById($user_id)) {
        $message['error'] = true;
        $message['message'] = "User not found";
        return buildResponse(404, $message, $response);
    }

    // get all items associated with the user
    $result = $db->getItems($user_id);

    $message['error'] = false;
    $message['items'] = array();

    // looping through result and preparing items array
    while ($item = $result->fetch_assoc()) {
        $item_details = array();
        $item_details['id'] = $item['id'];
        $item_details['user_id'] = $item['user_id'];
        $item_details['item'] = $item['item'];
        $item_details['created_at'] = $item['created_at'];
        array_push($message['items'], $item_details);
    }

    return buildResponse(200, $message, $response);
});

$app->put('/items/{id}', function($request, $response, $args) {
    $item_id = $args['id'];

    // check required params
    if (!hasRequiredParams(array('user_id', 'item'), $response)) {
        return $response;
    }

    // reading post params
    $request_data = $request->getParams();
    $user_id = $request_data['user_id'];
    $item = $request_data['item'];

    // check user with this user_id exists
    $db = new DbOperations();
    if (!$db->getUserById($user_id)) {
        $message['error'] = true;
        $message['message'] = "User not found";
        return buildResponse(404, $message, $response);
    }

    // update item
    if ($db->updateItem($user_id, $item_id, $item)) {
        $message['error'] = false;
        $message['message'] = "Item updated successfully";
    } else {
        $message['error'] = true;
        $message['message'] = "Failed to update item. Please try again";
    }

    return buildResponse(200, $message, $response);
});

$app->delete('/items/{id}', function($request, $response, $args) {
    $item_id = $args['id'];

    // check required params
    if (!hasRequiredParams(array('user_id'), $response)) {
        return $response;
    }

    // reading post params
    $request_data = $request->getParams();
    $user_id = $request_data['user_id'];

    // check user with this user_id exists
    $db = new DbOperations();
    if (!$db->getUserById($user_id)) {
        $message['error'] = true;
        $message['message'] = "User not found";
        return buildResponse(404, $message, $response);
    }

    // delete item
    if ($db->deleteItem($user_id, $item_id)) {
        $message['error'] = false;
        $message['message'] = "Item deleted successfully";
    } else {
        $message['error'] = true;
        $message['message'] = "Failed to delete item. Please try again";
    }

    return buildResponse(200, $message, $response);
});

/* ------------------- END ITEMS TABLE API -------------------------- */

/* -------------------- HELPER FUNCTIONS ---------------------------- */
function hasRequiredParams($required_params, $response) {
    $error = false;
    $error_params = "";
    $request_params = $_REQUEST;

    foreach ($required_params as $param) {
        if (!isset($request_params[$param]) || strlen(trim($request_params[$param])) <= 0) {
            $error = true;
            $error_params .= $param . ", ";
        }
    }

    if ($error) {
        $message = array();
        $message['error'] = true;
        $message['message'] = "Required param(s) " . substr($error_params, 0, -2) . " is/are missing.";
        buildResponse(400, $message, $response);
        return false;
    } else {
        return true;
    }
}

function buildResponse($status_code, $message, $response) {
    $response->withHeader('Content-type', 'application/json');
    $response->withStatus($status_code);
    $response->write(json_encode($message));
}

/**
 * Validating email address
 * @param String $email User email address
 * @return boolean
 */
function isValidEmail($email, $response) {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message["error"] = true;
        $message["message"] = "Email address is not valid";
        buildResponse(400, $message, $response);
        return false;
    } else {
        return true;
    }
}
/* -------------------- END HELPER FUNCTIONS ---------------------------- */

$app->run();
?>