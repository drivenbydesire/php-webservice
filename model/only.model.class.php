<?php
/**
 *
 */

require_once(DIR_ABSTRACT.ABS_MODEL_CLASS);

class OnlyModel extends Model
{

  function __construct(){
    parent::__construct();
  }

  public function fetchDataAll(){
      return $this->fetchAll('parent_child');
  }
}
