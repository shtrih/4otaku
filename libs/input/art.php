<?

class input__art extends input__common
{
	function add() { 
		global $post; global $check; global $def; global $url; global $sets; global $cookie;
		if (!$cookie) $cookie = new dinamic__cookie();
		
		if (is_array($post['images'])) {
			if ($url[2] == 'pool' && is_numeric($url[3])) $data = obj::db()->sql('select concat(id,"|") as pool, password from art_pool where id='.$url[3],1);
			if (!$data['password'] || $data['password'] == md5($post['password'])) {
				$tags = obj::transform('meta')->add_tags(obj::transform('meta')->parse($post['tags']));
				$category = obj::transform('meta')->category($post['category']);
				$author = obj::transform('meta')->author(obj::transform('meta')->parse($post['author'],$def['user']['author']));
				$post['images'] = array_reverse($post['images']);
				foreach ($post['images'] as $image) {
					$name = explode('#',$image);
					$name[0] = $check->hash($name[0]); $name[1] = $check->hash($name[1]); 
					if (
						$name[0] && $name[1] && $name[2] && 
						!obj::db('sub')->sql('select id from w8m_art where md5="'.$name[0].'"',2) && 
						!obj::db()->sql('select id from art where md5="'.$name[0].'"',2)
					) {
						obj::db()->insert('art',$insert_data = array($name[0],$name[1],$name[2],$name[3],$author,$category,$tags,"|".$data['pool'],"",
												$post['source'],0,0,obj::transform('text')->rudate(),$time = ceil(microtime(true)*1000),$def['area'][1]));
						obj::db()->insert('versions',array('art',$id = obj::db()->sql('select @@identity from art',2),
														base64_encode(serialize($insert_data)),$time,$sets['user']['name'],$_SERVER['REMOTE_ADDR']));					
						$i++;
					}
				}
				
				if (isset($post['transfer_to_main']) && $sets['user']['rights']) {
					$_post = array('sure' => 1, 'do' => array('art','transfer'), 'where' => 'main');
					include_once('libs/input/common.php');						
					if (!$id) $id = obj::db()->sql('select @@identity from art',2);
					$j = $i;
					while ($j > 0) {
						$_post['id'] = ($id - --$j);
						input__common::transfer($_post);
					}		
				} else {
					if ($i > 1) $this->add_res('Ваши изображения успешно добавлены, и доступны в <a href="/art/'.$def['area'][1].'/">очереди на премодерацию</a>.');
					else $this->add_res('Ваше изображение успешно добавлено, и доступно по адресу <a href="/art/'.$id.'/">http://4otaku.ru/art/'.$id.'/</a> или в <a href="/art/'.$def['area'][1].'/">очереди на премодерацию</a>.');
				}
				
				if ($data) {
					if (!$id) $id = obj::db()->sql('select @@identity from art',2);
					$j = 0;
					while ($j < $i) $newart .= '|'.($id - $j++);
					obj::db()->sql('update art_pool set count = count + '.$i.', art = concat("'.$newart.'",art) where id='.$url[3],0);
				}
			}
			else $this->add_res('Неправильный пароль от группы.', true);
		}
		else $this->add_res('Не все обязательные поля заполнены.', true);
	}
	
	function addpool() {
		global $post; global $check; global $def; global $add_res;
		if ($post['name'] && $text = obj::transform('text')->format($post['text'])) {
			$post['email'] = $check->email($post['email'],'');
			obj::db()->insert('art_pool',array($post['name'],$text,$post['text'],0,"|",md5($post['password']),$post['email'],microtime(true)*1000));
			$id = obj::db()->sql('select @@identity from art_pool',2);
			$add_res['text'] = 'Новая группа успешно добавлена, и доступна по адресу <a href="/art/pool/'.$id.'/">http://4otaku.ru/art/pool/'.$id.'/</a>.';
		}
		else $add_res = array('error' => true, 'text' => 'Не все обязательные поля заполнены.');
	}
	
	function edit_art_image() {
		global $post; global $check;
		if ($check->num($post['id']) && $post['type'] == 'art') {
			$name = explode('#',end($post['images']));
			$name[0] = $check->hash($name[0]); $name[1] = $check->hash($name[1]);
			obj::db()->update('art',array('md5','thumb','extension','resized'),$name,$post['id']);
		}		
	}
		
	function edit_art_source() {
		global $post; global $check;
		if ($check->num($post['id']) && $post['type'] == 'art') {
			obj::db()->update('art','source',$post['source'],$post['id']);
		}		
	}
	
	function edit_art_groups() {
		global $post; global $check;
		if ($check->num($post['id']) && $post['type'] == 'art' && is_array($post['group'])) {
			$pools = obj::db()->sql('select pool from art where id='.$post['id'],2);
			$post['group'] = array_filter(array_unique($post['group']));
			foreach ($post['group'] as $key => $group)
				if (obj::db()->sql('select id from art_pool where (id='.$group.' and (locate("|'.$post['id'].'|",art) or (password != "" and password != "'.md5($post['password']).'")))',2))
					unset($post['group'][$key]);
			if (count($post['group'])) {
				foreach ($post['group'] as $group) $pools .= $group.'|';
				$where = 'id='.implode(' or id=',$post['group']);
				obj::db()->update('art','pool',$pools,$post['id']);
				obj::db()->sql('update art_pool set count = count + 1, art = concat("|'.$post['id'].'",art) where ('.$where.')',0);
			}
		}		
	}	
	
	function edit_art_translations() {
		global $post; global $def; global $check;
		if ($check->num($post['id']) && $post['type'] == 'art') {
			$time = microtime(true)*1000;
			$date = obj::transform('text')->rudate();
			obj::db()->update('art_translation','active',0,$post['id'],'art_id');
			if ($post['size'] == 'resized') {
				$info = obj::db()->sql('select resized, md5 from art where id='.$post['id'],1);
				$full_size = explode('x',$info['resized']);
				$small_size = getimagesize(ROOT_DIR.SL.'images/booru/resized/'.$info['md5'].'.jpg');
				$coeff = $full_size[0] / $small_size[0];
			} else {
				$coeff = 1;
			}
			foreach ($post['trans'] as $key => $translation) {
				if (!$text = obj::transform('text')->format($translation['text']))
					unset ($post['trans'][$key]);
				else {
					foreach ($translation as $key2 => $one) if ($key2 != 'text') $post['trans'][$key][$key2] = round(intval($one) * $coeff);
					$post['trans'][$key]['pretty_text'] = $translation['text']; $post['trans'][$key]['text'] = $text;
				}
			}
			obj::db()->insert('art_translation',array($post['id'],base64_encode(serialize($post['trans'])),$post['author'],$date,$time,1));
			obj::db()->sql('update art set translator="'.$post['author'].'" where id='.$post['id'].' and translator=""',0);			
		}		
	}	

}
