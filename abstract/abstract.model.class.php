<?php
/**
 *
 */

require_once(DIR_INTERFACE.INF_MODEL_CLASS);

abstract class Model implements CRUD
{
  # db object
  private $conn;

  public function __construct(){
    $this->connect();
  }

  private function connect(){
    #include_once dirname(__FILE__) . '\def.database.php';
    require_once(DIR_CONFIG.DEF_DATABASE);

    try {
			# Connecting to mysql database
      echo 'Connecting db... \n';
			$this->conn = new PDO("mysql:host=" . DB_HOST . "; dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
			# set the PDO error mode to exception
			# $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->conn;
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
  }

  protected function insert(){

  }
}
?>