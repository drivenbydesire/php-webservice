<?php
require_once('config/def.constants.php');
require_once(DIR_SERVICES.WEBSERVICE_TEST);
echo 2/0;

require 'Slim/Slim.php';

\Slim\Slim::registerAutoloader();
$app = new \Slim\Slim();

$app->get('/test', function() use ($app){
    $response = array();
    $service = new WebService();
    $test = $service->test();
    $response["error"] = false;
    $response['message'] = "Testing the Webserver Class";
    echoRespnse(400, $response);
});

$app->get('/locations', function() use ($app)
{
    $response = array();
    $service = new WebService();
    $test = $service->test();
    $response["error"] = false;
    $response['message'] = "Testing the Webserver Class";
    echoRespnse(400, $response);
});

/**
 * Echoing json response to client
 * @param String $status_code Http response code
 * @param Int $response Json response
 */
function echoRespnse($status_code, $response)
{
    $app = \Slim\Slim::getInstance();
    // Http response code
    $app->status($status_code);

    // setting response content type to json
    $app->contentType('application/json');

    echo json_encode($response);
}

function debugEcho($debug){
    $app = \Slim\Slim::getInstance();
    echo $debug;
}

$app->run();
?>
