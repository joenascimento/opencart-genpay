<?php
class ControllerExtensionPaymentRakutenBoleto extends Controller {
	
	public function index() {
        $this->response->redirect($this->url->link('extension/payment/rakuten', 'token=' . $this->session->data['token'], true));
	}
}
