<?php
# Uncomment / Comment next line to give /remove permission for direct script file access
# exit('No direct script access allowed');

define('DEFAULT_TIMEZONE', 'Asia/Kolkata');
date_default_timezone_set(DEFAULT_TIMEZONE);

define('BASE_URL', 'http://localhost/php-webservice');
define('APP_URL', 'http://localhost/php-webservice');

define('DIR_SERVICES','service/');
define('WEBSERVICE_TEST','only.webservice.class.php');

define('DIR_ABSTRACT','abstract/');
define('DIR_MODEL','model/');
define('DIR_INTERFACE','interface/');
define('ABS_WEBSERVICE_CLASS','abstract.webservice.class.php');
define('ABS_MODEL_CLASS','abstract.model.class.php');
define('MODEL_CLASS','only.model.class.php');
define('INF_MODEL_CLASS','interface.model.crud.class.php');

define('DIR_CONFIG','config/');
define('DEF_DATABASE','def.database.php');
define('ABS_CONTROLLER_CLASS','abstract.controller.class.php');

define('DIR_CONTROLLER','controller/');
define('CONTROLLER_CLASS','controller.example.class.php');

define('DIR_SLIM','libs/Slim/');
define('SLIM_CLASS','Slim.php');

define('REQUIRE_SLIM_CLASS','Slim/Slim.php');
/*
# define constant, serialize array
define ("FRUITS", serialize (array (0=>"apple", 'test'=>"cherry", -1=>"banana")));

# use it
$my_fruits = unserialize (FRUITS);
print_r(FRUITS);
echo "<br><hr><br>";
print_r($my_fruits);
*/
?>
