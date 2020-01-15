<?php
namespace Glass;

class DatabaseManager {
	private $database;
	private $username;
	private $password;

	private $mysqli;

	function __construct() {
		//memory cached for performance
		//infinite persistence is not guaranteed, however

		if(!is_file(dirname(__DIR__) . "/config.json")) {
			throw new \Exception("Key file not found");
		} else {
			$keyData = json_decode(file_get_contents(dirname(__DIR__) . "/config.json"));
		}

		$this->database = $keyData->database;
		$this->username = $keyData->username;
		$this->password = $keyData->password;

		try {
			$this->mysqli = new \mysqli("localhost", $this->username, $this->password, $this->database);
			if($this->mysqli->connect_errno !== 0) {
				throw new \Exception("error");
			}
		} catch(Exception $e) {
			$this->mysqli = new mysqli("localhost", $this->username, $this->password);

			if($this->mysqli->connect_error) {
				throw new \Exception("Failed to connect to localhost with provided credentials: " . $this->mysqli->connect_error);
			}

			if(!$this->mysqli->select_db($this->database)) {
				if(!($this->query("CREATE DATABASE IF NOT EXISTS `" . $this->sanitize($this->database) . "`") &&
					$this->query("USE `" . $this->sanitize($this->database) . "`"))) {
					throw new \Exception("Unable to start database: " .  $this->mysqli->connect_error);
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

	public function update($table, $identifiers, $values, $time = null) {
		$sql = "UPDATE `" . $table . "` SET ";
		$first = true;
		foreach($values as $key=>$value) {
			if(!$first) {
				$sql .= ", ";
			} else {
				$first = false;
			}

      if($value == null) {
        $sql .= "`$key`=NULL";
      } else {
        $sql .= "`$key`='" . $this->sanitize($value) . "'";
      }
		}

		if($time != null) {
			$sql .= ", `$time`= NOW()";
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

		return $this->query($sql);
	}
}
?>
