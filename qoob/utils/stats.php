<?php
/**
 * stats
 * statistical analysis class
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.0.0
 * @package		qoob
 * @subpackage	utils 
 */
namespace qoob\utils;
class stats {
	protected
			/**
			 * @var $qoob qoob reference
			 */ 
			$qoob,
			/**
			 * @var $info statistic info
			 */ 
			$info;
	/**
	 * constructor
	 * load necessary classes
	 */
	function __construct() {
		$this->qoob = \qoob::open();
		$this->qoob->load('qoob\utils\xbd');
		$this->qoob->load("qoob\utils\location\geoip");
		$this->qoob->load('app\model\statsModel');
	}
	/**
	 * destructor
	 * mine statistic data
	 */
	function __destruct() {
		//get browser info
		$this->info = $this->qoob->xbd->browser();
		//get geo location from ipaddress
		$this->info["location"] = $this->qoob->geoip->getCountry($this->info["ipaddress"]);
		//get request info
		$this->info['time'] = @time();
		$this->info['domain'] = $this->getDomain(\library::exists('QOOB.domain')?\library::get('QOOB.domain'):dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]));
		$this->info['uri'] = \library::exists('REQUEST.uri')?\library::get('REQUEST.uri'):rtrim(str_replace(dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]), '', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']), '/');
		$this->info['url_checksum'] = crc32($this->info['domain'].$this->info['uri']);
		$this->info['verb'] = \library::exists('REQUEST.verb')?\library::get('REQUEST.verb'):'unknown';
		$this->info['ajax'] = \library::exists('REQUEST.ajax')?(\library::get('REQUEST.ajax')=='AJAX'?1:0):0;
    	$this->info['status'] = \library::exists('STATUS.code')?\library::get('STATUS.code'):500;
		$this->info['referer'] = filter_var(isset($_SERVER['HTTP_REFERER'])?$_SERVER['HTTP_REFERER']:'', FILTER_VALIDATE_URL)?filter_var($_SERVER['HTTP_REFERER'], FILTER_SANITIZE_URL):'';
		$this->info['ref_domain'] = $this->getDomain($this->info['referer']);
		$this->info['ref_checksum'] = crc32($this->info['ref_domain']);
		//save info
		$this->qoob->statsModel->save($this->info);
	}
	/**
	 * get domain
	 * strip domain and tld from url
	 *
	 * @param string $url
	 * @return string domain
	 */
	function getDomain($url) {
		return preg_replace('/(^([^:]+):\/\/(www\.)?|(:\d+)?\/.*$)/', '', $url);
	}
}

?>