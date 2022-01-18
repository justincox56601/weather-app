<?php
/**
 * Core App Class
 */

class Core{
	protected $currentController = 'Pages';
	protected $currentMethod = 'index';
	protected $params = [];

	public function __construct(){
		$url = $this->getUrl();
		
		//set current controller
		if(file_exists('../app/controllers/' . ucwords($url[0] . '.php'))){
			$this->currentController = ucwords($url[0]);
			array_shift($url);
		}

		//require the controller
		require_once '../app/controllers/' . $this->currentController . '.php';
		$this->currentController = new $this->currentController;

		//set the current method
		if(isset($url[0]) && method_exists($this->currentController, $url[0])){
			$this->currentMethod = $url[0];
			array_shift($url);
		}

		//set params
		$this->params = ($url)? array_values($url) : [];

		call_user_func_array([$this->currentController, $this->currentMethod], $this->params);

	}

	private function getUrl(){
		if(isset($_GET['url'])){
			$url = rtrim($_GET['url'], '/');
			$url = filter_var($url, FILTER_SANITIZE_URL);
			$url = explode('/', $url);
			return $url;
		}
	}
}