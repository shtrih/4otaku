<?php

class Create_Post extends Create_Abstract
{
	protected $field_rights = array(
		'transfer_to' => 1
	);
	
	protected $function_rights = array(
		'update' => 1
	);
	
	public function main() {
		
		$this->set_redirect();
		$post = $this->correct_main_data(query::$post);		
		
		if (!$post['title'] || !$post['link']) {		
			engine::add_res('Не все обязательные поля заполнены.', true);
			return;
		}		
		
		if ($post['author'] != def::user('name') && $post['author']) {
			$cookie = new dynamic__cookie();
			$cookie->inner_set('user.name', $post['author']);
		}		
		
		$worker = new Transform_Meta();

		$parsed_tags = $worker->parse_array($post['tags']);
		$tags = $worker->add_tags($parsed_tags);
		$category = $worker->category($post['category']);
		$language = $worker->language($post['language']);
		$parsed_author = $worker->parse($post['author'], def::user('author'));
		$author = $worker->author($parsed_author);

		$text = Transform_Text::format($post['text']);
		
		$links = Transform_Link::parse($post['link']);
		$extras = Transform_Link::parse($post['bonus_link']);
			
		$item = new Model_Post();
		$item->set_array(array(
			'title' => $post['title'],
			'text' => $text,
			'pretty_text' => undo_safety($post['text']),
			'author' => $author,
			'category' => $category,
			'language' => $language,
			'tag' => $tags
		));
			
		$item->insert();
		
		foreach($post['images'] as $image) {
			$image = explode('.', $image);
			$image = new Model_Post_Image(array(
				'file' => $image[0], 
				'extension' => $image[1]
			));
			$item->add_image($image);
		}
		
		foreach($links as $link) {
			$link = new Model_Post_Link($link);
			$item->add_link($link);
		}
		
		foreach($extras as $extra) {
			$extra = new Model_Post_Extra($extra);
			$item->add_extra($extra);
		}
		
		foreach($post['file'] as $file) {
			$file = new Model_Post_File($file);
			$item->add_file($file);
		}
		
		$item->save_additional_data();
				
		// TODO: перемести input__common::transfer в Model_Common
		if (!empty($post['transfer_to'])) {
			input__common::transfer(array(
				'sure' => 1, 
				'do' => array('post', 'transfer'), 
				'where' => $post['transfer_to'],
				'id' => $item->get_id()
			));
		}

		engine::add_res('Ваша запись успешно добавлена, и доступна по адресу '.
			'<a href="/post/'.$item->get_id().'/">http://4otaku.ru/post/'.$item->get_id().'/</a> или в '.
			'<a href="/post/'.def::area(1).'/">мастерской</a>.');			
	}
	
	public function update() {

		$this->set_redirect();
		
		if (!is_numeric(query::$post['id'])) {
			engine::add_res('Что-то странное с формой обновления, сообщите администрации', true);
			return;			
		}
		
		$author = trim(strip_tags(query::$post['author']));
		if (empty($author)) {
			engine::add_res('Вы забыли указать автора обновления', true);
			return;
		}
		
		$text = Transform_Text::format(query::$post['text']);
		if (!trim(strip_tags($text))) {
			engine::add_res('Вы забыли добавить описание обновления', true);
			return;			
		}		
		
		$links = array();
		foreach (query::$post['link'] as $link) {
			if (!empty($link['use'])) {
				unset($link['use']);
				$links[] = $link;
			}
		}
		
		$links = Transform_Link::similar(Transform_Link::parse($links));
		foreach ($links as $link) {
			if (count(array_filter($link['url'])) > 0) {
				$links_found = true;
				break;
			}
		}
		if (empty($links_found)) {
			engine::add_res('Проверьте ссылки, с ними была какая-то проблема', true);
			return;			
		}
		
		$update = new Model_Post_Update(array(
			'post_id' => query::$post['id'],
			'username' => $author,
			'text' => $text,
			'pretty_text' => undo_safety(query::$post['text']),
			'link' => serialize($links)
		));
		$update->insert();
	
		engine::add_res('Запись успешно обновлена');
	}
	
	protected function correct_main_data($data) {
		
		if (empty($data['tags'])) {
			$data['tags'] = '';
		}
		
		if (empty($data['images'])) {
			$data['images'] = array();
		} else {
			$data['images'] = (array) $data['images'];
		}
		
		$data['link'] = Check::link_array($data['link']);
		$data['bonus_link'] = Check::link_array($data['bonus_link']);

		return $data;
	}
}
