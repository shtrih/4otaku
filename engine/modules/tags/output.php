<?

class Tags_Output extends Output implements Plugins
{	
	const CACHE_PREFIX = '_tag_cache_items_';
	
	protected $count = 0;
	
	public function main () {
		$sections = Config::settings('sections');
		
		$query['section'] = key($sections);
		
		return $this->section($query);
	}
	
	public function section ($query) {
		$return = array();

		$type = $query['section'];
		if (empty($query['subsection'])) {
			$items = Config::settings('sections', $type, 'items');
			$area = key($items);
		} else {
			$area = $query['subsection'];
		}

		Cache::$prefix = self::CACHE_PREFIX;

		if (!($this->items = Cache::get($type.'_'.$area))) { 
		
			$this->items = $this->get_full_tag_cloud($type, $area);
			
			Cache::set($type.'_'.$area, $this->items, DAY);
		}
		
		$this->flags['area'] = $area;
		$this->flags['type'] = $type;
	}
	
	protected function get_full_tag_cloud ($type, $area) {
		$meta = Database::get_vector('meta', array('alias', 'name', 'color'), '`type` = "tag"');
		$aliases = array_unique(array_keys($meta));
	
		$count_worker = new Meta_Library();
		$data = $count_worker->get_meta_numbers($aliases, 'tag', $type, $area);
		$data = array_filter($data);

		$return = array();
		$max = 0; $min = false;
		
		foreach ($data as $alias => $one) {		
			
			if (!empty($meta[$alias])) {
				$return[] = array(
					'alias' => $alias,
					'count' => $one,
					'color' => $meta[$alias]['color'],
					'name' => $meta[$alias]['name'],
					'item_type' => 'tag',
				);
				
				$max = max($max, $one);
				$min = $min ? min($min, $one) : $one;
			}
		}

		$maxsize = Config::settings('tag_sizes', 'full_cloud_max');
		$minsize = Config::settings('tag_sizes', 'full_cloud_min');
		
		foreach ($return as & $one) {			
			$one['size'] = round(($maxsize - $minsize)*($one['count']-$min)/($max-$min) + $minsize);
		}
		unset($one);

		usort($return, array($this, 'namesort'));

		return $return;
	}
	
	protected function namesort($a, $b) {
		return strcmp($a['name'], $b['name']);
	}
}
