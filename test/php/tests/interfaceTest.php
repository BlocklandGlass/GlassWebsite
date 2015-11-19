<?php
//setup instructions:
//http://codeception.com/11-12-2013/working-with-phpunit-and-selenium-webdriver.html

//reference:
//http://facebook.github.io/php-webdriver/classes/RemoteWebDriver.html

require_once("TestManager.php"); //will include all classes we need
require "vendor/facebook/webdriver/lib/__init__.php";
use PHPUnit_Extensions_Selenium2TestCase_Keys as KEYS;

class InterfaceTest extends PHPUnit_Framework_TestCase {
	private static $webDriver;
	private $driver;
	private static $process;

	//we aren't changing data, so only load it once
	public static function setUpBeforeClass() {
		InterfaceTest::$process = proc_open("java \"-Dwebdriver.chrome.driver=res/chromedriver.exe\" -jar res/selenium-server-standalone-2.48.2.jar", [["pipe", "r"], ["pipe", "w"]], $pipe);

		if(InterfaceTest::$process === false) {
			throw new Exception("Failed to start selenium server");
		}
		//loads a verified user 'testuser' with blid '4833' and email 'email@email.com` and password 'asdf'
		TestManager::loadBasicDummyData();
		InterfaceTest::$webDriver = RemoteWebDriver::create("http://localhost:4444/wd/hub", DesiredCapabilities::chrome());
	}

	public static function tearDownAfterClass() {
		InterfaceTest::$webDriver->quit();
		//$status = proc_get_status(InterfaceTest::$process);
		//exec("taskkill /pid " . $status['pid'] . " /F");

		//alternatively, navigate to http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer
		file_get_contents("http://localhost:4444/selenium-server/driver/?cmd=shutDownSeleniumServer");
	}

	protected function setUp() {
		$this->driver = InterfaceTest::$webDriver;
	}

	protected function tearDown() {
		//nothing yet
	}

	public function testHomePage() {
		$this->driver->get('http://localhost:80/index.php');
		$this->assertEquals("Blockland Glass", $this->driver->getTitle());
	}

	public function testNavBar() {
		$this->driver->get('http://localhost:80/index.php');
		$menu = $this->driver->findElement(WebDriverBy::id('navcontent'))->findElement(WebDriverBy::tagName('ul'));

		//assert that we are not in mobile mode when maximized
		$this->driver->manage()->window()->maximize();
		$this->assertFalse(strpos($menu->getAttribute("class"), "mobilemenu"));

		//assert that we are in mobile mode with a small window
		$this->driver->manage()->window()->setSize(new WebDriverDimension(450, 600));
		$this->driver->wait(2, 250)->until(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className("mobilemenu")));
		$this->assertContains("mobilemenu", $menu->getAttribute("class"));

		//assert that reverting back clears mobile mode
		$this->driver->manage()->window()->maximize();
		//$this->driver->wait(2, 250)->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::presenceOfElementLocated(WebDriverBy::className("mobilemenu"))));
		sleep(1);
		$this->assertFalse(strpos($menu->getAttribute("class"), "mobilemenu"));
	}

	public function testLogInAndOut() {
		$this->driver->get('http://localhost:80/addons/boards.php');
		$button = $this->driver->findElement(WebDriverBy::id("navcontent"))->findElement(WebDriverBy::linkText("Log In"));
		$button->click();
		$this->assertContains("login.php", $this->driver->getCurrentURL());
		//$element1 = $this->driver->getActiveElement();
		$element1 = $this->driver->findElement(WebDriverBy::name("username"));
		$this->assertEquals("input", $element1->getTagName());
		//$this->assertEquals("username", $element1->getAttribute("name"));
		$element1->sendKeys("4833");
		//$element1->sendKeys(KEYS::TAB);
		//$element2 = $this->driver->getActiveElement();
		$element2 = $this->driver->findElement(WebDriverBy::name("password"));
		$this->assertEquals("input", $element1->getTagName());
		//$this->assertEquals("password", $element1->getAttribute("name"));
		$element2->sendKeys("asdf");
		$title = $this->driver->getTitle();
		$element2->submit();
		$this->driver->wait(5, 250)->until(WebDriverExpectedCondition::not(WebDriverExpectedCondition::titleIs($title)));
		$this->assertContains("boards.php", $this->driver->getCurrentURL());
	}
}
?>
