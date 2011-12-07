<?php

class Update_Post extends Update_Abstract
{	
	protected $field_rights = array(
		'author' => 1
	);
	
	protected $model;
	
	public function __construct($data) {
		if (empty($data['id']) || !Check::id($data['id'])) {
			throw new Error_Update('Incorrect Id');
		}		
		
		$model = new Model_Post($data['id']);
		$model->load();
		
		if ($model->is_phantom()) {
			throw new Error_Update('Incorrect Id');
		}
		
		if ($model['area'] != 'workshop' && !sets::user('rights')) {
			throw new Error_Update('Not enough rights');
		}		
		
		$this->model = $model;
		
		parent::__construct();
	}
	
	protected function save_changes() {
		$this->model->commit();
	}
	
	protected function title($data) {
		$this->model['title'] = $data['title'];
	}
	
	protected function text($data) {
		
		$text = Transform_Text::format($data['text']);
		
		$this->model['text'] = $text;
		$this->model['pretty_text'] = $data['text'];
	}
	
	protected function category($data) {

		$worker = new Transform_Meta();

		$category = $worker->category($data['category']);
		$this->model['category'] = $category;
	}	

	protected function language($data) {
		
		$worker = new Transform_Meta();
		
		$language = $worker->language($data['language']);
		$this->model['language'] = $language;
	}

	protected function tag($data) {
		
		$worker = new Transform_Meta();
		
		if ($this->model['area'] == 'flea_market' || 
			$this->model['area'] == 'main') {
			
			$area = 'post_'.$this->model['area'];			
			$worker->erase_tags(array_keys($this->model['meta']['tag']), $area);
		} else {
			$area = false;
		}

		$tag = $worker->parse_array($data['tag']);
		$tag = $worker->add_tags($tag, $area);
		$this->model['tag'] = $tag;
	}			

	protected function author($data) {
		
		$worker = new Transform_Meta();
		
		$author = $worker->parse($data['author'], def::user('author'));
		$author = $worker->author($author);

		$this->model['author'] = $author;
	}
}
