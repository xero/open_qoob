<?php
/**
 * qoob library tests
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	0.09.02
 */
class qoob_routes extends qoobTest {	
	/**
	 * test add route - no handler
	 */
	public function testAddNoHandler() {
		$this->setExpectedException(
			'Exception',
			'Missing Callback',
			500
		);
		$this->qoob->route('GET /', null);
	}
	/**
	 * test add route - bad gateway
	 */
	public function testAddBadGateway() {
		$this->setExpectedException(
			'Exception',
			'Not implemented:',
			500
		);
		$this->qoob->route('NOREST /', function(){});
	}
	/**
	 * test add route - invalid request type
	 */
	public function testAddBadRequest() {
		$this->setExpectedException(
			'Exception',
			'Invalid request type: PJAX',
			500
		);
		$this->qoob->route('POST / [PJAX]', function(){});
	}
	/**
	 * test add route - bad gateway
	 */
	public function testAddNoPattern() {
		$this->setExpectedException(
			'Exception',
			'Invalid routing pattern:',
			500
		);
		$this->qoob->route('POST', function(){});
	}
	/**
	 * test add route - success
	 */
	public function testAddRoute() {
		$this->qoob->route('GET /', function() {
				echo json_encode(array(
					'test' => true
				));
			}
		);
		$this->assertTrue(true);
	}
	/**
	 * test add route - success
	 */
	public function testRouteParse() {
		$this->expectOutputString('{"test":true}');
		$this->qoob->run();
	}
	/**
	 * test add route - fail 404
	 */
	public function testRouteParse404() {
		$this->forgeRequest(
			'localhost',
			'/qoob/this/is/a/fake/route',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->setExpectedException(
			'Exception',
			'Not Found',
			404
		);
		$this->qoob->run();
	}
	/** 
	 * test route patterns and arguments
	 */
	public function testRoutePattern() {
		$this->qoob->route('GET /user/:name', function($args) {
				echo json_encode(array(
					'user' => $args['name']
				));
			}
		);
		$this->forgeRequest(
			'localhost',
			'/qoob/user/xero',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->expectOutputString('{"user":"xero"}');
		$this->qoob->run();
	}
	/** 
	 * test route pattern mismatch
	 */
	public function testRoutePatternMismach() {
		$this->qoob->route('GET /user/:name', function($args) {
				echo json_encode(array(
					'user' => $args['name']
				));
			}
		);
		$this->forgeRequest(
			'localhost',
			'/qoob/user/xero',
			'/qoob/index.php',
			'POST', // <--- wrong type
			''
		);
		$this->setExpectedException(
			'Exception',
			'Not Found',
			404
		);
		$this->qoob->run();
	}	
	/**
	 * test post requests
	 */
	public function testRequestPost() {
		$this->qoob->route('POST /test/post', function($args) {
				echo json_encode(array(
					'post' => isset($args['val']) ? $args['val'] : ''
				));
			}
		);
		$this->forgeRequest(
			'localhost',
			'/qoob/test/post',
			'/qoob/index.php',
			'POST',
			''
		);
		$_POST['val'] = 'hello';
		$this->expectOutputString('{"post":"hello"}');
		$this->qoob->run();
	}
	/**
	 * test put requests
	 */
/*	public function testRequestPut() {
		$this->qoob->route('PUT /test/put', function($args) {
				echo json_encode(array(
					'put' => isset($args['val']) ? $args['val'] : 'xxx'
				));
			}
		);
		$this->forgeRequest(
			'localhost',
			'/qoob/test/put',
			'/qoob/index.php',
			'PUT',
			''
		);
		stream_wrapper_unregister("php");
		stream_wrapper_register("php", "MockPhpStream");
		file_put_contents('php://input', 'val=hello');
		$this->expectOutputString('{"put":"hello"}');
		$this->qoob->run();
		stream_wrapper_restore("php");
	}
*/	/**
	 * test class call method
	 */
	public function testCall() {
		$this->qoob->route('GET /test', 'testClass->something');
		$this->forgeRequest(
			'localhost',
			'/qoob/test',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->expectOutputString('{"hello":"world"}');
		$this->qoob->run();
	}	
	/**
	 * test call method - fail
	 */
	public function testCallFail() {
		$this->qoob->route('GET /test/class/fail', 'namespace\class->method');
		$this->forgeRequest(
			'localhost',
			'/qoob/test/class/fail',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->setExpectedException(
			'Exception',
			'Not Found',
			404
		);
		$this->qoob->run();
	}
	/**
	 * test error handling - thrown
	 */
	public function testErrorThrown() {
		$this->qoob->route('GET /test/err/thrown', function() {
			throw new \Exception("Error Thrown Manually", 500);
		});
		$this->forgeRequest(
			'localhost',
			'/qoob/test/err/thrown',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->setExpectedException(
			'Exception',
			'Error Thrown Manually',
			500
		);
		$this->qoob->run();		
	}
	/**
	 * test error handling - math
	 */
	public function testErrorMath() {
		library::set('CONFIG.debug', false);
		$this->qoob->route('GET /test/err/math', function() {
			$x = 806 / 0;
		});
		$this->forgeRequest(
			'localhost',
			'/qoob/test/err/math',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->expectOutputString('{"error":2,"message":"Division by zero"}');
		$this->qoob->run();
	}
	/**
	 * test error handling - trigger
	 */
	public function testErrorTrigger() {
		library::set('CONFIG.debug', false);
		$this->qoob->route('GET /test/err/trigger', function() {
			trigger_error("Fatal error", E_USER_ERROR);
		});
		$this->forgeRequest(
			'localhost',
			'/qoob/test/err/trigger',
			'/qoob/index.php',
			'GET',
			''
		);
		$this->expectOutputString('{"error":256,"message":"Fatal error"}');
		$this->qoob->run();
	}

}
//__________________________________________________________
//                                              test classes
class testClass {
	public function something() {
		echo json_encode(array(
			'hello' => 'world'
		));
	}
}
?>