<?php
/**
 * benchmark class
 * this class enables you to mark points in time and calculate the difference between them.
 * 
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.02
 * @package		qoob
 * @subpackage	utils
 */
namespace qoob\utils;
class benchmark {
	/**
	 * @var array $markers the points in time that are benchmarked
	 */
	var $markers = array();
	/**
	 * set marker
	 * save a moment in time by a name
	 *
	 * @param string $name name of the marker
	 * @return void
	 */
	function mark($name) {
		$this->markers[$name] = microtime();
	}	
	/**
	 * time difference 
	 * calculates the time difference between two marked points.
	 *
	 * @param string $point1 a particular marked point
	 * @param string $point2 a particular marked point
	 * @param int $decimals	the number of decimal places
	 * @return mixed decimal|boolean
	 */
	function diff($point1 = "", $point2 = "", $decimals = 4) {
		if ($point1 == "") {
			return false;
		}
		if (!isset($this->markers[$point1])) {
			return false;
		}
		if (!isset($this->marker[$point2])) {
			$this->markers[$point2] = microtime();
		}
		list($sm, $ss) = explode(" ", $this->markers[$point1]);
		list($em, $es) = explode(" ", $this->markers[$point2]);
		return number_format(($em + $es) - ($sm + $ss), $decimals);
	}
}
?>