<?php

namespace app\model;
class codeModel extends \qoob\core\db\mysql {
	protected $qoob;

	function __construct() {
		$this->qoob = \qoob::open();
		$this->qoob->benchmark->mark('mysqlConnectStart');
		$this->init(
			\library::get('CONFIG.DB.host'), 
			\library::get('CONFIG.DB.user'), 
			\library::get('CONFIG.DB.pass'), 
			\library::get('CONFIG.DB.name'),
			true,
			true
		);
		$this->connect();
		$this->qoob->benchmark->mark('mysqlConnectEnd');
	}
	public function listCode($limit, $offset) {
		return $this->query(
			"SELECT * FROM  `code` LIMIT :offset, :limit;",
			array(
				'limit' => $limit,
				'offset' => $offset
			)
		);
	}
	public function listAll() {
		$this->query(
			"SELECT * FROM  `code`;",
			array(),
			false, //dont return results
			true   //count rows
		);
		return $this->num_rows();
	}
	public function addCode($key, $val) {
		$this->query(
			"INSERT INTO `qoob`.`code` (`code_id`, `key`, `val`) VALUES (NULL, ':key', ':val');",
			array(
				'key' => $key,
				'val' => $val
			),
			false
		);
	}
	public function getID() {
		return $this->insertID();
	}
}

?>