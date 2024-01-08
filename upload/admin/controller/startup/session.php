<?php
class ControllerStartupSession extends Controller {
	public function index() {
		$session = new Session($this->config->get('session_engine'), $this->registry);
		$this->registry->set('session', $session);

		if (isset($this->request->cookie[$this->config->get('session_name')])) {
			$session_id = $this->request->cookie[$this->config->get('session_name')];
		} else {
			$session_id = '';
		}

		$session->start($session_id);

		// Update the session lifetime
		if ($this->config->get('config_session_expire')) {
			$this->config->set('session_expire', $this->config->get('config_session_expire'));
		}

		// Require higher security for session cookies
		$option = [
			'expires'  => $this->config->get('config_session_expire') ? time() + (int)$this->config->get('config_session_expire') : 0,
			'path'     => $this->config->get('session_path'),
			'secure'   => $this->request->server['HTTPS'],
			'httponly' => false,
			'SameSite' => $this->config->get('config_session_samesite')
		];

		setcookie($this->config->get('session_name'), $session->getId(), $option);
	}
}
