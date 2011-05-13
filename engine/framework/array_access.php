<?

class Array_Access implements ArrayAccess
{
	protected $data = array();
	
    public function __set($key, $value) {
        $this->offsetSet($key, $value);
    }	

	public function offsetSet ($offset, $value) {		
		if (is_null($offset)) {
			$this->data[] = $value;
		} else {
			$this->data[$offset] = $value;
		}
	}
	
	public function offsetExists ($offset) {
		return isset($this->data[$offset]);
	}
	
	public function offsetUnset ($offset) {
		unset($this->data[$offset]);
	}
	
	public function offsetGet ($offset) {
		return isset($this->data[$offset]) ? $this->data[$offset] : null;
	}
	
	// Немного утилитарных функций доступа
	
	public function add_to ($key, $value, $offset = null) {		
		if (is_null($offset)) {			
			$this->data[$key][] = $value;
		} else {
			$this->data[$key][$offset] = $value;
		}
	}
	
	public function last_of ($key) {
		$return = $this->data[$key];
		
		return is_array($return) ? end($return) : $return;
	}
	
	public function first_of ($key) {
		$return = $this->data[$key];
		
		return is_array($return) ? reset($return) : $return;
	}
	
	// Алиас для краткости и читаемости, в случаях когда надо вызвать функцию напрямую.
	
	public function add ($offset, $value) {
		$this->offsetSet($offset, $value);
	}
}
