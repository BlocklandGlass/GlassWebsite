<?php

class DatabaseManager {
	private $database;
	private $username;
	private $password;
	private $debug;

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

		if(isset($keyData->debug) && $keyData->debug) {
			$this->debug = true;
		} else {
			$this->debug = false;
		}

		try {
			$this->mysqli = @new mysqli("localhost", $this->username, $this->password, $this->database);
			if($this->mysqli->connect_errno !== 0) {
				throw new Exception("error");
			}
		} catch(Exception $e) {
			$this->mysqli = new mysqli("localhost", $this->username, $this->password);

			if($this->mysqli->connect_error) {
				throw new Exception("Failed to connect to localhost with provided credentials: " . $this->mysqli->connect_error);
			}

			if(!$this->mysqli->select_db($this->database)) {
				if(!($this->query("CREATE DATABASE IF NOT EXISTS `" . $this->sanitize($this->database) . "`") &&
					$this->query("USE `" . $this->sanitize($this->database) . "`"))) {
					throw new Exception("Unable to start database: " .  $this->mysqli->connect_error);
				}
			}
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

	public function debug() {
		return $this->debug;
	}

	public function update($table, $identifiers, $values) {
		$sql = "UPDATE `" . $table . "` SET ";
		$first = true;
		foreach($values as $key=>$value) {
			if(!$first) {
				$sql .= ", ";
			} else {
				$first = false;
			}
			$sql .= "`$key`='" . $this->sanitize($value) . "'";
		}

		if($identifiers != null) {
			$sql .= " WHERE ";
		}

		$first = true;
		foreach($identifiers as $key=>$value) {
			if(!$first) {
				$sql .= " AND ";
			} else {
				$first = false;
			}
			$sql .= "`$key` = '" . $this->sanitize($value) . "'";
		}

		echo($sql);

		return $this->query($sql);
	}
}
?>
