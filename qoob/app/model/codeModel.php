<?php

namespace model;
class codeModel extends \qoob\core\db\mysql {
	protected $qoob;

	function __construct() {
		$this->qoob = \qoob::open();
		$this->qoob->benchmark->mark('mysqlConnectStart');
		$this->init(
			\library::get('CONFIG.DB.host'), 
			\library::get('CONFIG.DB.user'), 
			\library::get('CONFIG.DB.pass'), 
			\library::get('CONFIG.DB.name')
		);
		$this->connect();
		$this->qoob->benchmark->mark('mysqlConnectEnd');
	}
	public function listCode() {
		$this->qoob->benchmark->mark('mysqlQueryStart');
		$result = $this->query(
			"SELECT * FROM  `code` LIMIT :limit, :offset;",
			array(
				'limit' => 0,
				'offset' => 30
			)
		);
		$this->qoob->benchmark->mark('mysqlQueryEnd');
		return $result;		
	}
}

?>