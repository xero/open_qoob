<?
/**
 * open qoob framework
 *
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	2.0057
 */
class qoob {
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
	 * route
	 * add a route pattern
	 * @param string $pattern route
	 * @param mixed $handler closure function or class->method reference
	 */
	function route($pattern, $handler) {
		if(empty($handler)) {
			die('missing callback');
		}
		$parts = explode(' ', trim($pattern));
		foreach ($this->split($parts[0]) as $verb) {
			if (!preg_match('/GET|HEAD|POST|PUT|PATCH|DELETE|CONNECT/', strtoupper($verb))) {
				die(sprintf('not implemented: %s', $verb));
			}
			if(!isset($parts[1])) {
				die(sprintf('invalid routing pattern: %s', $pattern));
			}
			$type = isset($parts[2])?str_replace(array('[',']'), '', strtoupper($parts[2])):'SYNC';
			if (!preg_match('/SYNC|AJAX/', $type)) {
				die(sprintf('invalid request type: %s', $type));
			}
			library::set(
				'routes', 
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
    	library::set('url', 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);
    	library::set('domain', 'http://'.dirname($_SERVER["HTTP_HOST"].$_SERVER["SCRIPT_NAME"]));
    	library::set('uri', rtrim(str_replace(library::get('domain'), '', library::get('url')), '/'));
    	library::set('ajax', (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])&&strtolower($_SERVER['HTTP_X_REQUESTED_WITH'])=='xmlhttprequest')?'AJAX':'SYNC');
    	$found = false;
		foreach(library::get('routes') as  $route) {
			// convert uris like '/users/:uid/posts/:pid' to regular expression
			$pattern = "@^".preg_replace('/\\\:[a-zA-Z0-9\_\-]+/', '([a-zA-Z0-9\-\_]+)', preg_quote($route['pattern']))."$@D";
			$args = array();
			// check if the current request matches the expression
			if($verb == $route['verb'] && preg_match($pattern, library::get('uri'), $matches)) {
				if($route['type'] == library::get('ajax')) {				
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
			die('404 file not found');
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
				die('404 file not found');
			}
			call_user_func_array(array(new $parts[1], $parts[3]), array($args));
		}
		$this->benchmark->mark('callEnd');
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
			if(!library::get($name)) {
				// create class and set a reference to it
				library::set($name, new $class);
				$this->$name = library::get($name);
			}
		}
	}	
	/**
	 * open qoob
	 * get the singleton reference to the open qoob framework
	 * @return class qoob
	 */
	static function open() {
		if (!library::exists($class=__CLASS__)) {
			library::set($class, new $class);
		}
		return library::get($class);
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