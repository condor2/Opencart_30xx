<?php
class ControllerStartupStartup extends Controller {
	public function index() {
		// Store
		$this->load->model('setting/store');

		$hostname = ($this->request->server['HTTPS'] ? 'https://' : 'http://') . str_replace('www.', '', $this->request->server['HTTP_HOST']) . rtrim(dirname($this->request->server['PHP_SELF']), '/.\\') . '/';

		$store_info = $this->model_setting_store->getStoreByHostname($hostname);

		// Store
		if (isset($this->request->get['store_id'])) {
			$this->config->set('config_store_id', (int)$this->request->get['store_id']);
		} elseif ($store_info) {
			$this->config->set('config_store_id', $store_info['store_id']);
		} else {
			$this->config->set('config_store_id', 0);
		}

		if (!$store_info) {
			$this->config->set('config_url', HTTP_SERVER);
		}

		// Settings
		$this->load->model('setting/setting');

		$results = $this->model_setting_setting->getSettings($this->config->get('config_store_id'));

		foreach ($results as $result) {
			if (!$result['serialized']) {
				$this->config->set($result['key'], $result['value']);
			} else {
				$this->config->set($result['key'], json_decode($result['value'], true));
			}
		}

		// Url
		$this->registry->set('url', new Url($this->config->get('config_url')));

		// Set time zone
		if ($this->config->get('config_timezone')) {
			date_default_timezone_set($this->config->get('config_timezone'));

			// Sync PHP and DB time zones.
			$this->db->query("SET time_zone = '" . $this->db->escape(date('P')) . "'");
		}

		// Theme
		$this->config->set('template_cache', $this->config->get('developer_theme'));

		// Response output compression level
		if ($this->config->get('config_compression')) {
			$this->response->setCompression($this->config->get('config_compression'));
		}


		// Language
		$code = '';

		$this->load->model('localisation/language');

		$languages = $this->model_localisation_language->getLanguages();

		if (isset($this->session->data['language'])) {
			$code = $this->session->data['language'];
		}

		if (isset($this->request->cookie['language']) && !array_key_exists($code, $languages)) {
			$code = $this->request->cookie['language'];
		}

		// Language Detection
		if (!empty($this->request->server['HTTP_ACCEPT_LANGUAGE']) && !array_key_exists($code, $languages)) {
			$detect = '';

			$browser_languages = explode(',', $this->request->server['HTTP_ACCEPT_LANGUAGE']);

			// Try using local to detect the language
			foreach ($browser_languages as $browser_language) {
				foreach ($languages as $key => $value) {
					if ($value['status']) {
						$locale = explode(',', $value['locale']);

						if (in_array($browser_language, $locale)) {
							$detect = $key;
							break 2;
						}
					}
				}	
			}

			if (!$detect) { 
				// Try using language folder to detect the language
				foreach ($browser_languages as $browser_language) {
					if (array_key_exists(strtolower($browser_language), $languages)) {
						$detect = strtolower($browser_language);

						break;
					}
				}
			}

			$code = $detect ? $detect : '';
		}

		if (!array_key_exists($code, $languages)) {
			$code = $this->config->get('config_language');
		}

		if (!isset($this->session->data['language']) || $this->session->data['language'] != $code) {
			$this->session->data['language'] = $code;
		}

		// Set a new language cookie if the code does not match the current one
		if (!isset($this->request->cookie['language']) || $this->request->cookie['language'] != $code) {
			$option = array(
				'expires'  => time() + 60 * 60 * 24 * 30,
				'path'     => '/',
				'SameSite' => 'Lax'
			);

			setcookie('language', $code, $option);
		}

		// Overwrite the default language object
		$language = new Language($code);
		$language->load($code);

		$this->registry->set('language', $language);

		// Set the config language_id
		$this->config->set('config_language_id', $languages[$code]['language_id']);

		// Customer
		$customer = new Cart\Customer($this->registry);
		$this->registry->set('customer', $customer);

		// Customer Group
		if (isset($this->session->data['customer']) && isset($this->session->data['customer']['customer_group_id'])) {
			// For API calls
			$this->config->set('config_customer_group_id', $this->session->data['customer']['customer_group_id']);
		} elseif ($this->customer->isLogged()) {
			// Logged in customers
			$this->config->set('config_customer_group_id', $this->customer->getGroupId());
		} elseif (isset($this->session->data['guest']) && isset($this->session->data['guest']['customer_group_id'])) {
			$this->config->set('config_customer_group_id', $this->session->data['guest']['customer_group_id']);
		} else {
			$this->config->set('config_customer_group_id', $this->config->get('config_customer_group_id'));
		}

		// Tracking Code
		if (isset($this->request->get['tracking'])) {
			$option = array(
				'expires'  => time() + 3600 * 24 * 1000,
				'path'     => '/',
				'SameSite' => $this->config->get('session_samesite')
			);

			setcookie('tracking', $this->request->get['tracking'], $option);

			$this->db->query("UPDATE `" . DB_PREFIX . "marketing` SET clicks = (clicks + 1) WHERE code = '" . $this->db->escape($this->request->get['tracking']) . "'");
		}

		// Currency
		$code = '';

		$this->load->model('localisation/currency');

		$currencies = $this->model_localisation_currency->getCurrencies();

		if (isset($this->session->data['currency'])) {
			$code = $this->session->data['currency'];
		}

		if (isset($this->request->cookie['currency']) && !array_key_exists($code, $currencies)) {
			$code = $this->request->cookie['currency'];
		}

		if (!array_key_exists($code, $currencies)) {
			$code = $this->config->get('config_currency');
		}

		if (!isset($this->session->data['currency']) || $this->session->data['currency'] != $code) {
			$this->session->data['currency'] = $code;
		}

		// Set a new currency cookie if the code does not match the current one
		if (!isset($this->request->cookie['currency']) || $this->request->cookie['currency'] != $code) {
			$option = array(
				'expires'  => time() + 60 * 60 * 24 * 30,
				'path'     => '/',
				'SameSite' => 'Lax'
			);

			setcookie('currency', $code, $option);
		}

		$this->registry->set('currency', new Cart\Currency($this->registry));

		// Tax
		$this->registry->set('tax', new Cart\Tax($this->registry));

		if (isset($this->session->data['shipping_address']['country_id']) && isset($this->session->data['shipping_address']['zone_id'])) {
			$this->tax->setShippingAddress($this->session->data['shipping_address']['country_id'], $this->session->data['shipping_address']['zone_id']);
		} elseif ($this->config->get('config_tax_default') == 'shipping') {
			$this->tax->setShippingAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		if (isset($this->session->data['payment_address']['country_id']) && isset($this->session->data['payment_address']['zone_id'])) {
			$this->tax->setPaymentAddress($this->session->data['payment_address']['country_id'], $this->session->data['payment_address']['zone_id']);
		} elseif ($this->config->get('config_tax_default') == 'payment') {
			$this->tax->setPaymentAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));
		}

		$this->tax->setStoreAddress($this->config->get('config_country_id'), $this->config->get('config_zone_id'));

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