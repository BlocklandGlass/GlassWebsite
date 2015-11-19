<?php
require_once("TestManager.php");

class DatabaseTest extends PHPUnit_Framework_TestCase {
	public function testError() {
		TestManager::clearDatabase();
		$database = new DatabaseManager();
		$resource = $database->query("SELECT `garbage` FROM `thingthatdoesnotexist`");
		$this->assertEquals(false, $resource);
		$this->assertNotEquals("", $database->error());
	}

	public function testBasicQuery() {
		TestManager::loadBasicDummyData();
		$database = new DatabaseManager();
		$resource = $database->query("SHOW TABLES");
		$this->assertNotEquals(false, $resource);
	}
}
?>
