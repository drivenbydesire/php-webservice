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
  
  public function fetchLocations(){
    $_res = $this->model->fetchLocations();
//    foreach($_res as $row){
//        $res['item'][] = $row['item'];
//        $res['parent'][] = $row['parent'];
//    }
    return json_encode($_res);
  }
}
