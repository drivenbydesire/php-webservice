<?php
# Include config defined constants
//require_once('config/def.constants.php');
require_once(DIR_ABSTRACT.ABS_WEBSERVICE_CLASS);

/**
 *
 */
class WebService extends AbsWebservice
{
  public function __construct($app){
    parent::__construct($app);
  }
  
  private function __clone() {}
  
  public function test(){
    var_dump($this->ctrl);
    //echo 'Testing Webservice Method... \n';
  }
  
  public function get_test(){
    $response["error"]      = false;
    $response['message']    = "Testing the Webserver Class Test GET";
    $this->sendResponse(200, $response);
  }
  
  public function get_locations(){
    $data = $this->ctrl->fetchLocations();
    $response["error"]      = false;
    $response['message']    = "Testing the Webserver Class Locations GET";
    $response["result"]     = json_decode($data);
    $this->sendResponse(200, $response);
  }
}
