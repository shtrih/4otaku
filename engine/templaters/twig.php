<?

	include_once 'Twig'.SL.'Autoloader.php';
	
	Twig_Autoloader::register();

	function twig_load_template($template, $params) {
		$loader = new Twig_Loader_Filesystem(ROOT.SL.'templates');
		
		$twig = new Twig_Environment($loader, array(
		  'cache' => ROOT.SL.'cache'
		));
	
		$template = $twig->loadTemplate($template.SL.'index.html');
		
		$template->display($params);
	}