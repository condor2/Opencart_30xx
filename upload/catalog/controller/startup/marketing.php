<?php
class ControllerStartupMarketing extends Controller {
	public function index() {
		// Tracking Code
		if (isset($this->request->get['tracking'])) {
			$option = array(
				'expires'  => time() + 3600 * 24 * 1000,
				'path'     => '/',
				'SameSite' => $this->config->get('config_session_samesite')
			);

			setcookie('tracking', $this->request->get['tracking'], $option);

			$this->db->query("UPDATE `" . DB_PREFIX . "marketing` SET clicks = (clicks + 1) WHERE code = '" . $this->db->escape($this->request->get['tracking']) . "'");
		}
	}
}
