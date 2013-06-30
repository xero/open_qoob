<?php
/**
 * open qoob framework
 *
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	2.0.2
 */
class qoob {
	/**
	 * error constants
	 */
	const
		E_Handler='Missing Callback',
		E_Pattern='Invalid routing pattern: %s',
		E_Fatal='Fatal error: %s',
		E_Open='Unable to open: %s',
		E_Loading='Failed loading: %s',
		E_Request='Invalid reqest type: %s',
		E_Routes='No routes specified',
		E_Method='Invalid method: %s',
		E_Gateway='Not implemented: %s';
	/**
	 * request types
	 */
	const
		REQUESTS='SYNC|AJAX';
	/**
	 * http verbs
	 */
	const
		VERBS='GET|HEAD|POST|PUT|DELETE';
	/**
	 * http status codes
	 * @see http://www.rfc-editor.org/rfc/rfc2616.txt 
	 */
	const
		// informational
		HTTP_100='Continue',
		HTTP_101='Switching Protocols',
		// successful
		HTTP_200='OK',
		HTTP_201='Created',
		HTTP_202='Accepted',
		HTTP_203='Non-Authorative Information',
		HTTP_204='No Content',
		HTTP_205='Reset Content',
		HTTP_206='Partial Content',
		// redirection
		HTTP_300='Multiple Choices',
		HTTP_301='Moved Permanently',
		HTTP_302='Found',
		HTTP_303='See Other',
		HTTP_304='Not Modified',
		HTTP_305='Use Proxy',
		HTTP_307='Temporary Redirect',
		// client error
		HTTP_400='Bad Request',
		HTTP_401='Unauthorized',
		HTTP_402='Payment Required',
		HTTP_403='Forbidden',
		HTTP_404='Not Found',
		HTTP_405='Method Not Allowed',
		HTTP_406='Not Acceptable',
		HTTP_407='Proxy Authentication Required',
		HTTP_408='Request Timeout',
		HTTP_409='Conflict',
		HTTP_410='Gone',
		HTTP_411='Length Required',
		HTTP_412='Precondition Failed',
		HTTP_413='Request Entity Too Large',
		HTTP_414='Request-URI Too Long',
		HTTP_415='Unsupported Media Type',
		HTTP_416='Requested Range Not Satisfiable',
		HTTP_417='Exception Failed',
		// server error
		HTTP_500='Internal Server Error',
		HTTP_501='Not Implemented',
		HTTP_502='Bad Gateway',
		HTTP_503='Service Unavailable',
		HTTP_504='Gateway Timeout',
		HTTP_505='HTTP Version Not Supported';

