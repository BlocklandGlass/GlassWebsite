<?php
require_once("TestManager.php"); //will include all classes we need

class JsonTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		//loads a verified user 'testuser' with blid '4833' and email 'email@email.com` and password 'asdf'
		TestManager::loadBasicDummyData();
	}

	public function testGetBoardIndex() {
		$response = include("../../private/json/getBoardIndex.php");
		$this->assertNotEquals(false, $response);
	}
}
?>
