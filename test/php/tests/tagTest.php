<?php
require_once("TestManager.php"); //will include all classes we need

class TagTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		//creates a tag 'dum tag' associated with one addon
		TestManager::loadBasicDummyData();
	}

	public function testGetTag1() {
		$response = TagManager::getTagsFromAddonID(1);
		//print_r($response);
		$this->assertNotEquals(false, $response);
		$this->assertEquals(1, count($response));
		$objID = $response[0];
		$obj = TagManager::getFromID($objID);
		$this->assertEquals('dum tag', $obj->getName());
	}

	public function testGetTag2() {
		$response = TagManager::getAddonsFromTagID(1);
		$this->assertNotEquals(false, $response);
		$this->assertEquals(1, count($response));
		$obj = AddonManager::getFromID($response[0]);
		$this->assertEquals('crapy adon', $obj->getName());
	}

	public function testAddTag() {
		$response = TagManager::createTagForAddonID("new tag", "0000ff", "brokenimage", 1);
		//echo("did work");
		$this->assertTrue($response);
		$tag = TagManager::getFromID(2);
		$this->assertNotEquals(false, $tag);
		$this->assertEquals("new tag", $tag->getName());
		$this->assertEquals(1, count(TagManager::getAddonsFromTagID($tag->getID())));
		$this->assertEquals(2, count(TagManager::getTagsFromAddonID(1)));
	}

	public function testRemoveTag1() {
		$response = TagManager::removeTagIDFromAddonID(1, 1);
		$this->assertTrue($response);
		$tag = TagManager::getFromID(1);
		$this->assertFalse($tag);
		$this->assertEquals(0, count(TagManager::getTagsFromAddonID(1)));
	}

	public function testRemoveTag2() {
		$this->testAddTag();
		$response = TagManager::removeTagIDFromAddonID(1, 1);
		$this->assertTrue($response);
		$this->assertFalse(TagManager::getFromID(1));
		$tag = TagManager::getFromID(2);
		$this->assertNotEquals(false, $tag);
		$this->assertEquals("new tag", $tag->getName());
		$tags = TagManager::getTagsFromAddonID(1);
		$this->assertEquals(1, count($tags));
		$obj = TagManager::getFromID($tags[0]);
		$this->assertEquals("new tag", $obj->getName());
	}
}
?>
