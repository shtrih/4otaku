<?

class Footer_Output extends Module_Output implements Plugins
{
	public function main () {

		return array('year' => date('Y'));
	}
}