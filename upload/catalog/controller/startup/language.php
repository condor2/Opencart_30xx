<?php
class ControllerStartupLanguage extends Controller {
    public function index() {
		// Language
        $this->load->model('localisation/language');

        $languages = $this->model_localisation_language->getLanguages();

        $language_codes = array_column($languages, 'language_id', 'code');

        $code = '';

        if (!empty($this->request->cookie['language']) && array_key_exists($this->request->cookie['language'], $language_codes)) {
            $code = $this->request->cookie['language'];
        }

		// Language Detection
        if (!$code && !empty($this->request->server['HTTP_ACCEPT_LANGUAGE'])) {
            $browser_languages = explode(',', $this->request->server['HTTP_ACCEPT_LANGUAGE']);
            $detect = '';

            foreach ($browser_languages as $browser_language) {
                $browser_language = strtolower(trim($browser_language));

                foreach ($languages as $key => $value) {
                    if ($value['status']) {
                        $locale = array_map('strtolower', explode(',', $value['locale']));

                        if (in_array($browser_language, $locale)) {
                            $detect = $key;
                            break 2;
                        }
                    }
                }
            }

            // Fallback: verificare cod limbă folder
            if (!$detect) {
                foreach ($browser_languages as $browser_language) {
                    $browser_language = strtolower(trim($browser_language));
                    if (array_key_exists($browser_language, $language_codes)) {
                        $detect = $browser_language;
                        break;
                    }
                }
            }

            $code = $detect ?: '';
        }

		if (!array_key_exists($code, $language_codes)) {
			$code = $this->config->get('config_language');
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
        $this->config->set('config_language_id', $language_codes[$code]);
        $this->config->set('config_language', $code);
    }
}
