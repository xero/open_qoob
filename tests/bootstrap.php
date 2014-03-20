<?php
/**
 * qoob test base class
 * bootstraps the framework into a single variable to avoid phpunit redefinition errors
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.3.0
 */
class qoobTest extends PHPUnit_Framework_TestCase {
	public 
		$qoob = null;

	public function __construct() {
		//instanciate the qoob
		$this->qoob = (!class_exists('qoob')) 
			? include('qoob/qoob.php')
			: qoob::open();

		//set server defaults
		$this->forgeRequest(
			'localhost',
			'/qoob/',
			'/qoob/index.php',
			'GET',
			''
		);
	}
	public function forgeRequest($host, $uri, $script, $request, $query, $ajax = false) {
		if (PHP_SAPI=='cli') {
			$_SERVER['HTTP_HOST'] = $host;
			$_SERVER['REQUEST_URI'] = $uri;
			$_SERVER["SCRIPT_NAME"] = $script;
			$_SERVER['REQUEST_METHOD'] = $request;
			$_SERVER['QUERY_STRING'] = $query;
			if($ajax) {
				$_SERVER['HTTP_X_REQUESTED_WITH']='xmlhttprequest';
			}
		}		
	}
}
class MockPhpStream{
	protected $index = 0;
	protected $length = null;
	protected $data = 'hello';

	public $context;

	function __construct(){
		if(file_exists($this->buffer_filename())){
			$this->data = file_get_contents($this->buffer_filename());
		}else{
			$this->data = '';
		}
		$this->index = 0;
		$this->length = strlen($this->data);
	}

	protected function buffer_filename(){
		return sys_get_temp_dir().'\php_input.txt';
	}

	function stream_open($path, $mode, $options, &$opened_path){
		return true;
	}

	function stream_close(){}

	function stream_stat(){
		return array();
	}

	function stream_flush(){
		return true;
	}

	function stream_read($count){
		if(is_null($this->length) === TRUE){
			$this->length = strlen($this->data);
		}
		$length = min($count, $this->length - $this->index);
		$data = substr($this->data, $this->index);
		$this->index = $this->index + $length;
		return $data;
	}

	function stream_eof(){
		return ($this->index >= $this->length ? TRUE : FALSE);
	}

	function stream_write($data){
		return file_put_contents($this->buffer_filename(), $data);
	}

	function unlink(){
		if(file_exists($this->buffer_filename())){
			unlink($this->buffer_filename());
		}
		$this->data = '';
		$this->index = 0;
		$this->length = 0;
	}
}
?>