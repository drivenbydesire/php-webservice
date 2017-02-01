<?php
/**
 *
 */
require_once(DIR_ABSTRACT.ABS_CONTROLLER_CLASS);

class Admin extends Controller{

  function __construct(){
    parent::__construct();
  }

  private function testDB(){
    var_dump($this->db);
  }
}
