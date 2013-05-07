<?php
/**
 * stache
 * a simple mustache inspired template system
 *
 * @example
 * {{var_name}} = regular variable (escaped)
 * {{&unescaped}} = an unescaped variable (may contain html)
 * {{!ignored}} = a variable that will not be rendered
 * {{#required}} = required variables will throw exceptions if not set
 * {{@unescaped_required}} = required unescaped variable
 *
 * @author 		xero harrison <x@xero.nu>
 * @copyright 	creative commons attribution-shareAlike 3.0 unported
 * @license 	http://creativecommons.org/licenses/by-sa/3.0/ 
 * @version 	2.33
 */
namespace qoob\core\view;
class stache {
	/**
	 * error constants
	 */
    const
        E_Open='Unable to open: %s',
        E_Require='Missing required variable: %s';
	/**
	 * render
	 * load, create, and display a template
	 *
	 * @param string $view file name (minus .html extension)
	 * @param array $data name value pairs to replace in the template file
	 * @param boolean $return auto echo on false, return string on true (default = false)
	 */
	public function render($view, $data = array(), $return = false) {
		$file = \library::get('UI.dir').DIRECTORY_SEPARATOR.'html'.DIRECTORY_SEPARATOR.$view.".html";
		if(file_exists($file)) {
			ob_start();
			include($file);
			$content = ob_get_contents();
			ob_end_clean();
			if(sizeof($data) > 0) {
				$content = $this->build($content, $data);
			}
			if($return) {
				return $content;
			} else {
	    		echo $content;
	    	}
	    } else {
	    	throw new \Exception(sprintf(self::E_Open, $file), 500);
	    }	
	}
	/**
	 * build
	 * replace mustache style markup with real data.
	 *
	 * @param string $content the template files markup
	 * @param array $data name value pairs to replace in the template file
	 */
	private function build($content, $data) {
		preg_match_all('/{{(.+?)}}/', $content, $staches);
		if(isset($staches[1])) {
			$staches = array_unique($staches[1]);
			$patterns = array();
			$replace = array();
			foreach ($staches as $key => $value) {
				$patterns[] = '/{{'.$value.'}}/';
				$prefix = substr($value, 0, 1);
				$value_name = preg_replace('/[\&\!\#\@]/', '', $value);
				//assigned variable
				if(isset($data[$value_name])) {
					switch($prefix) {
						//unescaped
						case '&':
							$replace[] = $data[$value_name];
						break;
						//ignored
						case '!':
							$replace[] = '';
						break;
						//required
						case '#':
							if(trim($value) === '') {
								throw new \Exception(sprintf(self::E_Require, $value_name), 500);
							}
							$replace[] = htmlentities($data[$value_name]);
						break;
						//required unescaped
						case '@':
							if(trim($value) === '') {
								throw new \Exception(sprintf(self::E_Require, $value_name), 500);
							}
							$replace[] = $data[$value_name];
						break;
						//escaped
						default:
							$replace[] = htmlentities($data[$value_name]);
						break;
					}
				//unassigned variable
				} else {
					if($prefix == '#' || $prefix == '@') {
						throw new \Exception(sprintf(self::E_Require, $value_name), 500);
					} else {
						$replace[] = '';
					}
				}
			}
			$content = preg_replace($patterns, $replace, $content);
		}
		return $content;
	}
}

?>