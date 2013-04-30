<?php
/**
 * stats model
 * save statistic data about a request
 *
 * @author 		xero harrison / http://xero.nu
 * @copyright 	creative commons attribution-shareAlike 3.0 Unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	1.0
 * @package		app
 * @subpackage	model
 */
namespace app\model;
class statsModel extends \qoob\core\db\mysql {

	function __construct() {
		$this->init(
			\library::get('CONFIG.DB.host'), 
			\library::get('CONFIG.DB.user'), 
			\library::get('CONFIG.DB.pass'), 
			\library::get('CONFIG.DB.name'),
			true,
			true
		);
		$this->connect();
	}
	public function save($info) {
		$this->query(
			"INSERT INTO `stats` (`auto_id`, `time`, `domain`, `uri`, `url_checksum`, `verb`, `ajax`, `status`, `referer`, `referer_domain`, `referer_checksum`, `browser`, `version`, `platform`, `type`, `useragent`, `ipaddress`, `hostname`, `location`) VALUES (NULL, :time, ':domain', ':uri', :url_checksum, ':verb', :ajax, :status, ':referer', ':ref_domain', :ref_checksum, ':browser', ':version', ':platform', ':type', ':useragent', ':ipaddress', ':hostname', ':location');",
			$info,
			false
		);
	}
}
?>