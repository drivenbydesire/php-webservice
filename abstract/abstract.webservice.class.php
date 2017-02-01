<?php
/**
 * Sample Abstract Class Layer For Webservice
 */
 function webservice_ErrorHandler($code, $message, $file, $line){
     date_default_timezone_set("Asia/Kolkata");
     $timestamp = date("Y-m-d H:i:s");
     error_log("\r\n"."$ ".$timestamp." >> Error msg: ".$message.", in file ".$file.", at line number ".$line."\n",3,'logs/errors.txt');
 }

abstract class AbsWebservice
{
  protected $ctrl;
  protected $app;

  function __construct($app){ # Constructer Method
    $this->app = $app;
    $this->ctrl = $this->getNewControllerInstance();
    set_error_handler(array($this, 'fatalErrorShutdownHandler'));
    date_default_timezone_set("Asia/Kolkata");
  }

  public function fatalErrorShutdownHandler($errno, $errstr, $errfile, $errline, $errcontext){
      // fatal error
      webservice_ErrorHandler($errno, $errstr, $errfile, $errline);
  }

  protected function sendResponse($status, $response){
    $this->app->status($status);
    $this->app->contentType('application/json');
    echo json_encode($response);
  }

  protected function getParamsJSON(){
    $test = $this->app->request()->getBody();
    $test = json_decode($test);
    return $test;
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

set_error_handler('webservice_ErrorHandler');
