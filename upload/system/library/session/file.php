<?php
namespace Session;
class File {
	private $config;

	public function __construct($registry) {
		$this->config = $registry->get('config');
	}

	public function read($session_id) {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		if (is_file($file)) {
			return json_decode($data, true);
		} else {
			return array();
		}
	}

	public function write($session_id, $data) {
		file_put_contents(DIR_SESSION . 'sess_' . basename($session_id), json_encode($data));

		return true;
	}

	public function destroy($session_id) {
		$file = DIR_SESSION . 'sess_' . basename($session_id);

		if (is_file($file)) {
			unlink($file);
		}
	}

	public function gc() {
		if (round(rand(1, $this->config->get('session_divisor') / $this->config->get('session_probability'))) == 1) {
			$expire = time() - $this->config->get('session_expire');

			$files = glob(DIR_SESSION . 'sess_*');

			foreach ($files as $file) {
				if (is_file($file) && filemtime($file) < $expire) {
					unlink($file);
				}
			}
		}
	}
}