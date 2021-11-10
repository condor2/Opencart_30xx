<?php
class ControllerMarketingAffiliate extends Controller {
	public function index() {
		$this->load->language('marketing/affiliate');

		$this->document->setTitle($this->language->get('heading_title'));

		$url = '';

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['breadcrumbs'] = [];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('text_home'),
			'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true)
		];

		$data['breadcrumbs'][] = [
			'text' => $this->language->get('heading_title'),
			'href' => $this->url->link('marketing/affiliate', 'user_token=' . $this->session->data['user_token'] . $url, true)
		];

		$data['list'] = $this->getList();

		$data['user_token'] = $this->session->data['user_token'];

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');

		$this->response->setOutput($this->load->view('marketing/affiliate', $data));
	}

	public function list() {
		$this->load->language('marketing/affiliate');

		$this->response->setOutput($this->getList());
	}

	protected function getList() {
		if (isset($this->request->get['filter_name'])) {
			$filter_name = $this->request->get['filter_name'];
		} else {
			$filter_name = '';
		}

		if (isset($this->request->get['filter_tracking'])) {
			$filter_tracking = $this->request->get['filter_tracking'];
		} else {
			$filter_tracking = '';
		}

		if (isset($this->request->get['filter_commission'])) {
			$filter_commission = $this->request->get['filter_commission'];
		} else {
			$filter_commission = '';
		}

		if (isset($this->request->get['filter_status'])) {
			$filter_status = $this->request->get['filter_status'];
		} else {
			$filter_status = '';
		}

		if (isset($this->request->get['filter_date_added'])) {
			$filter_date_added = $this->request->get['filter_date_added'];
		} else {
			$filter_date_added = '';
		}

		if (isset($this->request->get['sort'])) {
			$sort = $this->request->get['sort'];
		} else {
			$sort = 'name';
		}

		if (isset($this->request->get['order'])) {
			$order = $this->request->get['order'];
		} else {
			$order = 'ASC';
		}

		if (isset($this->request->get['page'])) {
			$page = (int)$this->request->get['page'];
		} else {
			$page = 1;
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_tracking'])) {
			$url .= '&filter_tracking=' . $this->request->get['filter_tracking'];
		}

		if (isset($this->request->get['filter_commission'])) {
			$url .= '&filter_commission=' . $this->request->get['filter_commission'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['action'] = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . $url);

		$this->load->model('customer/customer');

		$data['affiliates'] = [];

		$filter_data = [
			'filter_name'       => $filter_name,
			'filter_tracking'   => $filter_tracking,
			'filter_commission' => $filter_commission,
			'filter_status'     => $filter_status,
			'filter_date_added' => $filter_date_added,
			'sort'              => $sort,
			'order'             => $order,
			'start'             => ($page - 1) * $this->config->get('config_pagination_admin'),
			'limit'             => $this->config->get('config_pagination_admin')
		];

		$this->load->model('marketing/affiliate');

		$affiliate_total = $this->model_marketing_affiliate->getTotalAffiliates($filter_data);

		$results = $this->model_marketing_affiliate->getAffiliates($filter_data);

		foreach ($results as $result) {
			$data['affiliates'][] = [
				'customer_id' => $result['customer_id'],
				'name'        => $result['name'],
				'tracking'    => $result['tracking'],
				'commission'  => $result['commission'],
				'balance'     => $this->currency->format($this->model_customer_customer->getTransactionTotal($result['customer_id']), $this->config->get('config_currency')),
				'status'      => ($result['status'] ? $this->language->get('text_enabled') : $this->language->get('text_disabled')),
				'date_added'  => date($this->language->get('date_format_short'), strtotime($result['date_added'])),
				'customer'    => $this->url->link('customer/customer/edit', 'user_token=' . $this->session->data['user_token'] . '&customer_id=' . $result['customer_id'] . $url, true),
			];
		}

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_tracking'])) {
			$url .= '&filter_tracking=' . $this->request->get['filter_tracking'];
		}

		if (isset($this->request->get['filter_commission'])) {
			$url .= '&filter_commission=' . $this->request->get['filter_commission'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if ($order == 'ASC') {
			$url .= '&order=DESC';
		} else {
			$url .= '&order=ASC';
		}

		if (isset($this->request->get['page'])) {
			$url .= '&page=' . $this->request->get['page'];
		}

		$data['sort_name'] = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, true);
		$data['sort_tracking'] = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . '&sort=ca.tracking' . $url, true);
		$data['sort_commission'] = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . '&sort=ca.commission' . $url, true);
		$data['sort_status'] = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . '&sort=ca.status' . $url, true);
		$data['sort_date_added'] = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . '&sort=ca.date_added' . $url, true);

		$url = '';

		if (isset($this->request->get['filter_name'])) {
			$url .= '&filter_name=' . urlencode(html_entity_decode($this->request->get['filter_name'], ENT_QUOTES, 'UTF-8'));
		}

		if (isset($this->request->get['filter_tracking'])) {
			$url .= '&filter_tracking=' . $this->request->get['filter_tracking'];
		}

		if (isset($this->request->get['filter_commission'])) {
			$url .= '&filter_commission=' . $this->request->get['filter_commission'];
		}

		if (isset($this->request->get['filter_status'])) {
			$url .= '&filter_status=' . $this->request->get['filter_status'];
		}

		if (isset($this->request->get['filter_date_added'])) {
			$url .= '&filter_date_added=' . $this->request->get['filter_date_added'];
		}

		if (isset($this->request->get['sort'])) {
			$url .= '&sort=' . $this->request->get['sort'];
		}

		if (isset($this->request->get['order'])) {
			$url .= '&order=' . $this->request->get['order'];
		}

		$pagination = new Pagination();
		$pagination->total = $affiliate_total;
		$pagination->page = $page;
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('marketing/affiliate/list', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', true);

		$data['pagination'] = $pagination->render();

		$data['results'] = sprintf($this->language->get('text_pagination'), ($affiliate_total) ? (($page - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($page - 1) * $this->config->get('config_limit_admin')) > ($affiliate_total - $this->config->get('config_limit_admin'))) ? $affiliate_total : ((($page - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $affiliate_total, ceil($affiliate_total / $this->config->get('config_limit_admin')));

		$data['filter_name'] = $filter_name;
		$data['filter_tracking'] = $filter_tracking;
		$data['filter_commission'] = $filter_commission;
		$data['filter_status'] = $filter_status;
		$data['filter_date_added'] = $filter_date_added;

		$data['sort'] = $sort;
		$data['order'] = $order;

		return $this->load->view('marketing/affiliate_list', $data);
	}
}