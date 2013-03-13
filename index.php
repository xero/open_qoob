<?php
//framework init
$qoob = require('/qoob/qoob.php');

// closure style callbacks 
$qoob->route('GET /', function() {
	echo '<h1>open qoob</h1><p>this is the default page.</p>';
});
$qoob->route('GET /home/:sometime', function($args) {
	echo '<h1>open qoob</h1><p>this is the home method.<h3>args:</h3><pre>'.print_r($args, true).'</pre></p>';
});
// class->method style callbacks
$qoob->route('GET /things/going', 'test->blah');
$qoob->route('GET /date/:month/:day/:year', 'test->dating');
// callbacks with different request methods
$qoob->route('GET /home [sync]', 'request_types->sync');
$qoob->route('GET /home [ajax]', 'request_types->ajax');

//run
$qoob->run();
?>