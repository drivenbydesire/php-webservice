<?php
require_once('config/def.constants.php');
require_once(DIR_SERVICES.WEBSERVICE_TEST);
echo 2/0;

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->get('/test', function() use ($app){
    
    $service = new WebService($app);
    $service->get_test();
});

$app->post('/test', function() use ($app){
    
    $service = new WebService($app);
    $service->post_test();
});

$app->run();
?>