	/**
	 * status
	 * echo http header and return status message
	 * @param int $code status code
	 * @return string status message
	 */
	function status($code) {
		// account for system thrown exceptions
		if(!defined('self::HTTP_'.$code)) {
			$code = 500;
		}
		// no commandline headers
		if (PHP_SAPI!='cli') {
			header('HTTP/1.1 '.$code);
		}
		library::set('STATUS.code', $code);
		return @constant('self::HTTP_'.$code);
	}
	/**
	 * config
	 * parse a php ini into the library
	 * @param string $file file name
	 */
	function config($file) {
		if(!file_exists($file)) {
			throw new Exception(sprintf(self::E_Loading, $file), 500);	
		} 
		$ini = parse_ini_file($file, true);
		if(count($ini)>0) {
			foreach ($ini as $key => $val) {
				if(is_array($val)) {
					foreach ($val as $k => $v) {
						library::set('CONFIG.'.strtoupper($key).'.'.$k, $v);
					}
				} else {
					library::set('CONFIG.'.$key, $val);
				}
			}
		}
	}
	/**
	 * load
	 * load namespace aware classes into the framework
	 * @param string $class class name
	 */
	function load($class) {
		// nullbyte poisoning check
		$class = str_replace(chr(0), '', $class);
		if(class_exists($class)) {
			// remove namespace from class name
			$name = explode('\\', $class);
			$name = $name[count($name)-1];
			if(!library::exists($name)) {
				// create class and set a reference to it
				library::set('CLASS.'.$name, new $class);
				$this->$name = library::get('CLASS.'.$name);
			}
		} else {
			throw new Exception(sprintf(self::E_Loading, $class), 500);			
		}
	}
	/**
	 * route
	 * add a route pattern
	 * @param string $pattern route
	 * @param mixed $handler closure function or class->method reference
	 */
	function route($pattern, $handler) {
		if(empty($handler)) {
			throw new Exception($self::E_Handler, 500);
		}
		$parts = explode(' ', trim($pattern));
		foreach ($this->split($parts[0]) as $verb) {
			if (!preg_match('/'.self::VERBS.'/', strtoupper($verb))) {
				throw new Exception(sprintf(self::E_Gateway, $verb), 500);
			}
			if(!isset($parts[1])) {
				throw new Exception(sprintf(self::E_Pattern, $pattern), 500);
			}
			$type = isset($parts[2])?str_replace(array('[',']'), '', strtoupper($parts[2])):'SYNC';
			if (!preg_match('/'.self::REQUESTS.'/', $type)) {
				throw new Exception(sprintf(self::E_Request, $type), 500);
			}
			library::set(
				'ROUTES', 
				array(
					'verb' => strtoupper($verb),
					'type' => $type,
					'handler' => $handler,
					'pattern' => rtrim($parts[1], '/')
				)
			);
		}		
	}
	/**
	 * parse routes
	 * mine the current request against the routes in the library
	 */
	function parseRoutes() {
		$this->benchmark->mark('parseStart');
    	library::set('QOOB.url', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    	library::set('QOOB.domain', 'http://'.dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]));
    	library::set('REQUEST.verb', $_SERVER['REQUEST_METHOD']);
		library::set('REQUEST.uri', rtrim(str_replace('?'.$_SERVER['QUERY_STRING'], '', str_replace(library::get('QOOB.domain'), '', library::get('QOOB.url'))), '/'));
    	library::set('REQUEST.ajax', (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest')?'AJAX':'SYNC');
    	$found = false;
		foreach(library::get('ROUTES') as  $route) {
			// regular expression to identify uri requests formatted like '/users/:uid/posts/:pid'
			$pattern = "@^".preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route['pattern']))."$@D";
			$args = array();
			// check if the current request matches the expression
			if(library::get('REQUEST.verb') == $route['verb'] && preg_match($pattern, library::get('REQUEST.uri'), $matches)) {
				if($route['type'] == library::get('REQUEST.ajax')) {
					// remove the first match
					array_shift($matches);
					$found = true;
					// correlate route regex with uri parameters
					preg_match_all('/\\\:[a-zA-Z0-9\_\-]+/', preg_quote($route['pattern']), $names);
					for($i=0;$i<count($names[0]);$i++) {
						$args[str_replace('\:', '', $names[0][$i])] = isset($matches[$i])?$matches[$i]:'';
					}
					// get and merge request and uri arguments
					$requests = $this->parseRequest(library::get('REQUEST.verb'));
					$args = array_merge_recursive($requests, $args);
					break;					
				}
			}
		}
		$this->benchmark->mark('parseEnd');		
		if(!$found) {
			throw new Exception(self::HTTP_404, 404);
		} else {
			$this->call($route, $args);
		}
	}
	/**
	 * call
	 * execute a route handler
	 * @param array $route route information
	 * @param array $args url arguments
	 */
	function call($route, $args) {
		$this->benchmark->mark('callStart');
		// closure style
		if(is_callable($route['handler'])) {
			call_user_func_array($route['handler'], array($args));
		}
		// class creation
		if(is_string($route['handler']) && preg_match('/(.+)\h*(->|::)\h*(.+)/s', $route['handler'], $parts)) {
			if (!class_exists($parts[1]) || !method_exists($parts[1], $parts[3])) {
				throw new Exception(self::HTTP_404, 404);
			}
			call_user_func_array(array(new $parts[1], $parts[3]), array($args));
		}
		$this->benchmark->mark('callEnd');
	}
	/**
	 * parse request
	 * gets request arguments from the correct protocol for the given http verb
	 *
	 * @param string $verb the http verb
	 */
	function parseRequest($verb) {
		$args = array();
		switch ($verb) {
			case 'GET':
			case 'HEAD':
				$args = $_GET;
			break;
			case 'POST':
				$args = $_POST;
			break;
			case 'PUT':
			case 'DELETE':
				parse_str(file_get_contents('php://input'), $args);
			break;
		}
		return $args;
	}
	/**
	 * run
	 * framework execution
	 */
	function run() {
		$this->parseRoutes();
		$this->stats->mine();
	}
	/**
	 * split
	 * seperate a comma, semi-colon, or pipe delimited string
	 * @param string $str
	 * @param array $strs
	 */
	function split($str) {
		return array_map('trim',
			preg_split('/[,;|]/',$str,0,PREG_SPLIT_NO_EMPTY));
	}
	/**
	 * fatal error handler
	 * gracefully respond to fatal errors.
	 */
	function fatal_handler() {
		if(@is_array($err = @error_get_last())) {
			$code = isset($err['type']) ? $err['type'] : 0;
			if($code>0) {
				$this->error_handler(
					$code,
					isset($err['message']) ? $err['message'] : '',
					isset($err['file']) ? $err['file'] : '',
					isset($err['line']) ? $err['line'] : ''
				);
			}
		}
	}
	/**
	 * exception handler
	 * gracefully respond to exceptions.
	 *
	 * @param object $exc the php exception object
	 */
	function exception_handler($exc) {
		$this->error_handler(
			$exc->getCode(),
			$exc->getMessage(),
			$exc->getFile(),
			$exc->getLine(),
			$exc->getTrace()
		);		
	}
	/**
	 * error handler
	 * gracefully respond to errors
	 *
	 * @param int $num error code
	 * @param string $str error message
	 * @param string $file the file throwing the error
	 * @param int $line line number in the file throwing the error
	 * @param array $ctx error context
	 */	
	function error_handler($num, $str, $file, $line, $ctx=array()) {
		// remove php error output
		if(ob_get_length()>0){ 
			@ob_end_clean();
		}
		$code = $this->status($num);
		$this->logz->changeFile('error.log');
		$this->logz->write('error: '.$num.' - '.$str.' [file] '.$file.' [line] '.$line.' [context] '.trim(preg_replace('/\s+/', ' ', print_r($ctx, true))));
		//$this->stats->mine();
		if(library::get('CONFIG.debug')==true) {
			die('<h1>open qoob</h1><h3>error: '.$num.'!</h3><p>'.$str.'<br/><strong>file:</strong> '.$file.'<br/><strong>line:</strong> '.$line.'</p><pre>'.print_r($ctx, true).'</pre>');
		} else {
			die('<h1>open qoob</h1><h3>error: '.$code.'</h3>');
		}
	}
	/**
	 * open qoob
	 * get the singleton reference to the open qoob framework
	 * @return class qoob
	 */
	static function open() {
		if (!library::exists('CLASS.'.$class=__CLASS__)) {
			library::set('CLASS.'.$class, new $class);
		}
		return library::get('CLASS.'.$class);
	}
	/**
	 * clone
	 * disabled
	 * @deprecated
	 */
	private function __clone() {}
	/**
	 * constructor
	 * bootstraps the framework/core utils. initializes autoloading classes. set default variables.
	 */
	private function __construct() {
		spl_autoload_extensions(".php,.inc");
		spl_autoload_register(function($class) {
			$parts = explode('\\', $class);
			//support non-namespaced classes
			$parts[] = str_replace('_', DIRECTORY_SEPARATOR, array_pop($parts));
			$path = implode(DIRECTORY_SEPARATOR, $parts);
			$file = stream_resolve_include_path($path.'.php');
			if($file !== false) {
				require $file;
			}
		});
		library::set('STATUS.code', 200);
		library::set('UI.dir', realpath('ui'));
		library::set('TMP.dir', realpath('tmp'));
		library::set('CONFIG.debug', false);
		$this->load('qoob\utils\benchmark');
		$this->benchmark->mark('appStart');
		$this->load('qoob\utils\logz');
		$this->logz->setup(realpath('tmp'), 'error.log');
		$this->load('qoob\utils\stats');
	}
	/**
	 * destructor
	 * calculate the benchmarks
	 */
	public function __destruct() {
		if(library::get('CONFIG.debug')==true) {
			foreach ($this->benchmark->markers as $key => $value) {
				if(strpos($key, 'Start')>0) {
					$mark = substr($key, 0, strpos($key, 'Start'));
					$markers[$mark] = ($x=$this->benchmark->diff($mark.'Start', $mark.'End'))==false?('did not run'):($x.' seconds');
				}
			}
			echo str_replace('Array', 'benchmarks', '<pre style="border:1px solid #333;background:#ccc;padding:20px">'.print_r($markers, true).'</pre>');
			$this->logz->changeFile('benchmark.log');
			$this->logz->write(json_encode($markers));
		}
	}
}
//_________________________________________________________________________
//                                                           object library
/**
 * library
 * singleton object library
 *
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	2.22
 */
final class library {
	private static $catalog;
	static function exists($key) {
		return isset(self::$catalog[$key]);
	}
	static function get($key) {
		return self::$catalog[$key];
	}
	static function set($key, $value) {
		is_array($value) ? self::$catalog[$key][]=$value : self::$catalog[$key]=$value;
	}
	static function clear($key) {
		unset(self::$catalog[$key]);
	}
	function __construct() {}
	function __clone() {}
}
//_________________________________________________________________________
//                                                            open the qoob
/**
 * setup global error handling
 * @return class qoob instance
 */
$qoob = qoob::open();
set_error_handler(array(&$qoob, 'error_handler'));
set_exception_handler(array(&$qoob, 'exception_handler'));
register_shutdown_function(array(&$qoob, 'fatal_handler'));
return $qoob;
?>