<?

class input__art extends input__common
{
	function add() {
		global $check; global $def; global $url; global $sets; global $cookie;
		if (!$cookie) $cookie = new dynamic__cookie();
		
		if (is_array(query::$post['images'])) {
			if ($url[2] == 'pool' && is_numeric($url[3])) $data = obj::db()->sql('select concat(id,"|") as pool, password from art_pool where id='.$url[3],1);
			if (!$data['password'] || $data['password'] == md5(query::$post['password'])) {
				$tags = obj::transform('meta')->add_tags(obj::transform('meta')->parse(query::$post['tags']));
				$category = obj::transform('meta')->category(query::$post['category']);
				$author = obj::transform('meta')->author(obj::transform('meta')->parse(query::$post['author'],$def['user']['author']));
//				query::$post['images'] = array_reverse(query::$post['images']);
				
				$similar = array();
				
				if (!empty(query::$post['dublicates'])) {
					foreach (query::$post['images'] as $image_key => $image) {
						if (query::$post['dublicates'] != $image_key + 1) {
							$name = explode('#',$image);
							$name[0] = $check->hash($name[0]); $name[1] = $check->hash($name[1]);
							unset(query::$post['images'][$image_key]);
							$similar[] = $name;
						}
					}
				}
				
				foreach (query::$post['images'] as $image) {
					$name = explode('#',$image);
					$name[0] = $check->hash($name[0]); $name[1] = $check->hash($name[1]);
					if (
						$name[0] && $name[1] && $name[2] &&
						!obj::db()->sql('select id from art where md5="'.$name[0].'"',2)
					) {						
						obj::db()->insert('art',$insert_data = array($name[0],$name[1],$name[2],$name[3],(int) $name[4],$author,$category,$tags,"|".$data['pool'],0,"",
												query::$post['source'],0,0,obj::transform('text')->rudate(),$time = ceil(microtime(true)*1000),$def['area'][1]));
						obj::db()->insert('versions',array('art',$id = obj::db()->sql('select @@identity from art',2),
														base64_encode(serialize($insert_data)),$time,$sets['user']['name'],$_SERVER['REMOTE_ADDR']));

						if (function_exists('puzzle_fill_cvec_from_file') && function_exists('puzzle_compress_cvec')) {
							$image = ROOT_DIR.SL.'images'.SL.'booru'.SL.'thumbs'.SL.'large_'.$name[1].'.jpg';
							$vector = puzzle_fill_cvec_from_file($image);
							$vector = base64_encode(puzzle_compress_cvec($vector));

							obj::db()->insert('art_similar',array($id, $vector, 0, '|'),false);
						}

						$i++;
					}
				}
				
				if (!empty($similar)) {
					if (!$id) $id = obj::db()->sql('select @@identity from art',2);
					$order_next = 0;
					foreach ($similar as $variant) {
						$insert = array($id, $variant[0], 
							$variant[1], $variant[2], 
							!empty($variant[3]), $order_next++, $variant[4]);
							
						obj::db()->insert('art_variation', $insert);	
					}					
				}

				if (!empty(query::$post['transfer_to']) && $sets['user']['rights']) {
					$_post = array('sure' => 1, 'do' => array('art','transfer'), 'where' => query::$post['transfer_to']);
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
		global $check; global $def; global $add_res;
		if (query::$post['name'] && $text = obj::transform('text')->format(query::$post['text'])) {
			query::$post['email'] = $check->email(query::$post['email'],'');
			obj::db()->insert('art_pool',array(query::$post['name'],$text,query::$post['text'],0,"|",md5(query::$post['password']),query::$post['email'],microtime(true)*1000));
			$id = obj::db()->sql('select @@identity from art_pool',2);
			$add_res['text'] = 'Новая группа успешно добавлена, и доступна по адресу <a href="/art/pool/'.$id.'/">http://4otaku.ru/art/pool/'.$id.'/</a>.';
		}
		else $add_res = array('error' => true, 'text' => 'Не все обязательные поля заполнены.');
	}

	function edit_art_image() {
		global $check;
		if ($check->num(query::$post['id']) && query::$post['type'] == 'art') {
			$name = explode('#',end(query::$post['images']));
			$name[0] = $check->hash($name[0]); $name[1] = $check->hash($name[1]);
			obj::db()->update('art',array('md5','thumb','extension','resized','animated'),$name,query::$post['id']);
			obj::db()->update('art_similar',array('checked','similar'),array(0,'|'),query::$post['id']);
		}		
	}

	function edit_art_source() {
		global $check;
		if ($check->num(query::$post['id']) && query::$post['type'] == 'art') {
			obj::db()->update('art','source',query::$post['source'],query::$post['id']);
		}
	}

	function edit_art_groups() {
		global $check;
		if ($check->num(query::$post['id']) && query::$post['type'] == 'art' && is_array(query::$post['group'])) {
			$pools = obj::db()->sql('select pool from art where id='.query::$post['id'],2);
			query::$post['group'] = array_filter(array_unique(query::$post['group']));
			foreach (query::$post['group'] as $key => $group)
				if (obj::db()->sql('select id from art_pool where (id='.$group.' and (locate("|'.query::$post['id'].'|",art) or (password != "" and password != "'.md5(query::$post['password']).'")))',2))
					unset(query::$post['group'][$key]);
			if (count(query::$post['group'])) {
				foreach (query::$post['group'] as $group) $pools .= $group.'|';
				$where = 'id='.implode(' or id=',query::$post['group']);
				obj::db()->update('art','pool',$pools,query::$post['id']);
				obj::db()->sql('update art_pool set count = count + 1, art = concat("|'.query::$post['id'].'",art) where ('.$where.')',0);
			}
		}
	}

	function edit_art_translations() {
		global $def; global $check;
		if ($check->num(query::$post['id']) && query::$post['type'] == 'art') {
			$time = microtime(true)*1000;
			$date = obj::transform('text')->rudate();
			obj::db()->update('art_translation','active',0,query::$post['id'],'art_id');
			if (query::$post['size'] == 'resized') {
				$info = obj::db()->sql('select resized, md5 from art where id='.query::$post['id'],1);
				$full_size = explode('x',$info['resized']);
				$small_size = getimagesize(ROOT_DIR.SL.'images/booru/resized/'.$info['md5'].'.jpg');
				$coeff = $full_size[0] / $small_size[0];
			} else {
				$coeff = 1;
			}
			foreach (query::$post['trans'] as $key => $translation) {
				if (!$text = obj::transform('text')->format($translation['text']))
					unset (query::$post['trans'][$key]);
				else {
					foreach ($translation as $key2 => $one) if ($key2 != 'text') query::$post['trans'][$key][$key2] = round(intval($one) * $coeff);
					query::$post['trans'][$key]['pretty_text'] = $translation['text']; query::$post['trans'][$key]['text'] = $text;
				}
			}
			obj::db()->insert('art_translation',array(query::$post['id'],base64_encode(serialize(query::$post['trans'])),query::$post['author'],$date,$time,1));
			obj::db()->sql('update art set translator="'.query::$post['author'].'" where id='.query::$post['id'].' and translator=""',0);
		}
	}
	
	function edit_art_variation() {
		global $def; global $check;
		
		if ($check->rights()) {
			preg_match('/(?:^|\/)(\d+)(?:\/|#|$)/', query::$post['from'], $from);
			
			$from = (int) $from[1];
			$to = (int) query::$post['to'];
			
			if ($from && $to) {
				$admin = new input__admin();
				$admin->make_similar($from, $to);
			}
		}
	}	
	
	function edit_art_variation_list() {
		global $check;
		
		$id = (int) query::$post['id'];
		
		if ($check->rights() && $id) {
			
			$main_image = array_shift(query::$post['images']);
			if (empty($main_image)) {
				return;
			}
			
			$main_image = explode('#', $main_image);
			if ($main_image[3] === '1') {
				$path = ROOT_DIR.SL.'images'.SL.'booru'.SL.'full'.SL.$main_image[0].'.'.$main_image[2];
				$sizes = getimagesize($path);
				$weight = filesize($path);						
			
				if ($weight > 1024*1024) {
					$weight = round($weight/(1024*1024),1).' мб';
				} elseif ($weight > 1024) {
					$weight = round($weight/1024,1).' кб';
				} else {
					$weight = $weight.' б';
				}
				$main_image[3] = "$sizes[0]x$sizes[1]px; $weight";
			}
			
			$update = array(
				'md5' => $main_image[0],
				'thumb' => $main_image[1],
				'extension' => $main_image[2],
				'resized' => $main_image[3],
				'animated' => $main_image[4],
			);
			
			obj::db()->update('art',array_keys($update),array_values($update),$id);
			
			obj::db()->sql('delete from art_variation where art_id='.$id,0);
			$order_next = 0;
			foreach (query::$post['images'] as $image) {
				$image = explode('#', $image);
				$insert = array($id, $image[0], 
					$image[1], $image[2], 
					!empty($image[3]), $order_next++, $image[4]);
					
				obj::db()->insert('art_variation', $insert);
			}
		}		
	}

}
