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
    var_dump($this->model);
  }

  public function fetchData(){
    $_res = $this->model->fetchDataAll();
    return json_encode($_res);
  }
}
