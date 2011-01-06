<?

class input__comment extends input__common 
{
	function add() { 
		global $check; global $url; global $cookie; global $def;
		if (!$cookie) $cookie = new dynamic__cookie();		
		
		if (!query::$post['name']) query::$post['name'] = $def['user']['name'];
		elseif (query::$post['name'] != $def['user']['name']) $cookie->inner_set('user.name',query::$post['name']);
		if (!query::$post['mail']) query::$post['mail'] = $def['user']['mail'];
		elseif (query::$post['mail'] != $def['user']['mail']) $cookie->inner_set('user.mail',query::$post['mail']);
		
		query::$post['name'] = preg_replace('/#.*$/','',query::$post['name']);
		
		$comment = obj::transform('text')->format(query::$post['text']);
		
		if ($url[1] == 'order') $table = 'orders'; else $table = $url[1];
		$field = $table == 'news' ? 'url' : 'id';
		$item_id = in_array($url[2], $def['area']) ? $url[3] : $url[2];
		
		if (substr($item_id,0,3) == 'cg_') {
			$area = $def['area'][0];
		} else {
			$area = obj::db()->sql('select area from '.$table.' where '.$field.'="'.$item_id.'"',2);
		}
	
		if (trim(strip_tags(str_replace('<img', 'img', $comment))) && $area) {

			if (query::$post['parent'] && !($rootparent = obj::db()->sql('select rootparent from comment where id='.query::$post['parent'],2)))
				$rootparent = query::$post['parent'];

			obj::db()->insert('comment',array($rootparent,query::$post['parent'],$table,$item_id,query::$post['name'],query::$post['mail'],
						$_SERVER['REMOTE_ADDR'],$_COOKIE['settings'],$comment,query::$post['text'],$date = obj::transform('text')->rudate(true),
						$time = ceil(microtime(true)*1000),$area));

			if ($table == 'news') {
				obj::db()->sql('update news set comment_count=comment_count+1, last_comment='.$time.' where url="'.$item_id.'"',0);
			} elseif ($table == 'art' && substr($item_id,0,3) == 'cg_') {
				obj::db('sub')->sql('update w8m_art set comment_count=comment_count+1, last_comment='.$time.' where id='.substr($item_id,3),0);
			} else {
				obj::db()->sql('update '.$table.' set comment_count=comment_count+1, last_comment='.$time.' where id='.$item_id,0);
			}
			
			if ($table == 'orders') {
				$data = obj::db()->sql('select email, spam from orders where id='.$item_id,1);
				if ($data['spam'] && $data['email'] != query::$post['mail']) {	
					$this->set_events($item_id,$data['email']);
					$text = 'В вашем заказе на сайте 4отаку.ру, <a href="http://4otaku.ru/order/'.$item_id.'/">http://4otaku.ru/order/'.$item_id.'/</a> '.query::$post['name'].' '.(query::$post['name'] != $def['user']['name'] ? 'оставил' : 'оставлен').' новый комментарий. <a href="http://4otaku.ru/order/'.$item_id.'/comments/all#comment-'.obj::db()->sql('select @@identity from comment',2).'">Читать</a>. '.$this->unsubscribe($item_id);
					obj::db()->insert('misc',array('mail_notify',0,$data['email'],'',$text,$item_id));				
				} else {
					$this->set_events($item_id);
				}
			}
		}
	}
	
	function edit() {
		global $check; global $url;

		$check->rights();
		
		$comment = obj::transform('text')->format(query::$post['text']);
		if (str_replace('*','',query::$post['mail']))
			obj::db()->update('comment',array('username','email','text','pretty_text'),array(query::$post['author'],query::$post['mail'],$comment,query::$post['text']),query::$post['id']);
		else
			obj::db()->update('comment',array('username','text','pretty_text'),array(query::$post['author'],$comment,query::$post['text']),query::$post['id']);		
	}	
	
	function delete() {
		global $check; global $url;

		$check->rights(); 
		
		if (isset(query::$post['sure'])) {
		
			$comment = obj::db()->sql('select parent,rootparent from comment where id='.query::$post['id'],1);		
			
			obj::db()->update('comment','area','deleted',query::$post['id']);
			obj::db()->update('comment',array('parent','rootparent'),array($comment['parent'],$comment['rootparent']),query::$post['id'],'parent');

			if (!$comment['rootparent']) {
				$comments = obj::db()->sql('select * from comment where rootparent='.query::$post['id'],'id');
				if (!empty($comments)) foreach ($comments as $id => $one) {
					$temp = $one; $i = 0;
					while($temp['rootparent'] && $i < 20) {
						$i++; 
						$rootparent = $temp['parent'];
						$temp = $comments[$temp['parent']];
					}
					obj::db()->update('comment','rootparent',$rootparent,$id);
				}
			}
			
			if ($url[1] == 'news') 
				obj::db()->sql('update news set comment_count=comment_count-1 where url="'.$url[2].'"',0);
			else
				obj::db()->sql('update '.($url[1] == 'order' ? 'orders' : $url[1]).' set comment_count=comment_count-1 where id='.$url[2],0);			
		}
	}	
}
