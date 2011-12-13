<?php

class Read_Post_Gouf extends Read_Abstract
{
	protected $template = 'main/gouf';	
	protected $error_template = 'error/post';
	
	protected $mode = 'main';
	protected $sorters = array();
	
	protected $side_modules = array(
		'head' => array('title', 'js', 'css'),
		'header' => array('menu', 'personal'),
		'top' => array(),
		'sidebar' => array('comments','update','orders','tags'),
		'footer' => array('year')
	);
	
	public function __construct() {
		parent::__construct();
		
		$this->per_page = sets::pp('post');
		
		$sorter = new Database_Sorter('overall');
		$this->sorters[] = $sorter;
	}
	
	public function process($url) {
		if ($url[1] == 'update') {
			
			$this->mode = 'update';			
			array_splice($url, 1, 1);
		}
		
		parent::process($url);
	}
	
	protected function get_items() {		
		
		$start = ($this->page - 1) * $this->per_page;
		
		$query = Database::set_counter()->limit($this->per_page, $start);
		
		if ($this->mode == 'update') {
			$items = $this->get_items_update($query);
		} else {
			$items = $this->get_items_post($query);
		}
		
		$this->data['items'] = $items;
		$this->data['sorters'] = $this->sorters;
		$this->data['mode'] = $this->mode;
	}
	
	protected function get_items_update(Database_Instance $query) {
		
		foreach ($this->sorters as $sorter) {
			$sorter->set_prefix('pus');
			$query->order($sorter);			
		}
		
		$data = $query->join('post_update', 'pu.id = pus.id')
			->get_full_vector('post_update_status');

		$this->count = Database::get_counter();
			
		foreach ($data as $key => $item) {
			$item['id'] = $key;
			$data[$key] = new Model_Post_Update($item);
		}		
		$keys = array_keys($data);
		
		$links = Database::join('post_update_link_url', 'pulu.link_id = pul.id')
			->join('post_url', 'pulu.url_id = pu.id')->order('pul.order', 'asc')
			->order('pulu.order', 'asc')->get_full_table('post_update_link', 
				Database::array_in('pul.update_id', $keys), $keys);

		foreach ($links as $link) {
			$link = new Model_Post_Update_Link($link);
			$data[$link['update_id']]->add_link($link);
		}

		$images = Database::order('order', 'asc')->group('post_id')
			->get_full_table('post_image', Database::array_in('post_id', $keys), $keys);
				
		foreach ($images as $image) {
			$image = new Model_Post_Image($image);
			$data[$image['post_id']]->add_image($image);
		}		
		
		return $data;
	}
	
	protected function get_items_post(Database_Instance $query) {
		
		foreach ($this->sorters as $sorter) {
			$sorter->set_prefix('ps');
			$query->order($sorter);			
		}
		
		$data = $query->join('post', 'p.id = ps.id')
			->get_full_vector('post_status');

		$this->count = Database::get_counter();

		foreach ($data as $key => $item) {
			$item['id'] = $key;
			$data[$key] = new Model_Post($item);
		}
		
		$keys = array_keys($data);

		$images = Database::order('order', 'asc')->get_full_table('post_image', 
			Database::array_in('post_id', $keys), $keys);
				
		foreach ($images as $image) {
			$image = new Model_Post_Image($image);
			$data[$image['post_id']]->add_image($image);
		}
		
		$links = Database::join('post_link_url', 'plu.link_id = pl.id')
			->join('post_url', 'plu.url_id = pu.id')->order('pl.order', 'asc')
			->order('plu.order', 'asc')->get_full_table('post_link', 
				Database::array_in('pl.post_id', $keys), $keys);
		
		foreach ($links as $link) {
			$link = new Model_Post_Link($link);
			$data[$link['post_id']]->add_link($link);
		}
		
		return $data;
	}		
	
	protected function display_index($url) {
		
		$this->get_items();
	}
	
	protected function display_page($url) {

		$this->set_page($url, 2);
		$this->get_items();
	}
	
	protected function display_sort($url) {
		
		$this->set_sort($url, 2);
		$this->set_page($url, 4);
		$this->get_items();
	}
}