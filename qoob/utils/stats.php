<?php
/**
 * stats
 * statistical analysis class
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	0.23
 * @package		qoob
 * @subpackage	utils 
 */
namespace qoob\utils;
class stats {
	/**
	 * mine
	 * load XBD and geoip utils and save data
	 */
	function mine() {
		$q = \qoob::open();
		$q->benchmark->mark('statsStart');
		//get browser info
		$q->load('qoob\utils\xbd');
		$info = $q->xbd->browser();
		//get geo location from ipaddress
		$q->load("qoob\utils\location\geoip");
		$info["location"] = $q->geoip->getCountry($info["ipaddress"]);
		//get request info
		$info['time'] = @time();
		$info['domain'] = $this->getDomain(\library::exists('QOOB.domain')?\library::get('QOOB.domain'):dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]));
		$info['uri'] = \library::exists('REQUEST.uri')?\library::get('REQUEST.uri'):rtrim(str_replace(dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]), '', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), '/');
		$info['url_checksum'] = crc32($info['domain'].$info['uri']);
		$info['verb'] = \library::exists('REQUEST.verb')?\library::get('REQUEST.verb'):'unknown';
		$info['ajax'] = \library::exists('REQUEST.ajax')?(\library::get('REQUEST.ajax')=='AJAX'?1:0):0;
    	$info['status'] = \library::exists('STATUS.code')?\library::get('STATUS.code'):500;
		$info['referer'] = filter_var(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'', FILTER_VALIDATE_URL)?filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL):'';
		$info['ref_domain'] = $this->getDomain($info['referer']);
		$info['ref_checksum'] = crc32($info['ref_domain']);
		//save info
		$q->load('app\model\statsModel');
		$q->statsModel->save($info);
		$q->benchmark->mark('statsEnd');
	}
	function getDomain($url) {
		return preg_replace('/(^([^:]+):\/\/(www\.)?|(:\d+)?\/.*$)/', '', $url);
	}
}

?>