<?php
require '../libs/vendor/autoload.php';
require_once '../include/DbOperations.php';
require_once '../include/controller/AuthController.php';
require_once '../include/controller/UserController.php';
require_once '../include/controller/ItemController.php';

$app = new Slim\App();

$message = array();

$user_id = NULL;

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
$app->post('/register', \UserController::class . ':register');

/**
 * Authenticate user
 * 
 * endpoint - /login
 * method - POST
 * params - email, password
 */
$app->post('/login', \UserController::class . ':login');

/* ------------------- END USERS TABLE API -------------------------- */

/* ------------------- ITEMS TABLE API -------------------------- */

/**
 * Add new item
 * 
 * endpoint - /items
 * method - POST
 */
$app->post('/items', \ItemController::class . ':addItem')->add(\AuthController::class);

/**
 * Get single item
 * 
 * endpoint - /items/{id}
 * method - GET
 */
$app->get('/items/{id}', \ItemController::class . ':getItem')->add(\AuthController::class);

/**
 * Get items associated with the user.
 * 
 * endpoint - /items
 * method - GET
 */
$app->get('/items', \ItemController::class . ':getItems')->add(\AuthController::class);

/**
 * Update item.
 * 
 * endpoint - /items/{id}
 * method - PUT
 */
$app->put('/items/{id}', \ItemController::class . ':updateItem')->add(\AuthController::class);

/**
 * Update item status.
 * 
 * endpoint - /items/{id}/status/{code}
 * method - PUT
 */
$app->put('/items/{id}/status/{code}', \ItemController::class . ':updateStatus')->add(\AuthController::class);

/**
 * Delete single item.
 * 
 * endpoint - /items/{id}
 * method - DELETE
 */
$app->delete('/items/{id}', \ItemController::class . ':deleteItem')->add(\AuthController::class);

/**
 * Delete all items associated with the user.
 * 
 * endpoint - /items
 * method - DELETE
 */
$app->delete('/items', \ItemController::class . ':deleteItems')->add(\AuthController::class);

/**
 * Delete completed items.
 * 
 * endpoint - /clearcompleted
 * method - DELETE
 */
$app->delete('/clearcompleted', \ItemController::class . ':clearCompleted')->add(\AuthController::class);

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
    return $response;
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