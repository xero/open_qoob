<?php
/**
 * new relic
 * debugging class for the new relic php agent
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	0.01.01
 * @package		qoob
 * @subpackage	utils 
 * @see 		https://newrelic.com/docs/php/the-php-api
 */
namespace qoob\utils;
class newrelic {
	function name($name = 'unknown', $start = true) {
	    if (extension_loaded('newrelic')) {
	        if($start) {
	            newrelic_name_transaction($name);
	        } else {
	            newrelic_end_of_transaction();
	        }
	    }
	}
}
?>