<?php

class Read_Post_Update extends Read_Abstract
{
	protected $template = 'main/update';
	protected $error_template = 'error/post';
	
	protected $single_post_template = 'dynamic/post/update';
	
	protected $side_modules = array(
		'head' => array('title', 'js', 'css'),
		'header' => array('menu', 'personal'),
		'top' => array(),
		'sidebar' => array('comments','update','orders','tags'),
		'footer' => array('year')
	);
	
	public function __construct() {
		parent::__construct();		
		
		$this->per_page = sets::pp('updates_in_line');
	}
	
	protected function display_single_item($url) {
		
		$items = Database::order('sortdate', 'asc')
			->get_full_vector('post_update', 'post_id = ?', (int) $url[1]);
			
		foreach ($items as $id => &$item) {
			$item['id'] = $id;
			$item = new Model_Post_Update($item);
		}
		unset($item);
		
		$this->get_update_links($items);
		
		$this->template = $this->single_post_template;			
		$this->data['items'] = $items;
	}
	
	protected function display_index($url) {
		
		$this->get_items();
	}
	
	protected function display_page($url) {
	
		$this->set_page($url, 2);		
		$this->get_items();	
	}
	
	protected function get_items() {
		
		$start = ($this->page - 1) * $this->per_page;
		
		$items = Database::set_counter()->order('pu.sortdate')
			->join('post', 'p.id = pu.post_id')->limit($this->per_page, $start)
			->get_table('post_update', array('pu.*', 'p.title', 'p.comment_count'));

		$this->count = Database::get_counter();

		$pointers = array();
		$post_pointers = array();
		foreach ($items as &$item) {
			$item = new Model_Post_Update($item);
			$pointers[$item['id']] = $item;
			$post_pointers[$item['post_id']] = $item;
		}
		unset($item);			

		$this->get_update_images($post_pointers);
		$this->get_update_links($pointers);

		$this->data['items'] = $items;
		$this->data['navi'] = $this->get_bottom_navi();			
	}
		
	protected function get_bottom_navi() {
		$return = array();		
		
		$return['curr'] = $this->page;
			
		$return['last'] = ceil($this->count / $this->per_page);
		
		$return['start'] = max($return['curr'] - 5, 2);
		$return['end'] = min($return['curr'] + 6, $return['last'] - 1);

		$return['base'] = '/post/updates/';
		
		return $return;
	}
	
	protected function get_update_links($items) {
		$keys = array_keys($items);
		$links = Database::join('post_update_link_url', 'pulu.link_id = pul.id')
			->join('post_url', 'pulu.url_id = pu.id')->order('pul.order', 'asc')
			->order('pulu.order', 'asc')->get_full_vector('post_update_link', 
				Database::array_in('pul.update_id', $keys), $keys);

		foreach ($links as $link) {
			$link = new Model_Post_Update_Link($link);
			$items[$link['update_id']]->add_link($link);
		}		
	}
	
	protected function get_update_images($items) {
		$keys = array_keys($items);
		$images = Database::order('order', 'asc')->group('post_id')
			->get_full_table('post_image', Database::array_in('post_id', $keys), $keys);
				
		foreach ($images as $image) {
			$image = new Model_Post_Image($image);
			$items[$image['post_id']]->add_image($image);
		}		
	}
}
