<?php
/**
 *
 */

require_once(DIR_INTERFACE.INF_MODEL_CLASS);

abstract class Model implements CRUD
{
  /**
   * @var DB Connection Object [PDO Instance]
   */
  private $conn;

  /********************************************************************************
  * Instantiation and Configuration
  *******************************************************************************/

  /**
   * Constructor
   * @param  array $userSettings Associative array of application settings
   */
  public function __construct(){
    $this->connect();
  }

  private function connect(){
    #include_once dirname(__FILE__) . '\def.database.php';
    require_once(DIR_CONFIG.DEF_DATABASE);

    try {
			# Connecting to mysql database
			$this->conn = new PDO("mysql:host=" . DB_HOST . "; dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD);
			# set the PDO error mode to exception
			$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->conn;
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
  }

  protected function insert(){

  }

  protected function fetchAll($_tableName){
    $_qry = "SELECT * FROM `$_tableName`";
    $stmt = $this->conn->prepare($_qry);
    $execution = $stmt->execute(array());
    $result    = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ($execution)? $result: NULL;
  }
}
