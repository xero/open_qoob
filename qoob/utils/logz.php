<?php
/**
 * logz
 * PHP logging class
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.4
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
	 *
	 * @param string $data the content to be written
	 * @param string $dateFormat php style date format string (default = 'r')
	 */
	function write($data, $dateFormat='r') {
		file_put_contents(
			$this->dir.DIRECTORY_SEPARATOR.$this->file, 
			date($dateFormat).' ['.$this->getIP().'] '.$data.PHP_EOL, 
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
		if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) && $_SERVER['HTTP_X_FORWARTDED_FOR'] != '') {
		    return $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
		    return $_SERVER['REMOTE_ADDR'];
		}
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