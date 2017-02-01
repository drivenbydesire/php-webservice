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

  public function fetchLocations(){
      return $this->fetchAll('parent_child');
  }
}



// $test = new OnlyModel();
// var_dump($test);
?>
