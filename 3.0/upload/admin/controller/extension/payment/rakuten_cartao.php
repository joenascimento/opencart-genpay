<?php
class ControllerExtensionPaymentRakutenCartao extends Controller {
	
	public function index() {
		$this->response->redirect($this->url->link('extension/payment/rakuten', 'user_token=' . $this->session->data['user_token'], true));
	}
}
