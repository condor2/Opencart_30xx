<?php
class ControllerExtensionPaymentFreeCheckout extends Controller {
	public function index() {
		return $this->load->view('extension/payment/free_checkout');
	}

	public function confirm() {
		$json = array();

		if (isset($this->session->data['payment_method']['code']) && $this->session->data['payment_method']['code'] == 'free_checkout') {
			$this->load->model('checkout/order');

			$this->model_checkout_order->addOrderHistory($this->session->data['order_id'], $this->config->get('payment_free_checkout_order_status_id'));

			$json['redirect'] = $this->url->link('checkout/success', '', true);
		}

		$this->response->addHeader('Content-Type: application/json');
		$this->response->setOutput(json_encode($json));
	}
}