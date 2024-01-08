<?php
namespace Cache;
class File {
	private $expire;

	/**
	 * Constructor
	 *
	 * @param int $expire
	 */
	public function __construct($expire = 3600) {
		$this->expire = $expire;
	}

	/**
	 * Get
	 *
	 * @param string $key
	 *
	 * @return array|string|null
	 */
	public function get($key) {
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files) {
			return json_decode(file_get_contents($files[0]), true);
		} else {
			return array();
		}
	}

	/**
	 * Set
	 *
	 * @param string            $key
	 * @param array|string|null $value
	 *
	 * @return void
	 */
	public function set($key, $value, $expire = 0) {
		$this->delete($key);

		if (!$expire) {
			$expire = $this->expire;
		}

		file_put_contents(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.' . (time() + $expire), json_encode($value));
	}

	/**
	 * Delete
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function delete($key) {
		$files = glob(DIR_CACHE . 'cache.' . preg_replace('/[^A-Z0-9\._-]/i', '', $key) . '.*');

		if ($files) {
			foreach ($files as $file) {
				if (!@unlink($file)) {
					clearstatcache(false, $file);
				}
			}
		}
	}

	/**
	 * Destructor
	 */
	public function __destruct() {
		$files = glob(DIR_CACHE . 'cache.*');

		if ($files && mt_rand(1, 100) == 1) {
			foreach ($files as $file) {
				$time = substr(strrchr($file, '.'), 1);

				if ($time < time()) {
					if (!@unlink($file)) {
						clearstatcache(false, $file);
					}
				}
			}
		}
	}
}
