<?

class Footer_Web extends Module_Web implements Plugins
{
	public $url_parts = array();
	
	public function postprocess ($data) {
		
		return $data;
	}
}