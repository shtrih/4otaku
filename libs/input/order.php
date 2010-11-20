<?
include_once SITE_FDIR.SL.'libs'.SL.'input'.SL.'common.php';
class input__order extends input__common
{
	function add() { 
		global $post; global $db; global $check; global $def; global $transform_meta; 
		global $transform_text; global $cookie; global $add_res; global $sets;
		if (!$transform_meta) $transform_meta = new transform__meta();
		if (!$transform_text) $transform_text = new transform__text();
		if (!$cookie) $cookie = new dinamic__cookie();
		
		if ($post['mail'] && $post['subject']) {
			if ($check->email($post['mail'],false) && $post['mail'] != $def['user']['mail']) {
				$cookie->inner_set('user.mail',$post['mail']);
				
				if (!trim($post['user'])) $post['user'] = $def['user']['author'];
				if ($post['user'] != $def['user']['author']) $cookie->inner_set('user.name',$post['user']); else unset($post['user']);
			
				$category = $transform_meta->category($post['category']);
				$text = $transform_text->format($post['description']);				
				if ($post['subscribe']) $post['subscribe'] = 1;
				
				$db->insert('orders',$insert_data = array($post['subject'],$post['user'],$post['mail'],$post['subscribe'],$text,undo_safety($post['description']),"",
							$category,0,0,$transform_text->rudate(),$time = ceil(microtime(true)*1000),$def['area'][1]));
				$db->insert('versions',array('order',$id = $db->sql('select @@identity from orders',2),
												base64_encode(serialize($insert_data)),$time,$sets['user']['name'],$_SERVER['REMOTE_ADDR']));								
				if ($post['subscribe']) $this->set_events($id,$post['mail']); else $this->set_events($id);
				$add_res['text'] = 'Заказ успешно добавлен. Страница заказа: <a href="/order/'.$id.'/">http://4otaku.ru/order/'.$id.'</a>';
			}
			else $add_res = array('error' => true, 'text' => 'Вы указали неправильный е-мейл.');
		}
		else $add_res = array('error' => true, 'text' => 'Не все обязательные поля заполнены.');
	}
	
	function edit_orders_username() {
		global $post; global $db; global $check;
		if ($check->num($post['id']) && $post['type'] == 'orders') {
			$check->rights();
			$db->update('orders','username',$post['username'],$post['id']);
		}
	}
	
	function edit_orders_mail() {
		global $post; global $db; global $check;
		if (isset($post['subscribe'])) $post['subscribe'] = 1;
		if ($check->email($post['mail'],false) && $check->num($post['id']) && $post['type'] == 'orders') {
			$check->rights();
			$db->update('orders',array('email','spam'),array($post['mail'],$post['subscribe']),$post['id']);
			if ($post['subscribe']) $this->set_events($post['id'],$post['mail']); else $this->set_events($post['id']);
		}
	}		
	
	function change_link() {
		global $post; global $db; global $check;
		if ($check->num($post['id'])) {
			$db->update('orders','link',$post['link'],$post['id']);
			$data = $db->sql('select email, spam from orders where id='.$post['id'],1);
			if ($data['spam']) {	
				if (substr($post['link'],0,1) == '/') $post['link'] = 'http://'.$_SERVER['HTTP_HOST'].$post['link'];
				$this->set_events($post['id'],$data['email']);
				$text = 'В вашем заказе на сайте 4отаку.ру, <a href="http://4otaku.ru/order/'.$id.'/">http://4otaku.ru/order/'.$id.'/</a> добавили ссылку на найденное:<br /><br />
				<a href="'.$post['link'].'">'.$post['link'].'</a>'.$this->unsubscribe($id);
				$db->insert('misc',array('mail_notify',0,$data['email'],'',$text,$post['id']));				
			}
			else $this->set_events($post['id']);			
		}
	}		
}
