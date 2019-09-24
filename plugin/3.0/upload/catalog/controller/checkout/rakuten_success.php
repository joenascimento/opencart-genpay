<?php
class ControllerCheckoutRakutenSuccess extends Controller {
    public function index() {

        if (empty($this->session->data['success_order_id'])) {
            return $this->response->redirect($this->url->link('account/login'));
        }

        $this->load->language('checkout/success');
        $this->load->language('extension/payment/rakuten');
        $this->load->model('extension/payment/rakuten');
        $rakuten = $this->model_extension_payment_rakuten;

        if (isset($this->session->data['order_id'])) {
            $this->session->data['success_order_id'] = $this->session->data['order_id'];

            $this->cart->clear();

            unset($this->session->data['shipping_method']);
            unset($this->session->data['shipping_methods']);
            unset($this->session->data['payment_method']);
            unset($this->session->data['payment_methods']);
            unset($this->session->data['guest']);
            unset($this->session->data['comment']);
            unset($this->session->data['order_id']);
            unset($this->session->data['coupon']);
            unset($this->session->data['reward']);
            unset($this->session->data['voucher']);
            unset($this->session->data['vouchers']);
            unset($this->session->data['totals']);
        }

        $this->document->setTitle($this->language->get('heading_title'));

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/home')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_basket'),
            'href' => $this->url->link('checkout/cart')
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_checkout'),
            'href' => $this->url->link('checkout/checkout', '', true)
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_success'),
            'href' => $this->url->link('checkout/rakuten_success')
        );

        $data['order_status'] = $rakuten->getOrderStatus($this->session->data['success_order_id']);

        if ($this->customer->isLogged()) {
            if ($data['order_status'] == 'pending') {
                $data['title_rakuten_message'] = $this->language->get('rakuten_title_success');
                $data['text_rakuten_message'] = sprintf($this->language->get('rakuten_success'), $this->session->data['success_order_id'], 'Pending', $this->url->link('account/order', '', true));
            } else {
                $data['title_rakuten_message'] = $this->language->get('rakuten_title_failure');
                $data['text_rakuten_message'] = sprintf($this->language->get('rakuten_failure'), $this->session->data['success_order_id'], $data['order_status'], $this->url->link('account/order', '', true));
            }
        } else {
            $data['text_message'] = sprintf($this->language->get('text_guest'), $this->url->link('information/contact'));
        }

        $data['continue'] = $this->url->link('common/home');

        $data['column_left'] = $this->load->controller('common/column_left');
        $data['column_right'] = $this->load->controller('common/column_right');
        $data['content_top'] = $this->load->controller('common/content_top');
        $data['content_bottom'] = $this->load->controller('common/content_bottom');
        $data['footer'] = $this->load->controller('common/footer');
        $data['header'] = $this->load->controller('common/header');

        $this->response->setOutput($this->load->view('common/rakuten_success', $data));
    }
}