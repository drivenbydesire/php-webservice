<?php
/**
 *
 */
require_once(DIR_MODEL.MODEL_CLASS);

abstract class Controller
{
  #
  protected $model;
  
  function __construct(){
    #
    $this->model = new OnlyModel();
  }
}

?>
