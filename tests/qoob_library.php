<?php
/**
 * qoob library tests
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.0.0
 */
class qoob_library extends qoobTest {
	/**
	 * test if qoob library is loaded
	 */
	public function testInit() {
		$this->assertTrue(class_exists('library'));
	}
	/**
	 * test library creation
	 */
	public function testCreate() {
		$lib = new library();
		$this->assertTrue(isset($lib));
	}
	/**
	 * test library cloning
	 */
	public function testClone() {
		$lib = new library();
		$lib::set('hello', 'world');
		$lib2 = clone $lib;
		$this->assertTrue($lib2::exists('hello'));
	}
	/**
	 * test library key exists - failure
	 */
	public function testExistsFail() {
		$this->assertFalse(library::exists('fake'));
	}
	/**
	 * test library key set
	 */
	public function testSet() {
		library::set('something', 'some value');
		$this->assertEquals(
			library::get('something'), 
			'some value'
		);
	}
	/**
	 * test library key exists - success
	 */
	public function testExistsPass() {
		$this->assertTrue(library::exists('something'));
	}
	/**
	 * test library expose method
	 */
	public function testExpose() {
		$this->assertTrue(is_string(library::expose()));
	}
	/**
	 * test library clear
	 */
	public function testClear() {
		library::clear('something');
		$this->assertFalse(library::exists('something'));
	}


}
?>