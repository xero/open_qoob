<?php
//______________________________________________________________________________
//                                                                framework init
$qoob = require('qoob/qoob.php');
// load a config file
$qoob->config('qoob/app/config.ini.php');
//______________________________________________________________________________
//                                                                    add routes
// closure style callbacks 
$qoob->route('GET /', function() {
	echo '<h1>open qoob</h1><p>this is the default page.</p>';
});
$qoob->route('GET /home/:sometime', function($args) {
	echo '<h1>open qoob</h1><p>this is the home method.<h3>args:</h3><pre>'.print_r($args, true).'</pre></p>';
});
// class->method style callbacks (using namespaces)
$qoob->route('GET /things/going', 'app\test->blah');
$qoob->route('GET /date/:month/:day/:year', 'app\test->dating');
$qoob->route('GET /static', 'app\test::staticMethod');
// callbacks with different request methods (without namespaces)
$qoob->route('GET /home [sync]', 'request_types->sync');
$qoob->route('GET /home [ajax]', 'request_types->ajax');
// database test
$qoob->route('GET /model', 'app\test->modelTest');
// view test
$qoob->route('GET /template', 'app\test->templateTest');
//______________________________________________________________________________
//                                                                       execute
$qoob->run();
?>