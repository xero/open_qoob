<?
/**
 * open qoob framework
 *
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	2.001
 */
class qoob {
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