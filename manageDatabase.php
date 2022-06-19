<?php

class ManageDatabase {
	private $db;

	function __construct() {
		$DB_HOST = "localhost";
		$DB_NAME = getenv("USER");
		$DB_USER = getenv("USER");
		$DB_PASSWORD = null;

		$DB_OBJ_OPTIONS = [
			PDO::ATTR_EMULATE_PREPARES   => false, 
			//turn on errors in the form of exceptions
    		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, 
			//make the default fetch be an anonymous object with column names as properties
    		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ, 
		];

		//Connect to database
		try {
			$this->$db = new PDO("pgsql:host=" . $DB_HOST . ";dbname=" . $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_OBJ_OPTIONS);
			echo "Connection successful\n";
		} catch (PDOException $e) {
    		echo "Connection failed:\n" . $e->getMessage();
		}

		//Create database unless it already exists
		$create_db_commands = file_get_contents(__DIR__ . "/designDatabase.sql");
		try {
			$this->$db->exec($create_db_commands);
			echo "Database created\n";
		} catch (PDOException $e) {
			if (substr($e->getMessage(), 0, 15) === "SQLSTATE[42P07]") {
				echo "Database already exists\n";
			} else { echo "Error creating database:\n" . $e->getMessage(); }
		}	
	}
}

