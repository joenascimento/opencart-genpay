<?php
class ControllerExtensionPaymentRakutenBoleto extends Controller {
	
	public function index() {

        /** Load Models */
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/rakuten');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $order = $this->model_extension_payment_rakuten;

        /* CPF */
        if (isset($order_info['custom_field'][$this->config->get('payment_rakuten_cpf')])) {
            if (!preg_match('/(\.|-)/', $order_info['telephone'])) {
                $data['cpf'] = preg_replace('/([\d]{3})([\d]{3})([\d]{3})([\d]{2})/', '$1.$2.$3-$4', $order_info['custom_field'][$this->config->get('payment_rakuten_cpf')]);
            } else {
                $data['cpf'] = $order_info['custom_field'][$this->config->get('payment_rakuten_cpf')];
            }
        } else {
            $data['cpf'] = '';
        }

        $environment = $order->getEnvironment();

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
        $order = $this->model_extension_payment_rakuten;
        $custom_field = $order_info['shipping_custom_field'];
        $shipping_method = $order->getShippingMethod();
        $payment_method = $order->getPaymentMethod();
        $posted = $_POST;

        $totalamount = $order->getTotalAmount() + $order->getShippingAmount();

        /** Payload */
        $data = array(
            'reference'   => $order->getOrderId($order_info),
            'amount'      => $totalamount,
            'currency'    => $order->getCurrency($order_info),
            'webhook_url' => 'http://localhost/opencart/upload/admin/index.php?route=extension/payment/rakuten/callback',
            'fingerprint' => $posted['fingerprint'],
            'payments'    => array(),
            'customer'    => [
                'document'      => $order->getDocument($order_info),
                'name'          => $order->getName($order_info),
                'business_name' => $order->getName($order_info),
                'email'         => $order->getEmail($order_info),
                'birth_date'    => '1999-01-01',
                'kind'          => $order->getKind($order_info),
                'addresses'     => array(),
                'phones'        => array(
                    array(
                        'kind'         => 'billing',
                        'reference'    => 'others',
                        'number'       => array(
                            'country_code' => '55',
                            'area_code'    => preg_replace(
                                '/\((\d{2})\)\s(\d{4,5})-(\d{4})/',
                                '${1}',
                                $order->getPhone($order_info)
                            ),
                            'number' => preg_replace(
                                '/\((\d{2})\)\s(\d{4,5})-(\d{4})/',
                                '${2}${3}',
                                $order->getPhone($order_info)
                            )
                        )
                    ),
                    array(
                        'kind'         => 'shipping',
                        'reference'    => 'others',
                        'number'       => array(
                            'country_code' => '55',
                            'area_code'    => preg_replace(
                                '/\((\d{2})\)\s(\d{4,5})-(\d{4})/',
                                '${1}',
                                $order->getPhone($order_info)
                            ),
                            'number' => preg_replace(
                                '/\((\d{2})\)\s(\d{4,5})-(\d{4})/',
                                '${2}${3}',
                                $order->getPhone($order_info)
                            )
                        )
                    )
                )
            ],
            'order' => array(
                'reference'       => $order->getOrderId($order_info),
                'payer_ip'        => $order->getIp($order_info),
                'items_amount'    => $order->getTotalAmount(),
                'shipping_amount' => (float) $order->getShippingAmount(),
                'taxes_amount'    => (float) $order->getTaxAmount(),
                'discount_amount' => (float) $order->discount($order->getTotalAmount()),
                'items' => $order->getItems($order_info),
            ),
        );

        //Commissionings
        if ( $shipping_method == 'rakuten-log' ) {
            $commissionings = array(

                'reference'                 => (string) $order->getOrderId($order_info),
                'kind'                      => 'rakuten_logistics',
                'amount'                    => (float) $order->getShipipngAmount(),
                'calculation_code'          => $order->getCalculationCode(),
                'postage_service_code'      => $shipping_data->get_meta('postage_service_code'),

            );

            $data['commissionings'][] = $commissionings;
        }

        //Billing Address.
        if ( ! empty( $order->getStreetAddress($order_info) ) ) {
            $billing_address = [
                'kind' => 'billing',
                'contact' => $order->getName($order_info),
                'street' => $order->getStreetAddress($order_info),
                'number' => $order->getAddressNumber($custom_field),
                'complement' => $order->getAddressComplement($custom_field),
                'city' => $order->getCity($order_info),
                'district' => $order->getAddressDistrict($custom_field),
                'state' => $order->getState($order_info),
                'country' => $order->getCountry($order_info),
                'zipcode' => $order->getPostalCode($order_info),
            ];

            $data['customer']['addresses'][] = $billing_address;
        }

        if ( $payment_method == 'rakuten_credit_card' ) {
            $payment = [
                'reference'                => $order->getOrderId($order_info),
                'method'                   => $payment_method,
                'amount'                   => $totalamount,
                'installments_quantity'    => (integer) $posted['rakuten_pay_installments'],
                'brand'                    => strtolower( $posted['rakuten_pay_card_brand'] ),
                'token'                    => $posted['rakuten_pay_token'],
                'cvv'                      => $posted['rakuten_pay_card_cvc'],
                'holder_name'              => $posted['rakuten_pay_card_holder_name'],
                'holder_document'          => $posted['rakuten_pay_card_holder_document'],
                'options'                  => [
                    'save_card'   => false,
                    'new_card'    => false,
                    'recurrency'  => false
                ]
            ];
            if ( isset( $installments ) ) {
                $payment['installments'] = $installments;
            }
        } else {
            $payment = [
                'reference' => $order->getOrderId($order_info),
                'method' => 'billet',
                'amount' => (float) $totalamount,
            ];
        }

        $data['payments'][] = $payment;

        // Shipping Address
        if ( ! empty( $_POST['ship_to_different_address'] ) ) {
            $shipping_address = [
                'kind' => 'shipping',
                'contact' => $order->getName($order_info),
                'street' => $order->getShippingStreetAddress($order_info),
                'number' => $order->getShippingAddressNumber($custom_field),
                'complement' => $order->getShippingAddressComplement($custom_field),
                'city' => $order->getShippingCity($order_info),
                'district' => $order->getShippingDistrict($custom_field),
                'state' => $order->getShippingState($order_info),
                'country' => $order->getShippingCountry($order_info),
                'zipcode' => $order->getShippingPostalCode($order_info),
            ];

            // Non-WooCommerce default address fields.
            if ( ! empty( $posted['shipping_address_number'] ) ) {
                $shipping_address['number'] = $posted['shipping_address_number'];
            }
            if ( ! empty( $posted['shipping_district'] ) ) {
                $shipping_address['district'] = $posted['shipping_district'];
            }

            $data['customer']['addresses'][] = $shipping_address;
        } else {
            $shipping_address                = $billing_address;
            $shipping_address['kind']        = 'shipping';
            $data['customer']['addresses'][] = $shipping_address;
        }


        $result = $order->chargeTransaction( $data );

        return $result;

    }

