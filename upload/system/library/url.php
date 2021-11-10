<?php
/**
 * @package		OpenCart
 * @author		Daniel Kerr
 * @copyright	Copyright (c) 2005 - 2017, OpenCart, Ltd. (https://www.opencart.com/)
 * @license		https://opensource.org/licenses/GPL-3.0
 * @link		https://www.opencart.com
*/

/**
* URL class
*/
class Url {
	private $url;
	private $rewrite = array();

	/**
	 * Constructor.
	 *
	 * @param string $url
	 * @param string $ssl Depricated
	 */
	public function __construct($url) {
		$this->url = $url;
	}

	/**
	 * Add a rewrite method to the URL system
	 *
	 * @param Controller $rewrite
	 *
	 * @return void
	 */
	public function addRewrite($rewrite) {
		$this->rewrite[] = $rewrite;
	}

	/**
	 * Generates a URL
	 *
	 * @param string        $route
	 * @param string|array	$args
	 * @param bool			$js
	 *
	 * @return string
	 */
	public function link($route, $args = '', $js = false) {
		$url = $this->url . 'index.php?route=' . $route;

		if ($args) {
			if (is_array($args)) {
				$url .= '&' . http_build_query($args);
			} else {
				$url .= '&' . trim($args, '&');
			}
		}

		foreach ($this->rewrite as $rewrite) {
			$url = $rewrite->rewrite($url);
		}

		if (!$js) {
			return str_replace('&', '&amp;', $url);
		} else {
			return $url;
		}
	}
}