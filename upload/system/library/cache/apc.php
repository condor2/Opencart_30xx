<?php
namespace Cache;
class APC {
	private $expire;
	private $active;

	public function __construct($expire = 3600) {
		$this->expire = $expire;
		$this->active = function_exists('apc_cache_info') && ini_get('apc.enabled');
	}

	public function get($key) {
		return $this->active ? apc_fetch(CACHE_PREFIX . $key) : false;
	}

	public function set($key, $value, $expire = 0) {
		if (!$expire) {
			$expire = $this->expire;
		}

		if ($this->active) {
			apc_store(CACHE_PREFIX . $key, $value, $expire);
		}
	}

	public function delete($key) {
		if ($this->active) {
			$cache_info = apc_cache_info('user');

			$cache_list = $cache_info['cache_list'];

			foreach ($cache_list as $entry) {
				if (str_starts_with($entry['info'], CACHE_PREFIX . $key)) {
					apcu_delete($entry['info']);
				}
			}
		}
	}
}
