<?php
class ControllerStartupSession extends Controller {
	public function index() {
		// Session
		if (isset($this->request->cookie[$this->config->get('session_name')])) {
			$session_id = $this->request->cookie[$this->config->get('session_name')];
		} else {
			$session_id = '';
		}

		$this->session->start($session_id);

		// Setting the cookie path to the store front so admin users can login to cutomers accounts.
		$path = dirname($_SERVER['PHP_SELF']);

		$path = substr($path, 0, strrpos($path, '/')) . '/';

		// Require higher security for session cookies
		$option = [
			'expires'  => time() + $this->config->get('session_expire'),
			'path'     => !empty($_SERVER['PHP_SELF']) ? $path : '',
			'secure'   => $this->request->server['HTTPS'],
			'httponly' => false,
			'SameSite' => 'Strict'
		];

		oc_setcookie($this->config->get('session_name'), $this->session->getId(), $option);
	}
}