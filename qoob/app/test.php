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
		$this->qoob->load('app\model\codeModel');
		$this->qoob->benchmark->mark('modelLoadEnd');

		$this->qoob->benchmark->mark('modelAddStart');
		$this->qoob->codeModel->addCode('hello', 'world');
		$this->qoob->benchmark->mark('modelAddEnd');

		$this->qoob->benchmark->mark('modelIdStart');
		$id = $this->qoob->codeModel->getID();		
		$this->qoob->benchmark->mark('modelIdend');
		echo 'added new id: '.$id.'<br/>';

		$this->qoob->benchmark->mark('modelCountStart');
		$count = $this->qoob->codeModel->listAll();
		$this->qoob->benchmark->mark('modelCountEnd');
		echo $count.' total rows in the table<br/>';

		$this->qoob->benchmark->mark('modelListStart');
		$result = $this->qoob->codeModel->listCode($count, 0);
		$this->qoob->benchmark->mark('modelListEnd');
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