<?php

namespace app;
class test {
	protected $qoob;

	function __construct() {
		$this->qoob = \qoob::open();
	}

	function blah() {
		echo "<h1>open qoob</h1><p>blah test method.</p>";
	}
	function dating($args) {
		echo '<h1>open qoob</h1><p>date test method.<pre>'.print_r($args, true)."</pre></p>";
	}
	static function staticMethod($args) {
		echo '<h1>open qoob</h1><p>static method test.</p>';
	}
	function modelTest(){
		$this->qoob->benchmark->mark('modelLoadStart');
		$this->qoob->load('model\codeModel');
		$this->qoob->benchmark->mark('modelLoadEnd');

		$result = $this->qoob->codeModel->listCode();
		echo "<pre>".print_r($result, true)."</pre>";
	}
	function templateTest() {
		$url = \library::get('QOOB.domain').'/';
		$this->qoob->load('qoob\core\view\stache');

		//roman numeral year
		$N = date('Y');
		$c='IVXLCDM'; 
		for($a=5,$b=$s='';$N;$b++,$a^=7) 
			for($o=$N%$a,$N=$N/$a^0;$o--;$s=$c[$o>2?$b+$N-($N&=-2)+$o=1:$b].$s);  

		$this->qoob->stache->render(
			'templateTest', 
			array(
				'author' => \library::get('CONFIG.GENERAL.author'),
				'copyright' => \library::get('CONFIG.GENERAL.copyrightHTML'),
				'keywords' => \library::get('CONFIG.GENERAL.keywords'),
				'description' => \library::get('CONFIG.GENERAL.description'),
				'domain' => $url,
				'year'=> $s, 
				'title'=> 'open qoob', 
				'bodyCopy'=>'a semi-RESTful php api framework designed to simplify and expedite the process of creating dynamic web applications.'
			)
		);
	}
}

?>