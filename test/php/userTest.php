<?php
require_once("../../private/class/databaseManager.php");

class UserTest extends PHPUnit_Framework_TestCase {
	public function testRegister() {
		//user registration system will change from current format to an email based one
		//code from register.php, login.php, and logout.php will all be merged into a static class
		//no point writing tests for old system now
	}
}
?>
