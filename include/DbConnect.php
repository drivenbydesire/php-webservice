<?php

/**
 * Handling database connection
 *
 * @author Sayantan Roy
 */
class DbConnect
{

	private $conn;

	function __construct()
	{
	}

	/**
	 * Establishing database connection
	 * @return database connection handler
	 */
	function connect()
	{
		include_once dirname(__FILE__) . '/Config.php';
		try {
			// Connecting to mysql database
			$this->conn = new PDO("mysql:host=" . DB_HOST . "; dbname=" . DB_NAME, DB_USERNAME, DB_PASSWORD, array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'utf8'"));
			// set the PDO error mode to exception
			// $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
			return $this->conn;
		} catch (PDOException $e) {
			echo "Connection failed: " . $e->getMessage();
		}
	}

}


?>