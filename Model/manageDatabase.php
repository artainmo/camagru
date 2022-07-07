<?php

class ManageDatabase {
	private $db;

	function __construct() {
		$DB_HOST = "localhost"; //getenv('DB_HOST')
		$DB_NAME = getenv("USER"); //getenv('DB_NAME')
		$DB_USER = getenv("USER"); //getenv('DB_USER')
		$DB_PASSWORD = //getenv('DB_PASSWORD')

		$DB_OBJ_OPTIONS = [
			PDO::ATTR_EMULATE_PREPARES => false,
			//turn on errors in the form of exceptions
    	PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
			//make the default fetch be an anonymous object with column names as properties
    	PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ,
		];

		//Connect to database
		try {
			$this->db = new PDO("pgsql:host=" . $DB_HOST . ";dbname=" . $DB_NAME,
				$DB_USER, $DB_PASSWORD, $DB_OBJ_OPTIONS);
			//echo "Connection successful\n";
		} catch (PDOException $e) {
    		echo "Connection to database failed.<br><br>Error Message:<br>" . $e->getMessage();
			exit();
		}

		//Create database unless it already exists
		$create_db_commands = file_get_contents(__DIR__ . "/designDatabase.sql");
		try {
			$this->db->exec($create_db_commands);
			//echo "Database created\n";
		} catch (PDOException $e) {
			if (substr($e->getMessage(), 0, 15) === "SQLSTATE[42P07]") {
				//echo "Database already exists\n";
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

	function createPicture($imagePNGData, $username) {
		$storagePath = __DIR__ . "/../View/public/pictures/" . uniqid(rand(), true) . '.png';
		$creationTime = date('Y-m-d H:i:s');
		$ret = $this->execSqlParams("INSERT INTO pictures (storagePath, creationTime, account_id)" .
			"VALUES (?, ?, ?)", [$storagePath, $creationTime, $username]);
		if (!isset($ret[0]) or $ret[0] !== false) {
			file_put_contents($storagePath, $imagePNGData);
		}
		return $ret;
	}

	function getPictures() {
		$i = 0;
		$ret = $this->db->query("SELECT * FROM pictures ORDER BY creationTime DESC")->fetchAll();

		while (count($ret) > $i) {
			$ret[$i]->imageData = file_get_contents($ret[$i]->storagepath);
			$i++;
		}
		return $ret;
	}

	function getPicturesOfUser($username) {
		$i = 0;
		$ret = $this->execSqlParams("SELECT * FROM pictures WHERE account_id=? " .
			"ORDER BY creationTime DESC", [$username]);

		while (count($ret) > $i) {
			$ret[$i]->imageData = file_get_contents($ret[$i]->storagepath);
			$i++;
		}
		return $ret;
	}

	function getPicture($storagePath) {
		$ret = $this->execSqlParams("SELECT * FROM pictures WHERE storagePath=?", [$storagePath]);
		if (!(isset($ret[0]) and $ret[0] === false) and count($ret) !== 0) {
			$ret[0]->imageData = file_get_contents($storagePath);
		}
		return $ret;
	}

	function deletePicture($storagePath) {
		$ret = $this->execSqlParams("DELETE FROM pictures WHERE storagePath=?", [$storagePath]);
		if (!isset($ret[0]) or $ret[0] !== false) {
			unlink($storagePath);
		}
		return $ret;
	}

	function createLike($liker, $picture) {
		return $this->execSqlParams("INSERT INTO likes (liker_id, picture_id, time) VALUES (?, ?, ?)",
									[$liker, $picture, date('Y-m-d H:i:s')]);
	}

	function getILiked($liker, $picture) {
		$ret = $this->execSqlParams("SELECT * FROM likes WHERE liker_id=? AND picture_id=?"
			, [$liker, $picture]);
		if (isset($ret[0]) and $ret[0] === false) { return false; }
		return (count($ret) === 0) ? false : true;
	}

	function getLikesOfPicture($picture) {
		return $this->execSqlParams("SELECT * FROM likes WHERE picture_id=?", [$picture]);
	}

	function deleteLike($liker, $picture) {
		return $this->execSqlParams("DELETE FROM likes WHERE liker_id=? AND picture_id=?", [$liker, $picture]);
	}

	function deleteLikesOfPicture($picId) {
		return $this->execSqlParams("DELETE FROM likes WHERE picture_id=?", [$picId]);
	}

	function createCommentAndSendNotification($commenter, $picture, $content) {
		$ret = $this->execSqlParams("INSERT INTO comments (commenter_id, picture_id, content, time)
					VALUES (?, ?, ?, ?)", [$commenter, $picture, $content, date('Y-m-d H:i:s')]);
		if (isset($ret[0]) && gettype($ret[0]) === "boolean" && $ret[0] === false) {
			return $ret;
		}
		$AccountOfPicture = ($this->getPicture($picture))[0]->account_id;
		$AccountOfPicture = $this->getAccount($AccountOfPicture);
		if ($AccountOfPicture[0]->picture_comment_email_notification === false) { return $ret; }
		require(__DIR__ . "/../Controller/utils/sendmail.php");
		$picture = substr($picture, strrpos($picture, '/') - strlen($picture) + 1);
		sendMail($AccountOfPicture[0]->email, $AccountOfPicture[0]->username,
			"New Comment On Picture", "The following comment was made by ${commenter} " .
			"on one of your pictures.<br><br>Comment:<br>${content}<br><br>");
			//"Click on the following button to see the new comment: " .
            //"<button><a href=" .
			//"'http://localhost:8000/view-picture.php?picId=${picture}'" . The link does not work because user first has to connect
            //">Verify</a></button>");
		return $ret;
	}

	function getCommentsOfPicture($picture) {
		return $this->execSqlParams("SELECT * FROM comments WHERE picture_id=? " .
			"ORDER BY time DESC", [$picture]);
	}

	function deleteComment($id) {
		return $this->execSqlParams("DELETE FROM comments WHERE id=?", [$id]);
	}

	function deleteCommentsOfPicture($picId) {
		return $this->execSqlParams("DELETE FROM comments WHERE picture_id=?", [$picId]);
	}
}
