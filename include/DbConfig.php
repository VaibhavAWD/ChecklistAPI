<?php
/**
 * Db Constants
 */
define('DB_HOST', 'localhost');
define('DB_NAME', 'myapis_checklist');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');

/**
 * Db Operations helper Constants
 */
define('USER_CREATED_SUCCESSFULLY', 1);
define('FAILED_TO_CREATE_USER', 2);
define('USER_ALREADY_EXISTS', 3);

// authentication
define('USER_AUTHENTICATED', 4);
define('USER_AUTHENTICATION_FAILURE', 5);
define('USER_NOT_FOUND', 6);

?>