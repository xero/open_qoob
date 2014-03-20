<?php
/**
 * open qoob core tests
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	0.04.02
 */
class qoob_core extends qoobTest {
	/**
	 * test if open qoob framework is loaded
	 */
	public function testInit() {
		$this->assertTrue(isset($this->qoob));
	}
	/**
	 * test if open qoob singleon method
	 */
	public function testSingleton() {
		$this->assertEquals(
			get_class(qoob::open()),
			'qoob'
		);
	}
	/**
	 * test framework version
	 */
	public function testVersion() {
		$q = $this->qoob;
		$this->assertEquals(
			$q::VERSION, 
			'2.02.02'
		);
	}
	/**
	 * test class loading
	 */
	public function testLoadSuccess() {
		$this->qoob->load('qoob\utils\logz');
		$this->assertTrue(isset($this->qoob->logz));
	}
	/**
	 * test class loading failure
	 */
	public function testLoadFail() {
		$this->setExpectedException('Exception');
		$this->qoob->load('this\is\gonna\fail');
	}
	/**
	 * test class loading failure
	 */
	public function testLoadFailMessage() {
		$this->setExpectedException(
			'Exception', 
			'Failed loading: this\is\gonna\fail'
		);
		$this->qoob->load('this\is\gonna\fail');
	}
	/**
	 * test class loading failure
	 */
	public function testLoadFailCode() {
		$this->setExpectedException(
			'Exception', 
			'Failed loading: this\is\gonna\fail', 
			500
		);
		$this->qoob->load('this\is\gonna\fail');
	}
	/**
	 * test status code
	 */
	public function testStatus() {
		$this->assertEquals(
			$this->qoob->status(404),
			'Not Found'
		);
	}
	/**
	 * test status - bad code
	 */
	public function testStatusBadCode() {
		$this->assertEquals(
			$this->qoob->status(999),
			'Internal Server Error'
		);
	}
	/**
	 * test loading variables via a config file
	 */
	public function testConfigPass() {
		$this->qoob->config('qoob/app/config.ini.php');
		$this->assertEquals(
			library::get('CONFIG.GENERAL.description'),
			'the open qoob framework'
		);
	}
	/**
	 * test loading variables via a config file - bad filename
	 */
	public function testConfigFail() {
		$this->setExpectedException(
			'Exception', 
			'Failed loading: not/a/real/config', 
			500
		);
		$this->qoob->config('not/a/real/config');
	}
}
?>