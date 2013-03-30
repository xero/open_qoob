<?php
class request_types {
	/**
	 * @var array $data a fake dataset
	 */
	private $data = array(
						'first_name' => 'xero',
						'last_name' => 'harrison',
						'site' => 'http://xero.nu',
						'github' => 'http://github.com/xero'
					);

	/**
	 * ajax method
	 * for asynchronous requests
	 * @return json encoded dataset 
	 */
	function ajax() {
		echo json_encode($this->data);
	}
	/**
	 * sync method
	 * for synchronous requests
	 * @return human readable dataset 
	 */
	function sync() {
		echo '<pre>'.print_r($this->data, true).'</pre>';
	}	
}
?>