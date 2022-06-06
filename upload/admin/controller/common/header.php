<?php
class ControllerCommonHeader extends Controller {
	public function index() {
		if (is_file(DIR_IMAGE . $this->config->get('config_icon'))) {
			$this->document->addLink(HTTP_CATALOG . 'image/' . $this->config->get('config_icon'), 'icon');
		}

		$data['title'] = $this->document->getTitle();

		$data['base'] = HTTP_SERVER;
		$data['description'] = $this->document->getDescription();
		$data['keywords'] = $this->document->getKeywords();
		$data['links'] = $this->document->getLinks();
		$data['styles'] = $this->document->getStyles();
		$data['scripts'] = $this->document->getScripts();
		$data['lang'] = $this->language->get('code');
		$data['direction'] = $this->language->get('direction');

		$this->load->language('common/header');

		if (!isset($this->request->get['user_token']) || !isset($this->session->data['user_token']) || ($this->request->get['user_token'] != $this->session->data['user_token'])) {
			$data['logged'] = '';

			$data['home'] = $this->url->link('common/login', '', true);
		} else {
			$data['logged'] = true;

			$data['home'] = $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true);
			$data['logout'] = $this->url->link('common/logout', 'user_token=' . $this->session->data['user_token'], true);
			$data['profile'] = $this->url->link('common/profile', 'user_token=' . $this->session->data['user_token'], true);
			
			$this->load->model('tool/image');

			$data['firstname'] = '';
			$data['lastname'] = '';
			$data['user_group'] = '';
			$data['image'] = $this->model_tool_image->resize('profile.png', 45, 45);

			$this->load->model('user/user');

			$user_info = $this->model_user_user->getUser($this->user->getId());

			if ($user_info) {
				$data['firstname'] = $user_info['firstname'];
				$data['lastname'] = $user_info['lastname'];
				$data['username']  = $user_info['username'];
				$data['user_group'] = $user_info['user_group'];

				if (is_file(DIR_IMAGE . html_entity_decode($user_info['image'], ENT_QUOTES, 'UTF-8'))) {
					$data['image'] = $this->model_tool_image->resize(html_entity_decode($user_info['image'], ENT_QUOTES, 'UTF-8'), 45, 45);
				}
			}

			// Orders
			$this->load->model('sale/order');

			// Processing Orders
			$data['processing_status_total'] = $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $this->config->get('config_processing_status'))));
			$data['processing_status'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&filter_order_status=' . implode(',', $this->config->get('config_processing_status')), true);

			// Complete Orders
			$data['complete_status_total'] = $this->model_sale_order->getTotalOrders(array('filter_order_status' => implode(',', $this->config->get('config_complete_status'))));
			$data['complete_status'] = $this->url->link('sale/order', 'user_token=' . $this->session->data['user_token'] . '&filter_order_status=' . implode(',', $this->config->get('config_complete_status')), true);

			// Returns
			$this->load->model('sale/return');

			$return_total = $this->model_sale_return->getTotalReturns(array('filter_return_status_id' => $this->config->get('config_return_status_id')));

			$data['return_total'] = $return_total;

			$data['return'] = $this->url->link('sale/return', 'user_token=' . $this->session->data['user_token'], true);

			// Customers
			$this->load->model('report/online');

			$data['online_total'] = $this->model_report_online->getTotalOnline();

			$data['online'] = $this->url->link('report/online', 'user_token=' . $this->session->data['user_token'], true);

			$this->load->model('customer/customer_approval');

			$customer_total = $this->model_customer_customer_approval->getTotalCustomerApprovals(array('filter_type' => 'customer'));

			$data['customer_total'] = $customer_total;
			$data['customer_approval'] = $this->url->link('customer/customer_approval', 'user_token=' . $this->session->data['user_token'], true);

			// Products
			$this->load->model('catalog/product');

			$product_total = $this->model_catalog_product->getTotalProducts(array('filter_quantity' => 0));

			$data['product_total'] = $product_total;

			$data['product'] = $this->url->link('catalog/product', 'user_token=' . $this->session->data['user_token'] . '&filter_quantity=0', true);

			// Reviews
			$this->load->model('catalog/review');

			$review_total = $this->model_catalog_review->getTotalReviews(array('filter_status' => 0));

			$data['review_total'] = $review_total;

			$data['review'] = $this->url->link('catalog/review', 'user_token=' . $this->session->data['user_token'] . '&filter_status=0', true);

			// Affliate
			$this->load->model('marketing/affiliate');

			$affiliate_total = $this->model_marketing_affiliate->getTotalAffiliates(array('filter_status' => 0));

			$data['affiliate_total'] = $affiliate_total;
			$data['affiliate_approval'] = $this->url->link('marketing/affiliate', 'user_token=' . $this->session->data['user_token'] . '&filter_status=0', true);

			$data['alerts'] = $customer_total + $product_total + $review_total + $return_total + $affiliate_total;

			// Online Stores
			$data['stores'] = array();

			$data['stores'][] = array(
				'name' => $this->config->get('config_name'),
				'href' => HTTP_CATALOG
			);

			$this->load->model('setting/store');

			$results = $this->model_setting_store->getStores();

			foreach ($results as $result) {
				$data['stores'][] = array(
					'name' => $result['name'],
					'href' => $result['url']
				);
			}
		}

		return $this->load->view('common/header', $data);
	}
}