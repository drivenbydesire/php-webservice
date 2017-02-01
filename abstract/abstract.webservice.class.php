<?php
/**
 * Sample Abstract Class Layer For Webservice
 */
 function vendorTGW_ErrorHandler($code, $message, $file, $line){
     date_default_timezone_set("Asia/Kolkata");
     $timestamp = date("Y-m-d H:i:s");
     error_log("\r\n"."$ ".$timestamp." >> Error msg: ".$message.", in file ".$file.", at line number ".$line."\n",3,'logs/errors.txt');
 }

abstract class AbsWebservice
{
  //protected $db;
  protected $ctrl;
  protected $app;
  
  function __construct($app){ # Constructer Method
    $this->app = $app;
    # Initialize Model
    //$this->db = $this->getNewModelInstance();
    $this->ctrl = $this->getNewControllerInstance();
    //$this->app = $this->getSlimInstance();
    set_error_handler(array($this, 'fatalErrorShutdownHandler'));
    date_default_timezone_set("Asia/Kolkata");
  }

  public function fatalErrorShutdownHandler($errno, $errstr, $errfile, $errline, $errcontext){
      // fatal error
      vendorTGW_ErrorHandler($errno, $errstr, $errfile, $errline);
  }
  
  protected function sendResponse($status, $response){
    $this->app->status($status);
    $this->app->contentType('application/json');
    echo json_encode($response);
  }
  
  protected function getParams(){
    $test = $this->app->request()->getBody();
    var_dump($test);
  }
  
  private function getNewModelInstance(){
    # Load Model Class
    require_once(DIR_MODEL.MODEL_CLASS);
    # Get New Instance Of Model
    $_conn = new OnlyModel();
    # Store Model Object in This Protected Variable
    return $_conn;
  }

  private function getNewControllerInstance(){
    # Load Controller Class
    require_once(DIR_CONTROLLER.CONTROLLER_CLASS);
    # Get New Instance Of Controller
    $_ctrl = new Admin();
    # Store Controller Object in This Protected Variable
    return $_ctrl;
  }
}

set_error_handler('vendorTGW_ErrorHandler');
