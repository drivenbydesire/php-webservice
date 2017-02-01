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
  
  public function get_test(){
    $data = $this->ctrl->fetchData();
    $response["error"]      = false;
    $response['message']    = "Testing the Webserver Class Test GET";
    $response["result"]     = json_decode($data);
    $this->sendResponse(200, $response);
  }
  
  public function post_test(){
    $_params = $this->getParamsJSON();
    print_r($_params);
    $data = $this->ctrl->fetchData();
    $response["error"]      = false;
    $response['message']    = "Testing the Webserver Class Test POST";
    $response["result"]     = json_decode($data);
    $this->sendResponse(200, $response);
  }
}
