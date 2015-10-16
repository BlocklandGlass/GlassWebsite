<?php

class DatabaseManager {
	private $database;
	private $username;
	private $password;

	private $mysqli;

	function __construct() {
		if(!is_file(dirname(__FILE__) . "/mysql.json")) {
			$json = new stdClass();
			$json->database = "blocklandglass";
			$json->username = "user";
			$json->password = "default";
			$json->configured = false;
			file_put_contents(dirname(__FILE__) . "/mysql.json", json_encode($json));
			$this->sendToSetup();
		} else {
			$databaseData = json_decode(file_get_contents(dirname(__FILE__) . "/mysql.json"));

			if(!is_object($databaseData)) {
				$this->sendToSetup();
			} else if($databaseData->username == "user" && $databaseData->password == "default") {
				$this->sendToSetup();
			}

			$this->database = $databaseData->database;
			$this->username = $databaseData->username;
			$this->password = $databaseData->password;

			if(!$databaseData->configured) {
				$this->createNewDatabase();
				$databaseData->configured = true;
				file_put_contents(dirname(__FILE__) . "/mysql.json", json_encode($databaseData));
			}

			$this->mysqli = new mysqli("localhost", $this->username, $this->password, $this->database);
			if($this->mysqli->connect_error) {
				//redirect to error page?
				echo "DATABASE ERROR!"; //temp TODO
			}
		}
	}

	function __destruct() {
		$this->mysqli->close();
	}

	private function sendToSetup() {
		header('Location: /setup.php');
	}

	function createNewDatabase() {
		// TODO
		// haven't tested thoroughly
		$command = "mysql -u{$this->username} -p{$this->password} -hlocalhost < ";

		exec($command . realpath(dirname(__FILE__) . "/db.sql") , $output, $status);
	}

	public function fetchMysqli() { //this should be used extremely rarely
		return $this->mysqli;
	}

	public function query($sql) {
		return $this->mysqli->query($sql);
	}

	public function sanitize($sql) {
		return $this->mysqli->real_escape_string($sql);
	}
}
?>
