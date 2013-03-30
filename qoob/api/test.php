<?php

class test {
	function blah() {
		echo "<h1>open qoob</h1><p>blah test method.</p>";
	}
	function dating($args) {
		echo '<h1>open qoob</h1><p>date test method.<pre>'.print_r($args, true)."</pre></p>";
	}
	function dbtest(){
		$q = qoob::open();
		
		$q->benchmark->mark('mysqlLoadStart');
		$q->load('qoob\core\db\mysql');
		$q->benchmark->mark('mysqlLoadEnd');

		$q->benchmark->mark('mysqlConnectStart');
		$q->mysql->init(
			library::get('CONFIG.DB.host'), 
			library::get('CONFIG.DB.user'), 
			library::get('CONFIG.DB.pass'), 
			library::get('CONFIG.DB.name')
		);
		$q->mysql->connect();
		$q->benchmark->mark('mysqlConnectEnd');
		
		$q->benchmark->mark('mysqlQueryStart');
		$result = $q->mysql->query(
			"SELECT * FROM  `code` LIMIT :limit, :offset;",
			$patterns = array(
				'/:limit/',
				'/:offset/'
			),
			array(
				0,
				30
			)
		);
		$q->benchmark->mark('mysqlQueryEnd');
		echo "<pre>".print_r($result, true)."</pre>";
	}
}

?>