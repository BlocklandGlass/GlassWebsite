<?php
class TestObjectClass {
	private $name;
	private $data;
	private $id;

	public function __construct($input) {
		$this->name = "default name";
		$this->data = "forty two";
		$this->id = $input;
	}

	public function dothing() {
		return "thing: " . $this->id;
	}
}

//these tests are inherently flaky since apc_store makes no guarantees
class CacheTest extends PHPUnit_Framework_TestCase {
	public function testDelete() {
		$this->assertTrue(apc_store('testvar', 7, 10));
		$this->assertTrue(apc_exists('testvar'));
		$this->assertTrue(apc_delete('testvar'));
		$this->assertFalse(apc_exists('testvar'));
		$this->assertFalse(apc_fetch('testvar'));
	}

	public function testBasicStoreFetchTest() {
		$this->assertTrue(apc_store('testvar', 8, 10));
		$this->assertTrue(apc_exists('testvar'));
		$testvar = apc_fetch('testvar');
		$this->assertNotEquals(false, $testvar);
		$this->assertEquals(8, $testvar);
	}

	public function testArrayStoreFetch() {
		$arr = array("a" => 8, "b" => 54, "c" => -6);
		$this->assertTrue(apc_store('testvar', $arr, 10));
		$this->assertTrue(apc_exists('testvar'));
		$testvar = apc_fetch('testvar');
		$this->assertNotEquals(false, $testvar);
		$this->assertEquals(8, $testvar["a"]);
		$this->assertEquals(54, $testvar["b"]);
		$this->assertEquals(-6, $testvar["c"]);
	}

	public function testObjectArrayStoreFetch() {
		$arr = [];
		for($i=0; $i<8; $i++) {
			$arr[] = new TestObjectClass($i);
		}
		$this->assertTrue(apc_store('testvar', $arr, 10));
		$this->assertTrue(apc_exists('testvar'));
		$testvar = apc_fetch('testvar');
		$this->assertNotEquals(false, $testvar);

		for($i=0; $i<3; $i++) {
			$testobj = $testvar[$i];
			$this->assertEquals("thing: " . $i, $testobj->dothing());
		}
	}
}
?>
