<?php

namespace app;
class test {
	function blah() {
		echo "<h1>open qoob</h1><p>blah test method.</p>";
	}
	function dating($args) {
		echo '<h1>open qoob</h1><p>date test method.<pre>'.print_r($args, true)."</pre></p>";
	}
	function modelTest(){
		$q = \qoob::open();
		
		$q->benchmark->mark('modelLoadStart');
		$q->load('model\codeModel');
		$q->benchmark->mark('modelLoadEnd');
		
		$result = $q->codeModel->listCode();
		echo "<pre>".print_r($result, true)."</pre>";
	}
}

?>