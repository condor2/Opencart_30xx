<?php
namespace Cache;
class Mem {
	private $expire;
	private $memcache;
	const CACHEDUMP_LIMIT = 9999;

	public function __construct($expire = 3600) {
		$this->expire = $expire;

		$this->memcache = new \Memcache();
		$this->memcache->pconnect(CACHE_HOSTNAME, CACHE_PORT);
	}

	public function get($key) {
		return $this->memcache->get(CACHE_PREFIX . $key);
	}

	public function set($key, $value, $expire = 0) {
		if (!$expire) {
			$expire = $this->expire;
		}

		$this->memcache->set(CACHE_PREFIX . $key, $value, MEMCACHE_COMPRESSED, $expire);
	}

	public function delete($key) {
		$this->memcache->delete(CACHE_PREFIX . $key);
	}
}