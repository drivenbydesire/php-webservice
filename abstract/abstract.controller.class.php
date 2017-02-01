<?php
/**
 *
 */
require_once(DIR_MODEL.MODEL_CLASS);

abstract class Controller
{
  protected $db;
  function __construct(){
    # code...
    $this->db = new OnlyModel();
    echo ' importing Controller... \n';
  }
}

?>
