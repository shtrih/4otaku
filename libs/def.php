<?

class def
{
	static $data = array();
	
	static function import($def) {
		foreach ($def as $key => $value) {
			self::$data[$key] = $value;
		}
	}
	
	static function array_get($name) {
		if (isset(self::$data[$name])) {
			return self::$data[$name];
		} 
		
		return array();	
	}
	
	static function get($name, $key = false, $sets_retrieve = true) {
		if ($key === false) {
			return self::array_get($name);
		}

		if (isset(self::$data[$name][$key])) {
			return self::$data[$name][$key];
		} 
		
		// Если внезапно нужной настройки не оказалось, последние меры умирающего.
		if (empty(self::$data)) {
			include ROOT_DIR.SL.'engine'.SL.'config.php';
			self::import($def);
			if (isset($def[$name][$key])) {
				return $def[$name][$key];
			}			
		} 
		
		if ($sets_retrieve && ($sets = sets::get($name,$key,false))) {
			return $sets;			
		} 

		return null;
	}
	
	public static function __callStatic($name, $arguments) {
		return self::get($name,$arguments[0]);
	}	
	
	static function db($key) {
		return self::get('db',$key);
	}
	
	static function site($key) {
		return self::get('site',$key);
	}	
	
	static function video($key) {
		return self::get('video',$key);
	}		
	
	static function user($key) {
		return self::get('user',$key);
	}	
}