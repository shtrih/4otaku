<?

class side__sidebar extends engine
{
	function __construct() {
		global $url; global $searchbutton;
		$known = array('msie', 'firefox');
		$agent = strtolower($_SERVER['HTTP_USER_AGENT']);
		$pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9]+(?:\.[0-9]+)?)#';
		if (preg_match_all($pattern, $agent, $matches))
			$searchbutton[end($matches['browser'])] = end($matches['version']);
	}

	function comments() {
		global $sets; global $url;

		if ($url[1] == "order") {
			$area = "orders";
		} elseif ($url[1] == "search") {
			if ($url[2] == "a") $area = 'art';
			else if ($url[2] == "p") $area = 'post';
			else if ($url[2] == "v") $area = 'video';
		} else {
			$area = $url[1];
		}

		if (!($return = obj::db()->sql('select * from comment where (place="'.$area.'" and area != "deleted") order by sortdate desc limit '.$sets['pp']['latest_comments']*5,'sortdate'))) {
			$return = obj::db()->sql('select * from comment where area != "deleted" order by sortdate desc limit '.$sets['pp']['latest_comments']*5,'sortdate');
		} else {
			$link = $area == 'orders' ? 'order' : $area;
		}
		if (is_array($return)) {
			$used = array();
			foreach ($return as $key => $one) {
				if (in_array($one['place'].'-'.$one['post_id'],$used)) unset ($return[$key]);
				$used[] = $one['place'].'-'.$one['post_id'];
			}
			krsort($return);
			$return = array_slice($return,0,$sets['pp']['latest_comments'],true);
			foreach ($return as &$comment) {
				if ($comment['place'] != 'art') $comment['title'] = obj::db()->sql('select title from '.$comment['place'].' where ('.($comment['place']== 'news' ? 'url' : 'id').'="'.$comment['post_id'].'") limit 1',2);
				else {
					if (substr($comment['post_id'],0,3) == 'cg_') $comment['title'] = 'CG №'.substr($comment['post_id'],3);
					else $comment['title'] = 'Изображение №'.$comment['post_id'];
				}
				$comment['text'] = obj::transform('text')->cut_long_text(strip_tags($comment['text'],'<br><em><strong><s>'),100);
				$comment['text'] = preg_replace('/(<br(\s[^>]*)?>\n*)+/si','<br />',$comment['text']);
				$comment['text'] = obj::transform('text')->cut_long_words($comment['text']);
				$comment['href'] =  '/'.($comment['place'] == "orders" ? "order" : $comment['place']).'/'.$comment['post_id'].'/';
				$comment['username'] = mb_substr($comment['username'],0,30);
			}
			return array('data' => $return, 'link' => $link);
		}
	}

	function update() {
		$return = obj::db()->sql('select * from updates order by sortdate desc limit 1',1);
		$return['text'] = obj::transform('text')->cut_long_text(strip_tags($return['text'],'<br>'),100);
		$return['text'] = preg_replace('/(<br(\s[^>]*)?>\n*)+/si','<br />',$return['text']);
		$return['text'] = obj::transform('text')->cut_long_words($return['text']);
		$return['author'] = mb_substr($return['username'],0,20);
		$return['post_title'] = obj::db()->sql('select title from post where id = '.$return['post_id'],2);
		return $return;
	}

	function orders() {
		global $sets;
		if ($return = obj::db()->sql('select id, username, title, text, comment_count from orders where area="workshop"')) {
			shuffle($return);
			return array_slice($return, 0, $sets['pp']['random_orders']);
		}
	}

	function tags() {
		global $sets; global $def; global $url;

		if ($url['area'] != $def['area'][0] && $url['area'] != $def['area'][2]) $area = $url[1].'_'.$def['area'][0];
		else $area = $url[1].'_'.$url['area'];

		$words = array(
			$def['type'][0] => array('запись','записи','записей'),
			$def['type'][1] => array('видео','видео','видео'),
			$def['type'][2] => array('арт','арта','артов')
		);

		return $this->tag_cloud(22,8,$area,$words[$url[1]],$sets['pp']['tags']);
	}

	function art_tags() {
		global $data; global $check; global $url; 
		
		if (in_array($url['area'], def::get('area')) && $url['area'] != 'workshop') {
			$area = $url['area'];
		} else { 
			$area = def::get('area',0);
		}

		if (is_array($data['main']['art']['thumbs'])) {
			$page_flag = true;
			foreach ($data['main']['art']['thumbs'] as $art)
				if (is_array($art['meta']['tag']))
					foreach ($art['meta']['tag'] as $alias => $tag)
						if ($tags[$alias]) $tags[$alias]['count']++;
						else $tags[$alias] = array('name' => $tag['name'], 'color' => $tag['color'], 'count' => 1);
		}
		elseif (is_array($data['main']['art'][0]['meta']['tag'])) {
			$page_flag = false;
			foreach ($data['main']['art'][0]['meta']['tag'] as $alias => $tag)
				if ($tags[$alias]) $tags[$alias]['count']++;
				else $tags[$alias] = array('name' => $tag['name'], 'color' => $tag['color'], 'count' => 1);
		}
		unset($tags['prostavte_tegi'],$tags['tagme'],$tags['deletion_request']);

		if (!empty($tags)) {
				$where = 'where alias="'.implode('" or alias="',array_keys($tags)).'"';
				$global = obj::db()->sql('select alias, art_'.$area.' from tag '.$where,'alias');
			if ($page_flag) {
				foreach ($global as $alias => $global_count)
					$return[$tags[$alias]['count']*$global_count.'.'.rand(0,10000)] = array('alias' => $alias, 'num' => $global_count) + $tags[$alias];

				krsort($return);
				$return = array_slice($return,0,25);
				shuffle($return);				
			} else {
				foreach ($global as $alias => $global_count)
					$return[$alias] = array('alias' => $alias, 'num' => $global_count) + $tags[$alias];
				
				uasort($return,array('self','name_sort'));
			}
			return $return;
		}
	}
	
	static function name_sort($a, $b) {
		return (
			obj::transform('text')->strtolower_ru($a['name']) > 
			obj::transform('text')->strtolower_ru($b['name'])
		) ? 1 : -1;
	}

	function admin_functions() {
		return true;
	}

	function quicklinks() {
		return true;
	}

	function board_list() {
		return obj::db()->sql('select alias, name from category where locate("|board|",area) order by id','alias');
	}

	function masstag() {
		return obj::db()->sql('select alias, name from category where locate("|art|",area) order by id','alias');
	}
}
