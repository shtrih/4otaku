<?

class Output_Post extends Output_Abstract
{	
	public function single($query) {
		$post = Globals::db()->get_row('post',$query['id']);
		
		$items = $this->call->get_items($post['id']);
		
		$return['posts'] = array(
			$post['id'] => array_merge($post, current($items))
		);

		return $return;
	}	
	
	public function listing($query) {
		$return = array();	
		
		// TODO: perpage и area=main костыли, избавиться
		$perpage = 5;
		
		$page = isset($query['page']) && $query['page'] > 0 ? $query['page'] : 1;
		
		$start = ($page - 1) * $perpage;
		
		$listing_condition = $this->call->build_listing_condition($query);
		
		$condition = $listing_condition . " order by date desc limit $start, $perpage";
		
		$return['posts'] = Globals::db()->get_vector('post',$condition);

		$keys = array_keys($return['posts']);
		
		$items = $this->call->get_items($keys);
		
		foreach ($return['posts'] as $id => & $post) {
			$post = array_merge($post, (array) $items[$id]);
		}		
		
		$count = Globals::db()->get_field('post',$listing_condition,'count(*)');
		
		$return['curr_page'] = $page;
		$return['pagecount'] = ceil($count / $perpage);

		return $return;
	}
	
	public function build_listing_condition($query) {	
		
		return "area = 'main'";	
	}		
	
	public function get_items($ids) {
		$ids = (array) $ids;
		
		$condition = "item_id in (".str_repeat('?,',count($ids)-1)."?)";
		
		$items = Globals::db()->get_table('post_items',$condition,'item_id,type,sort_number,data',$ids);
		
		$return = array();
		
		if (!empty($items)) {
			foreach ($items as $item) {
				if ($item['type'] != 'link') {
					$return[$item['item_id']][$item['type']][$item['sort_number']]
						 = Crypt::unpack_array($item['data']);
				} else {
					$data = Crypt::unpack_array($item['data']);
					
					$crc = crc32($data['name'].'&'.$data['size'].'&'.$data['sizetype']);
					
					$link = & $return[$item['item_id']]['link'][$crc];
					
					if (!empty($link)) {
						$link['url'][$data['url']] = $data['alias'];
					} else {
						$link = $data;
						$link['url'] = array($link['url'] => $link['alias']);
					}
				}
			}
		}
		
		return $return;	
	}	
}