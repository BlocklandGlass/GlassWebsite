<?php
require_once("TestManager.php"); //will include all classes we need

class UserTest extends PHPUnit_Framework_TestCase {
	protected function setUp() {
		//loads a verified user 'testuser' with blid '4833' and email 'email@email.com` and password 'asdf'
		TestManager::loadBasicDummyData();
	}

	public function testRegisterSuccess() {
		$response = UserManager::register('otherguy@email.com', 'asdf', 'asdf', '1234');
		$this->assertTrue(isset($response['redirect']));
		$this->assertEquals("/login.php", $response['redirect']);
	}

	public function testRegisterFailEmail1() {
		$response = UserManager::register('email@email.com', 'asdf', 'asdf', '1234');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("That E-mail address is already in use.", $response['message']);
	}

	public function testRegisterFailEmail2() {
		$response = UserManager::register('notanEmail', 'asdf', 'asdf', '1234');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("Invalid e-mail address", $response['message']);
	}

	public function testRegisterFailBLID1() {
		$response = UserManager::register('otherguy@email.com', 'asdf', 'asdf', '4833');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("That BL_ID is already in use! Contact administration if you believe this is a mistake.", $response['message']);
	}

	public function testRegisterFailBLID2() {
		$response = UserManager::register('otherguy@email.com', 'asdf', 'asdf', 'blid');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("INVALID BL_ID", $response['message']);
	}

	public function testRegisterFailPassword1() {
		$response = UserManager::register('otherguy@email.com', 'a', 'a', '1234');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("Your password must be at least 4 characters.", $response['message']);
	}

	public function testRegisterFailPassword2() {
		$response = UserManager::register('otherguy@email.com', 'asdf', 'fdsa', 'blid');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("Your passwords do not match.", $response['message']);
	}

	public function testLoginSuccess1() {
		$response = UserManager::login('4833', 'asdf');
		$this->assertTrue(isset($response['redirect']));
		$this->assertEquals("/index.php", $response['redirect']);
	}

	public function testLoginSuccess2() {
		$response = UserManager::login('email@email.com', 'asdf');
		$this->assertTrue(isset($response['redirect']));
		$this->assertEquals("/index.php", $response['redirect']);
	}

	public function testLoginFail1() {
		$this->testRegisterSuccess();
		$response = UserManager::login('4833', 'fdsa');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("Incorrect login credentials", $response['message']);
	}

	public function testLoginFail2() {
		$this->testRegisterSuccess();
		$response = UserManager::login('1234', 'asdf');
		$this->assertTrue(isset($response['message']));
		$this->assertEquals("This BL_ID has not been verified yet, please use your E-mail instead", $response['message']);
	}

	public function testLoginSuccess3() {
		$this->testRegisterSuccess();
		$response = UserManager::login('otherguy@email.com', 'asdf');
		$this->assertTrue(isset($response['redirect']));
		$this->assertEquals("/index.php", $response['redirect']);
	}

	public function testGetFromBLID1() {
		$response = UserManager::getFromBLID(4833);
		$this->assertNotEquals(false, $response);
		$this->assertEquals(4833, $response->getBLID());
		$this->assertEquals('testuser', $response->getUserName());
	}

	public function testGetFromBLIDFail() {
		$response = UserManager::getFromBLID(4321);
		$this->assertEquals(false, $response);
	}

	public function testGetFromBLID2() {
		$this->testRegisterSuccess();
		$response = UserManager::getFromBLID(1234);
		$this->assertNotEquals(false, $response);
		$this->assertEquals(1234, $response->getBLID());
	}
}
?>
