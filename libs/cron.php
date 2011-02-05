<?

class cron
{

	/* Gouf - проверяльщик ссылок */

	function gouf_check() {
		$count = obj::db()->sql('select count(id) from gouf_links',2);
		$limit = ceil($count/1440);
		$links = obj::db()->sql('select id, link, status from gouf_links order by checkdate limit '.$limit);
		$base = obj::db()->sql('select * from gouf_base order by id');
		if (is_array($links)) foreach ($links as $link) {
			$result = $this->gouf_check_single($base, $link['link']);
			if ($result != 'unknown' && $result != $link['status']) {
				obj::db()->update('gouf_links',array('checkdate','status'),array(time(),$result),$link['id']);
			} else {
				obj::db()->update('gouf_links',array('checkdate'),array(time()),$link['id']);
			}
		}
	}

	function gouf_refresh_links() {
		$posts = obj::db()->sql('select id, title, link from post where area = "main"');
		$gouf_temp_links = obj::db()->sql('select id, link from gouf_links');

		$post_links = array(); $gouf_links = array();
		if (is_array($gouf_temp_links)) foreach ($gouf_temp_links as $link) {
			$link['link'] = html_entity_decode($link['link'], ENT_QUOTES, "utf-8" );
			$gouf_links[$link['link']] = $link;
		}
		if (is_array($posts)) foreach ($posts as $post) {
			$links = unserialize ($post['link']);
			if (is_array($links)) foreach ($links as $row)
				  if (is_array($row['url'])) foreach ($row['url'] as $link)
					  $post_links[$link] = array('id' => $post['id'],'link' => $link,'title' => $post['title']);
		}

		$delete_row = array_diff_key($gouf_links,$post_links);
		$insert_row = array_diff_key($post_links,$gouf_links);

		if (is_array($delete_row)) foreach ($delete_row as $link) obj::db()->sql('delete from gouf_links where id='.$link['id'],0);
		if (is_array($insert_row)) foreach ($insert_row as $link) obj::db()->insert('gouf_links',array($link['id'],$link['title'],0,'works',$link['link']));
	}

	function gouf_check_single($base, $link){
		$link = str_replace('&apos;',"'",html_entity_decode($link,ENT_QUOTES,'UTF-8'));
		$return = 'works';
		foreach ($base as $one) if (stristr($link,$one['alias'])) {

			$input = $this->gouf_curl(str_replace(' ','%20',stripslashes($link)), ($one['alias'] == 'mediafire.com'));

			if (!trim($input) || ($one['alias'] == 'megaupload.com' && stristr($input,'http://www.megaupload.com/?c=msg')))
				return 'unknown';

			$return = 'error';
			if ($input) {
				$tests = explode('|',$one['text']);
				foreach ($tests as $test) if (stristr($input,$test)) $return = 'works';
			}
			break;

		}
		if ($return == 'error' && $one['alias'] == 'megaupload.com') {
			$fh = fopen("test.txt", 'w');
			fwrite($fh, $link."\n\n".implode("/n", $tests)."\n\n".$input);
			fclose($fh);
		}
		return $return;
	}

