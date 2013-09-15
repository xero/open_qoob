<?php
/**
 * logz
 * PHP logging class
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.42.01
 * @package		qoob
 * @subpackage	utils 
 */
namespace qoob\utils;
class logz {
	/**
	 * @var filename
	 */
	protected $file;
	/**
	 * @var logs directory
	 */
	protected $dir;

	/**
	 * constructor
	 * set default path and file
	 */
	function __construct() {
		$this->setup(
			\library::get('TMP.dir'), 
			'error.log'
		);
	}
	/**
	 * set directory and filename
	 *
	 * @param string $dir the server log directory
	 * @param string $file the log filename
	 */
	function setup($dir, $file) {
		$this->changeDirectory($dir);
		$this->changeFile($file);
	}
	/**
	 * set directory and filename
	 *
	 * @param string $file the log filename
	 */
	function changeFile($file) {
		$this->file = $file;
	}
	/**
	 * set directory and filename
	 *
	 * @param string $dir the server log directory
	 */
	function changeDirectory($dir) {
		if(!is_dir($dir)) {
			mkdir($dir, 0755, true);
		}
		$this->dir = $dir;
	}
	/**
	 * clear the log
	 */
	function clear() {
		file_put_contents($this->dir.DIRECTORY_SEPARATOR.$this->file, '');
	}
	/**
	 * delete the log
	 */
	function destroy() {
		@unlink($this->dir.DIRECTORY_SEPARATOR.$this->file);
	}
	/**
	 * write to log
	 * output in "date (uri) [ip] message" format
	 *
	 * @param string $data the content to be written
	 * @param string $dateFormat php style date format string (default = 'r')
	 */
	function write($data, $dateFormat='r') {
		file_put_contents(
			$this->dir.DIRECTORY_SEPARATOR.$this->file, 
			date($dateFormat).' ('.$this->getURI().') ['.$this->getIP().'] '.$data.PHP_EOL, 
			FILE_APPEND | LOCK_EX
		);
	}
	/**
	 * get the number of lines in a log file
	 *
	 * @param int $line line number
	 * @return string log entry
	 */
	function read($lineNumber=1) {
		$linecount = 1;
		$handle = fopen($this->dir.DIRECTORY_SEPARATOR.$this->file, "r");
		while(!feof($handle)){
			$line = fgets($handle);
			if($linecount == $lineNumber) {
				break;
			}
			$linecount++;
		}
		fclose($handle);
		return $line;
	}	
	/**
	 * get ipaddress of current user
	 *
	 * @return string ipaddress
	 */
	function getIP() {
		if(getenv('HTTP_X_FORWARDED_FOR') != '') {
		    return getenv('HTTP_X_FORWARDED_FOR');
		} else {
		    return getenv('REMOTE_ADDR');
		}
	}
	/**
	 * get uri of current request
	 *
	 * @return string uri
	 */
	function getURI() {
		return 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	}
	/**
	 * get the number of lines in a log file
	 *
	 * @return int line count
	 */
	function count() {
		$linecount = 0;
		$handle = fopen($this->dir.DIRECTORY_SEPARATOR.$this->file, "r");
		while(!feof($handle)){
			$line = fgets($handle);
			$linecount++;
		}
		fclose($handle);
		$line = null;
		return $linecount-1; //last line is empty
	}
}
?>