	public function confirm() {

        $this->load->model('checkout/order');
        $this->load->model('extension/payment/rakuten');

        $rakuten = $this->model_extension_payment_rakuten;
	    $response = $_POST;
        $result = json_decode($response['body'], true);
        $payments = array_shift($result['payments']);
        $result_status = $payments['result'];
        $billet_url = '<a href="'.$payments['billet']['url'].'" target="_blank">Visualizar Boleto</a>';
        $chargeUuid = $result['charge_uuid'];
        $environment = $rakuten->getEnvironment()['place'];

		switch ($result_status) {
			case 'pending':
				$status = $this->config->get('payment_rakuten_aguardando_pagamento');
                $paymentStatus = 'pending';
				break;
			default: 
				$status = $this->config->get('payment_rakuten_aguardando_pagamento');
                $paymentStatus = 'pending';
				break;
		}
		
        if (isset($this->session->data['order_id'])) {
            $order_id = $this->session->data['order_id'];
        } else {
            $order_id = $this->request->post["order_id"];
        }
        
		$this->model_checkout_order->addOrderHistory($order_id, $status, $billet_url, '1');
        $this->db->query("INSERT INTO `rakutenpay_orders` (`order_id`, `charge_uuid`, `status`, `environment`, `created_at`, `updated_at`) VALUES ('$order_id', '$chargeUuid', '$paymentStatus', '$environment', CURRENT_TIME, CURRENT_TIME)");

		if (isset($this->session->data['order_id'])) {
			$this->cart->clear();
			unset($this->session->data['shipping_method']);
			unset($this->session->data['shipping_methods']);
			unset($this->session->data['payment_method']);
			unset($this->session->data['payment_methods']);
			unset($this->session->data['comment']);
			unset($this->session->data['coupon']);
		}
	}
}