	function gouf_curl($link, $simple=false) {

		if (!$simple) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)');
			curl_setopt($ch, CURLOPT_TIMEOUT, 20);
			curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
			curl_setopt($ch, CURLOPT_URL, $link);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$input = curl_exec($ch);
			curl_close($ch);
		}
		else $input = @file_get_contents($link);
		return $input;
	}

	/* Gouf закончился */

	function clean_tags() {
		global $def;
/*		foreach ($def['type'] as $type) $query .= ' union select concat("'.$type.'",id) as id, tag, area, "'.$type.'" as type from '.$type;
		$data = obj::db()->sql(substr($query,7));
		foreach ($data as $one) {
			$tags = array_unique(array_filter(explode('|',$one['tag'])));
			foreach ($tags as $tag) $update[$tag][$one['type'].'_'.$one['area']]++;
		}
		$tags = obj::db()->sql('select * from tag','alias');
		foreach ($tags as $alias => $tag)
			foreach ($tag as $key => $field)
				if (strpos($key,'_') && $field != $update[$alias][$key])
					obj::db()->update('tag',$key,$update[$alias][$key],$tag['id']);
*/	}

	function clean_settings() {
		obj::db()->sql('DELETE FROM settings WHERE ((data="YTowOnt9" OR data="") AND lastchange < '.(time()-3600).')',0);
	}

	function send_mails() {
		$data = obj::db()->sql('select * from misc where type = "mail_notify" and data1 < '.time());
		if (!empty($data)) {
			$mail = new mail('html');
			foreach ($data as $send) {
				if (!obj::db()->sql('select id from orders where (id ='.$send['data5'].' and spam = 0)',2)) {
					if ($send['data3']) $mail->text($send['data4'])->send($send['data2'],$send['data3']);
					else $mail->text($send['data4'])->send($send['data2']);
					obj::db()->sql('delete from misc where id ='.$send['id'],0);
				}
			}
		}
	}

	function close_orders() {
		$data = obj::db()->sql('select * from misc where type = "close_order" and data1 < '.time());
		if (!empty($data)) {
			foreach ($data as $delete) {
				if ($id = obj::db()->sql('select id from orders where (id ='.$delete['data2'].' and area = "workshop")',2)) {
					obj::db()->sql('delete from misc where id ='.$delete['id'],0);
					obj::db()->sql('update orders set area = "flea_market", comment_count=comment_count+1, last_comment='.($time = ceil(microtime(true)*1000)).' where id='.$id,0);
					obj::db()->insert('comment',array(0,0,'orders',$id,'Gouf Custom MS-07B-3','gouf@4otaku.ru','255.255.255.255','dummy',
						'Закрыл автоматом за долгое отсутствие интереса и прогресса','Закрыл автоматом за долгое отсутствие интереса и прогресса',
						obj::transform('text')->rudate(),$time,'workshop'));
				}
			}
		}
	}

	function add_to_search() {
		global $search;
		if (!$search) $search = new search();

		$data['post'] = obj::db()->sql('select * from post where area != "deleted" and sortdate > '.(time() - 7200)*1000,'id');
		$data['video'] = obj::db()->sql('select * from video where area != "deleted" and sortdate > '.(time() - 7200)*1000,'id');
		$data['art'] = obj::db()->sql('select * from art where area != "deleted" and sortdate > '.(time() - 7200)*1000,'id');
		$data['news'] = obj::db()->sql('select * from news where area != "deleted" and sortdate > '.(time() - 7200)*1000,'id');
		$data['orders'] = obj::db()->sql('select * from orders where area != "deleted" and sortdate > '.(time() - 7200)*1000,'id');
		$data['comment'] = obj::db()->sql('select * from comment where area != "deleted" and sortdate > '.(time() - 7200)*1000,'id');

		$index = obj::db()->sql('select place, item_id from search');
		if (is_array($index)) foreach ($index as &$one) $one = $one['place'].$one['item_id'];

		foreach ($data as $table => $batch)
			if (is_array($batch))
				foreach ($batch as $id => $item)
					if (!is_array($index) || !in_array($table.$id,$index))
						$search->$table($item,$id);
	}

	function update_search() {
		global $search;
		if (!$search) $search = new search();

		$index = obj::db()->sql('select place, item_id from search order by lastupdate limit 90');
		$index[] = obj::db()->sql('select place, item_id from search where sortdate > '.(ceil(microtime(true)*1000) - 86400*3*1000).' order by lastupdate limit 1',1);
		$index = array_filter($index);

		foreach ($index as $one) $data[$one['place']][$one['item_id']] = obj::db()->sql('select * from '.$one['place'].' where id = '.$one['item_id'],1);

		foreach ($data as $table => $batch)
			if (is_array($batch))
				foreach ($batch as $id => $item) {
						obj::db()->sql('delete from search where place="'.$table.'" and item_id='.$id,0);
						if ($item['area'] != 'deleted' && $item['area']) $search->$table($item,$id);
					}
	}

	function check_dropout_search() {
		global $search;
		$data['post'] = obj::db()->sql('select id, concat(id,"#post") from post where area != "deleted"','id');
		$data['video'] = obj::db()->sql('select id, concat(id,"#video") from video where area != "deleted"','id');
		$data['art'] = obj::db()->sql('select id, concat(id,"#art") from art where area != "deleted"','id');
		$data['news'] = obj::db()->sql('select id, concat(id,"#news") from news where area != "deleted"','id');
		$data['orders'] = obj::db()->sql('select id, concat(id,"#orders") from orders where area != "deleted"','id');
		$data['comment'] = obj::db()->sql('select id, concat(id,"#comment") from comment where area != "deleted"','id');

		$all = array();
		foreach ($data as $key => $table) if (empty($table)) $data[$key] = array();
		unset($table);
		$all = array_merge($all,$data['post'],$data['video'],$data['art'],$data['news'],$data['orders'],$data['comment']);
		unset($data);

		$index = obj::db()->sql('select id, concat(item_id,"#",place) from search','id');
		$index = array_diff($all, $index);
		unset($all);

		$sql = 'INSERT INTO search (`item_id`, `place`) VALUES ("' . str_replace('#','","',implode('"), ("', $index)) . '");';
		obj::db()->sql($sql, 0);
	}

	function search_balance_weights() {
		$types = array('post', 'video', 'art', 'news', 'orders', 'comment');

		$time = time();
		foreach ($types as $type) {
			$data[$type] = obj::db()->sql('select id, `index` from search where place = "'.$type.'" order by md5(id+'.$time.') limit 100','id');
			empty($data[$type]) ? $stop = true : null;
		}

		if (empty($stop)) {
			foreach ($types as $type) {
				$weights = array();
				foreach ($data[$type] as $index) {
					preg_match_all('/=(\d+)\|/u', $index, $tmp_weights);
					$weights[] = array_sum($tmp_weights[1]);
				}
				if (!empty($weights)) {
					$weight = array_sum($weights) / count($weights);
					obj::db()->sql('insert into search_weights values("'.$type.'","'.$weight.'") on duplicate key update weight = '.$weight,0);
				}
			}
		}
	}

	function resize_arts() {
		global $def;

		$arts = obj::db()->sql('select id, md5, extension from art where locate("|need_resize|",tag)');

		if (!empty($arts)) {

			global $imagick; global $path; global $image_class; global $composite;
			include_once ROOT_DIR.SL.'engine'.SL.'upload'.SL.'functions.php';

			foreach ($arts as $art) {
				$imagick =  new $image_class($path = ROOT_DIR.SL.'images'.SL.'booru'.SL.'full'.SL.$art['md5'].'.'.$art['extension']);
				$sizefile = filesize($path);
				$resized = '';

				$sizes = $imagick->getImageWidth().'x'.$imagick->getImageHeight();
				$resize_address = ROOT_DIR.SL.'images'.SL.'booru'.SL.'resized'.SL.$art['md5'].'.jpg';

				if (file_exists($resize_address)) {
					$resized = $sizes;
				} elseif ($imagick->getImageWidth() > $def['booru']['resizewidth']*$def['booru']['resizestep']) {
					if (scale($def['booru']['resizewidth'],$resize_address,95,false))
						$resized = $sizes;
				} elseif ($sizefile > $def['booru']['resizeweight']) {
					if (scale(ceil($imagick->getImageWidth()/2),$resize_address,95,false))
						$resized = $sizes;
				}

				if (!empty($resized)) {
					if ($sizefile > 1024*1024) {
						$sizefile = round($sizefile/(1024*1024),1).' мб';
					} elseif ($sizefile > 1024) {
						$sizefile = round($sizefile/1024,1).' кб';
					} else {
						$sizefile = $sizefile.' б';
					}
					$resized .= 'px; '.$sizefile;
				}

				obj::db()->sql('update art set resized="'.$resized.'", tag=replace(tag,"|need_resize|","|") where id='.$art['id'],0);
			}
		}
	}

	function track_similar_pictures() {
		if (
			!function_exists('puzzle_fill_cvec_from_file') ||
			!function_exists('puzzle_vector_normalized_distance') ||
			!function_exists('puzzle_compress_cvec') ||
			!function_exists('puzzle_uncompress_cvec')
		) {
			return;
		}
/*
 		puzzle_set_max_width(20000);
		puzzle_set_max_height(20000);
 
		$max  = obj::db()->sql('select max(id) from art_similar',2);
		$arts = obj::db()->sql('select id, thumb from art where id > '.($max ? $max : 0).' and area != "deleted" order by id limit 2000');

		foreach ($arts as $art) {
			$image = ROOT_DIR.SL.'images'.SL.'booru'.SL.'thumbs'.SL.'large_'.$art['thumb'].'.jpg';
			$vector = puzzle_fill_cvec_from_file($image);
			$vector = base64_encode(puzzle_compress_cvec($vector));

			obj::db()->insert('art_similar',array($art['id'], $vector, 0, ''),false);
		}
*/
		$all = obj::db()->sql('select id, vector from art_similar where vector != ""','id');
		$arts = obj::db()->sql('select * from art_similar where vector != "" and checked=0 limit 100','id');
		
		if (is_array($all) && is_array($arts)) {
			foreach ($all as $compare_id => $vector) {
				$all[$compare_id] = puzzle_uncompress_cvec(base64_decode($vector));			
			}

			foreach ($arts as $id => $art) { 
				$art['vector'] = puzzle_uncompress_cvec(base64_decode($art['vector'])); 
				$similar = '|';
				foreach ($all as $compare_id => $vector) { 
					if (
						$id != $compare_id &&
						puzzle_vector_normalized_distance($art['vector'], $vector) < 0.4
					) {
						$similar .= $compare_id.'|';
					}					
				}
				obj::db()->update('art_similar',array('checked','similar'),array(1,$similar),$id);
			}
		}
	}
}
