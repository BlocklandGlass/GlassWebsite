<?php

class DatabaseManager {
	private $database;
	private $username;
	private $password;

	private $mysqli;

	function __construct() {
		//memory cached for performance
		//infinite persistence is not guaranteed, however
		$keyData = apc_fetch('mysqlKey');

		if($keyData === false) {
			if(!is_file(dirname(__FILE__) . "/key.json")) {
				throw new Exception("Key file not found");
			} else {
				$keyData = json_decode(file_get_contents(dirname(__FILE__) . "/key.json"));
				apc_store('mysqlKey', $keyData);
			}
		}
		$this->database = $keyData->database;
		$this->username = $keyData->username;
		$this->password = $keyData->password;
		$this->mysqli = new mysqli("localhost", $this->username, $this->password, $this->database);

		if($this->mysqli->connect_error) {
			throw new Exception("Unable to connect to database: " .  $this->mysqli->connect_error);
		}
	}

	function __destruct() {
		$this->mysqli->close();
	}

	public function fetchMysqli() {
		//this should be used extremely rarely
		return $this->mysqli;
	}

	public function query($sql) {
		return $this->mysqli->query($sql);
	}

	public function sanitize($sql) {
		return $this->mysqli->real_escape_string($sql);
	}

	public function error() {
		return $this->mysqli->error;
	}
}
?>
