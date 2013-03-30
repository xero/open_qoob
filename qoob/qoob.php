<?php
/**
 * open qoob framework
 *
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	2.072
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
		VERBS='GET|HEAD|POST|PUT|PATCH|DELETE|CONNECT';
	/**
	 * http status codes
	 * @see http://www.rfc-editor.org/rfc/rfc2616.txt 
	 */
	const
		//--- informational
		HTTP_100='Continue',
		HTTP_101='Switching Protocols',
		//--- successful
		HTTP_200='OK',
		HTTP_201='Created',
		HTTP_202='Accepted',
		HTTP_203='Non-Authorative Information',
		HTTP_204='No Content',
		HTTP_205='Reset Content',
		HTTP_206='Partial Content',
		//--- redirection
		HTTP_300='Multiple Choices',
		HTTP_301='Moved Permanently',
		HTTP_302='Found',
		HTTP_303='See Other',
		HTTP_304='Not Modified',
		HTTP_305='Use Proxy',
		HTTP_307='Temporary Redirect',
		//--- client error
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
		//--- server error
		HTTP_500='Internal Server Error',
		HTTP_501='Not Implemented',
		HTTP_502='Bad Gateway',
		HTTP_503='Service Unavailable',
		HTTP_504='Gateway Timeout',
		HTTP_505='HTTP Version Not Supported';
	/**
	 * internal variables
	 */
	private 
		$debug;
	/**
	 * status
	 * echo http header and return status message
	 * @param int $code status code
	 * @return string status message
	 */
	function status($code) {
		// account for system thrown exceptions
		$code = $code>0 ? $code : 500;
		// no commandline headers
		if (PHP_SAPI!='cli') {
			header('HTTP/1.1 '.$code);
		}
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
    	$verb = $_SERVER['REQUEST_METHOD'];
    	library::set('QOOB.url', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    	library::set('QOOB.domain', 'http://'.dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]));
    	library::set('REQUEST.uri', rtrim(str_replace(library::get('QOOB.domain'), '', library::get('QOOB.url')), '/'));
    	library::set('REQUEST.ajax', (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest')?'AJAX':'SYNC');
    	$found = false;
		foreach(library::get('ROUTES') as  $route) {
			// regular expression to identify uri requests formatted like '/users/:uid/posts/:pid'
			$pattern = "@^".preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route['pattern']))."$@D";
			$args = array();
			// check if the current request matches the expression
			if($verb == $route['verb'] && preg_match($pattern, library::get('REQUEST.uri'), $matches)) {
				if($route['type'] == library::get('REQUEST.ajax')) {
					// remove the first match
					array_shift($matches);
					$found = true;
					// correlate route regex with uri parameters
					preg_match_all('/\\\:[a-zA-Z0-9\_\-]+/', preg_quote($route['pattern']), $names);
					for($i=0;$i<count($names[0]);$i++) {
						$args[str_replace('\:', '', $names[0][$i])] = isset($matches[$i])?$matches[$i]:'';
					}
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
		//closure style
		if(is_callable($route['handler'])) {
			call_user_func_array($route['handler'], array($args));
		}
		//class creation
		if(is_string($route['handler']) && preg_match('/(.+)\h*(->|::)\h*(.+)/s', $route['handler'], $parts)) {
			if (!class_exists($parts[1]) || !method_exists($parts[1], $parts[3])) {
				throw new Exception(self::HTTP_404, 404);
			}
			call_user_func_array(array(new $parts[1], $parts[3]), array($args));
		}
		$this->benchmark->mark('callEnd');
	}
	/**
	 * run
	 * begin framework execution
	 */
	function run() {
		$this->parseRoutes();
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
	 * exception handler
	 * gracefully respond to exceptions.
	 *
	 * @param object $exc the php exception object
	 */
	function exception_handler($exc) {
		$code = $exc->getCode();
		$msg = $exc->getMessage();
		$this->status($code);
		/**
		  * @todo respond in the correct context
		  */
		die("<h1>open qoob</h1><h3>exception: ".$code."!</h3><p>".$msg."</p>");
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
	function error_handler($num, $str, $file, $line, $ctx) {
		$this->status($num);
		die('<h1>open qoob</h1><h3>error: '.$num.'!</h3><p>num: '.$num.'<br/>str: '.$str.'<br/>file: '.$file.'<br/>line: '.$line.'</p><pre>'.print_r($ctx, true).'</pre>');
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
	 * bootstraps the framework. initializes autoloading classes.
	 */
	private function __construct() {
		set_error_handler(array(&$this, 'error_handler'));
		set_exception_handler(array(&$this, 'exception_handler'));
		set_include_path(
			implode(
				PATH_SEPARATOR, 
				array(
					get_include_path(), 
					basename(__DIR__).DIRECTORY_SEPARATOR.'utils', 
					basename(__DIR__).DIRECTORY_SEPARATOR.'core',
					basename(__DIR__).DIRECTORY_SEPARATOR.'api'
				)
			)
		);
		spl_autoload_register();
		$this->load('qoob\utils\benchmark');
		$this->benchmark->mark('appStart');
	}
	/**
	 * destructor
	 * displays the benchmarks
	 */
	public function __destruct() {
		foreach ($this->benchmark->markers as $key => $value) {
			if(strpos($key, 'Start')>0) {
				$mark = substr($key, 0, strpos($key, 'Start'));
				$markers[$mark] = ($x=$this->benchmark->diff($mark.'Start', $mark.'End'))==false?('did not run'):($x.' seconds');
			}
		}
		echo str_replace('Array', 'benchmarks', '<pre style="border:1px solid #333;background:#ccc;padding:20px">'.print_r($markers, true).'</pre>');
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
 * @return class qoob instance
 */
return qoob::open();
?>