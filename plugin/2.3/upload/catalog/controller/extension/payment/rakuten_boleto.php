<?php

class ControllerExtensionPaymentRakutenBoleto extends Controller {

	public function index() {

        /** Load Models */
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/rakuten');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $rakuten = $this->model_extension_payment_rakuten;

        /* CPF */
        if (isset($order_info['custom_field'][$this->config->get('rakuten_cpf')])) {
            if (!preg_match('/(\.|-)/', $order_info['telephone'])) {
                $data['cpf'] = preg_replace('/([\d]{3})([\d]{3})([\d]{3})([\d]{2})/', '$1.$2.$3-$4', $order_info['custom_field'][$this->config->get('rakuten_cpf')]);
            } else {
                $data['cpf'] = $order_info['custom_field'][$this->config->get('rakuten_cpf')];
            }
        } else {
            $data['cpf'] = '';
        }

        $environment = $rakuten->getEnvironment();

        $data['environment'] = $environment['place']; //Sandbox/Production
        $data['rpay_js'] = $environment['rpay_js']; //Sandbox/Production
        $data['api'] = $environment['api']; //API Key
        $data['continue'] = $this->url->link('checkout/rakuten_success', '', true); //Success Page
        $data['webhook'] = $this->url->link('extension/payment/rakuten/callback', '', true); //Webhook

		return $this->load->view('extension/payment/rakuten_boleto', $data);

	}

	public function transition()
    {
        /** Load Models */
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/rakuten');

        /** Variables */
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $rakuten = $this->model_extension_payment_rakuten;
        $custom_payment_fields = $order_info['payment_custom_field']; //District, complement and address number
        $custom_shipping_fields = $order_info['shipping_custom_field']; //District, complement and address number
        $shipping_method = $rakuten->getShippingMethod();
        $posted = $_POST;
        $total_amount = $rakuten->getTotalAmount($order_info) - $rakuten->getDiscount($order_info);

        $rakuten->setLog(print_r($posted, true));

        /** Payload */
        $data = array(
            'reference'   => $rakuten->getOrderId($order_info),
            'amount'      => (float) $total_amount,
            'currency'    => $rakuten->getCurrency($order_info),
            'webhook_url' => $rakuten->getWebhook() . 'index.php?route=extension/payment/rakuten/callback',
            'fingerprint' => $posted['fingerprint'],
            'payments'    => array(),
            'customer'    => [
                'document'      => $rakuten->getOnlyNumbers($posted['billet_document']),
                'name'          => $rakuten->getName($order_info),
                'business_name' => $rakuten->getName($order_info),
                'email'         => $rakuten->getEmail($order_info),
                'birth_date'    => $rakuten->getBirthDate($order_info),
                'kind'          => $rakuten->getKind($order_info),
                'addresses'     => array(),
                'phones'        => array(
                    array(
                        'kind' => 'billing',
                        'reference' => 'others',
                        'number' => [
                            'country_code' => '55',
                            'area_code' => $rakuten->getPhone($order_info)['ddd'],
                            'number' => $rakuten->getPhone($order_info)['number'],
                        ],
                    ),
                    array(
                        'kind' => 'shipping',
                        'reference' => 'others',
                        'number' => [
                            'country_code' => '55',
                            'area_code' => $rakuten->getPhone($order_info)['ddd'],
                            'number' => $rakuten->getPhone($order_info)['number']
                        ]
                    )
                )
            ],
            'order' => array(
                'reference'       => $rakuten->getOrderId($order_info),
                'payer_ip'        => $rakuten->getIp($order_info),
                'items_amount'    => $rakuten->getSubTotalAmount($order_info),
                'shipping_amount' => (float) $rakuten->getShippingAmount(),
                'taxes_amount'    => (float) $rakuten->getTaxAmount(),
                'discount_amount' => (float) $rakuten->getDiscount($order_info),
                'items' => $rakuten->getItems($order_info),
            ),
        );

        //Commissionings
        if ( $shipping_method == 'rakuten-log' ) {
            $commissionings = array(

                'reference'                 => (string) $rakuten->getOrderId($order_info),
                'kind'                      => 'rakuten_logistics',
                'amount'                    => (float) $rakuten->getShipipngAmount(),
                'calculation_code'          => $rakuten->getCalculationCode(),
                'postage_service_code'      => $shipping_data->get_meta('postage_service_code'),

            );

            $data['commissionings'][] = $commissionings;
        }

        //Billing Address.
        if ( ! empty( $rakuten->getStreetAddress($order_info) ) ) {
            $billing_address = [
                'kind' => 'billing',
                'contact' => $rakuten->getName($order_info),
                'street' => $rakuten->getStreetAddress($order_info),
                'number' => $rakuten->getAddressNumber($custom_payment_fields),
                'complement' => $rakuten->getAddressComplement($custom_payment_fields),
                'city' => $rakuten->getCity($order_info),
                'district' => $rakuten->getAddressDistrict($order_info),
                'state' => $rakuten->getState($order_info),
                'country' => $rakuten->getCountry($order_info),
                'zipcode' => $rakuten->getPostalCode($order_info),
            ];

            $data['customer']['addresses'][] = $billing_address;
        }


        // Shipping Address
        if (!empty($rakuten->getShippingStreetAddress($order_info))) {
            $shipping_address = [
                'kind' => 'shipping',
                'contact' => $rakuten->getShippingName($order_info),
                'street' => $rakuten->getShippingStreetAddress($order_info),
                'number' => $rakuten->getShippingAddressNumber($custom_shipping_fields),
                'complement' => $rakuten->getShippingAddressComplement($custom_shipping_fields),
                'city' => $rakuten->getShippingCity($order_info),
                'district' => $rakuten->getShippingAddressDistrict($order_info),
                'state' => $rakuten->getShippingState($order_info),
                'country' => $rakuten->getShippingCountry($order_info),
                'zipcode' => $rakuten->getShippingPostalCode($order_info),
            ];

            $data['customer']['addresses'][] = $shipping_address;
        }

        // Billet Payment Method
        $payment = [
            'reference' => $rakuten->getOrderId($order_info),
            'method' => 'billet',
            'amount' => (float) $total_amount,
        ];

        $data['payments'][] = $payment;

        try {
            $response = $rakuten->chargeTransaction( $data );
            print_r($response);
            return $response;
        } catch (Exception $e) {
            $rakuten->setException($e->getMessage());
        }
    }
}
