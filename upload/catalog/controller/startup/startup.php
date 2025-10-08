<?php
class ControllerStartupStartup extends Controller {
	public function __isset($key) {
		// To make sure that calls to isset also support dynamic properties from the registry
		// See https://www.php.net/manual/en/language.oop5.overloading.php#object.isset
		if ($this->registry) {
			if ($this->registry->get($key) !== null) {
				return true;
			}
		}

		return false;
	}

	public function index() {
		// Theme
		$this->config->set('template_cache', $this->config->get('developer_theme'));

		// Weight
		$this->registry->set('weight', new Cart\Weight($this->registry));

		// Length
		$this->registry->set('length', new Cart\Length($this->registry));

		// Cart
		$this->registry->set('cart', new Cart\Cart($this->registry));

		// Encryption
		$this->registry->set('encryption', new Encryption());
	}
}
