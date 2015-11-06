<?php
require_once("../../private/class/databaseManager.php");

class DatabaseTest extends PHPUnit_Framework_TestCase {
	public function testError() {
		$database = new DatabaseManager();
		$resource = $database->query("SELECT `garbage` FROM `thingthatdoesnotexist`");
		$this->assertEquals(false, $resource);
		$this->assertNotEquals("", $database->error());
	}

	public function testBasicQuery() {
		$database = new DatabaseManager();
		$resource = $database->query("SHOW TABLES");
		$this->assertNotEquals(false, $resource);
	}
}
?>
