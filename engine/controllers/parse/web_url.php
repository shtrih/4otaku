<?

class Parse_Web_Url
{
	public function check_index(& $url) {
		if (empty($url)) {
			return array('function' => 'index', 'class' => 'index');
		}
	}

	public function get_download(& $url) {

		if (isset($url[0]) && $url[0] == 'download') {
			array_shift($url);

			return array('download' => true);
		}
	}

	public function get_area(& $url) {
		if (isset($url[1])) {
			if (
				$url[1] == 'workshop' ||
				$url[1] == 'flea' ||
				$url[1] == 'sprites'
			) {
				$area = array_splice($url, 1, 1);

				return array('area' => current($area));
			}
		}

		return array('area' => 'main');
	}

	public function get_mixed(& $url) {

		if (isset($url[1]) && $url[1] == 'mixed' && isset($url[2])) {
			$mixed = Meta::parse_mixed_url($url[2]);

			array_splice($url, 1, 2);

			return array('mixed' => $mixed, 'function' => 'listing');
		}
	}

	public function get_meta(& $url) {
		$meta_types = array('tag', 'category', 'language', 'author');

		if (isset($url[1]) && in_array($url[1], $meta_types) && isset($url[2])) {

			$meta = array_splice($url, 1, 2);

			return array('meta' => $meta[0], 'alias' => $meta[1], 'function' => 'listing');
		}
	}

	public function get_page(& $url) {
		if (isset($url[1]) && $url[1] == 'page' && isset($url[2]) && is_numeric($url[2])) {

			$page = array_splice($url, 1, 2);

			return array_merge($url, array('page' => end($page), 'function' => 'listing'));
		}

		return $url;
	}

	public function get_id(& $url) {

		if (isset($url[1]) && is_numeric($url[1])) {

			$id = array_splice($url, 1, 1);

			return array_merge($url, array('id' => current($id), 'function' => 'single'));
		}

		return $url;
	}


}