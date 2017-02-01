<?php
# Include config defined constants
//require_once('config/def.constants.php');
require_once(DIR_ABSTRACT.ABS_WEBSERVICE_CLASS);

/**
 *
 */
class WebService extends AbsWebservice
{
  public function __construct(){
    parent::__construct();
  }
  
  private function __clone() {}
  
  public function test(){
    var_dump($this->ctrl);
    //echo 'Testing Webservice Method... \n';
  }

}
