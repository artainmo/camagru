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
			$this->db = new PDO("pgsql:host=" . $DB_HOST . ";dbname=" . $DB_NAME, $DB_USER, $DB_PASSWORD, $DB_OBJ_OPTIONS);
			echo "Connection successful\n";
		} catch (PDOException $e) {
    		echo "Connection failed:\n" . $e->getMessage();
		}

		//Create database unless it already exists
		$create_db_commands = file_get_contents(__DIR__ . "/designDatabase.sql");
		try {
			$this->db->exec($create_db_commands);
			echo "Database created\n";
		} catch (PDOException $e) {
			if (substr($e->getMessage(), 0, 15) === "SQLSTATE[42P07]") {
				echo "Database already exists\n";
			} else { echo "Error creating database:\n" . $e->getMessage(); }
		}	
	}
	
	private function execSqlParams($query, $params) {
		try {
			$prep = $this->db->prepare($query);
			$prep->execute($params);
			return $prep->fetchAll();
		} catch (PDOException $e) { 
			return [false, $e->getMessage()];
		}
	}

	function createAccount($username, $password, $email) {
		$encryptedPassword = password_hash($password, PASSWORD_DEFAULT);
		return $this->execSqlParams("INSERT INTO account (username, password, email) VALUES (?, ?, ?);", 
							[$username, $encryptedPassword, $email]);
	}

	function verifyPasswordAccount($username, $passwordTry) {
		$user = $this->getAccount($username);
		if (count($user) === 0) { return false; }
		return password_verify($passwordTry, $user[0]->password);
	}

	function getAccount($username) {
		return $this->execSqlParams("SELECT * FROM account WHERE username=?", [$username]);
	}

	function updateAccount($username, $columnToUpdate, $newValue) {
		if ($columnToUpdate === "password") {
			$newValue = password_hash($newValue, PASSWORD_DEFAULT);
		}
		return $this->execSqlParams("UPDATE account SET ${columnToUpdate} = ?
          WHERE username = ?;", [$newValue, $username]);
	}

	function deleteAccount($username) {
		return $this->execSqlParams("DELETE FROM account WHERE username=?", [$username]);
	}

	function createPicture($filename, $username) {
		$storagePath = "public/picture/" . $filename;
		$creationTime = date('Y-m-d H:i:s');
		return $this->execSqlParams("INSERT INTO pictures (storagePath, creationTime, account_id) VALUES (?, ?, ?)",
		   							[$storagePath, $creationTime, $username]);
	}

	function getPictures() {
		return $this->db->query("SELECT * FROM pictures")->fetchAll();
	}

	function getPicture($storagePath) {
		return $this->execSqlParams("SELECT * FROM pictures WHERE storagePath=?", [$storagePath]);
	}

	function deletePicture($storagePath) {
		return $this->execSqlParams("DELETE FROM pictures WHERE storagePath=?", [$storagePath]);
	}

	function createLike($liker, $picture) {
		return $this->execSqlParams("INSERT INTO likes (liker_id, picture_id, time) VALUES (?, ?, ?)",
									[$liker, $picture, date('Y-m-d H:i:s')]);
	}

	function getLikesOfPicture($picture) {
		return $this->execSqlParams("SELECT * FROM likes WHERE picture_id=?", [$picture]);
	}

	function deleteLike($liker, $picture) {
		return $this->execSqlParams("DELETE FROM likes WHERE liker_id=? AND picture_id=?", [$liker, $picture]);
	}
	
	function createComment($commenter, $picture, $content) {
		return $this->execSqlParams("INSERT INTO comments (commenter_id, picture_id, content, time) 
									VALUES (?, ?, ?, ?)", 
									[$commenter, $picture, $content, date('Y-m-d H:i:s')]);
	}

	function getCommentsOfPicture($picture) {
		return $this->execSqlParams("SELECT * FROM comments WHERE picture_id=?", [$picture]);
	}

	function deleteComment($id) {
		return $this->execSqlParams("DELETE FROM comments WHERE id=?", [$id]);
	}
}
