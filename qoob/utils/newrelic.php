<?php
/**
 * new relic
 * debugging class for the new relic php agent
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	0.02.01
 * @package		qoob
 * @subpackage	utils 
 * @see 		https://newrelic.com/docs/php/the-php-api
 */
namespace qoob\utils;
class newrelic {
	/**
	 * name
	 * set the name of the transaction
	 * @param string $name name displayed in new relic
	 * @param boolean $start start or end this named transaction
	 */
	function name($name = 'unknown', $start = true) {
		if (extension_loaded('newrelic')) {
			if($start) {
				newrelic_name_transaction($name);
			} else {
				newrelic_end_of_transaction();
			}
		}
	}
	/**
	 * disable
	 * prevents the output filter from inserting RUM javascript for a transaction
	 * @return string
	 */
	function disable() {
		if (extension_loaded('newrelic')) {
			newrelic_disable_autorum();
		}
	}
	/**
	 * header
	 * returns the javascript string to inject into the header for browser timing
	 * @return string
	 */
	function header() {
		if (extension_loaded('newrelic')) {
			return newrelic_get_browser_timing_header();
		}
	}
	/**
	 * header
	 * returns the javascript string to inject into the footer for browser timing
	 * @return string
	 */
	function footer() {
		if (extension_loaded('newrelic')) {
			return newrelic_get_browser_timing_footer();
		}
	}
}
?>