<?php
class ControllerExtensionPaymentRakutenCartao extends Controller {

	public function index() {

		$data = array();

        /** Linguagem */
        $this->load->language('extension/payment/rakuten');

        /** Load Models */
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/rakuten');
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');

        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']);
        $rakuten = $this->model_extension_payment_rakuten;
        $total = $rakuten->getTotalAmount($order_info) + $rakuten->getShippingAmount();
        $installments = $rakuten->getInstallments($total);
        $yearValues = $rakuten->setYearValues();

        /** Total */
		$data['total'] = $rakuten->getTotalAmount();

        /** Nome do Cliente */
        $data['cliente'] = $order_info['firstname'] . ' ' . $order_info['lastname'];

		/** Quantidade de Parcelas */
		$data['qntParcelas'] = (int)$this->config->get('payment_rakuten_qnt_parcelas');

        /** Telefone do titular */
        if (!preg_match('/(\(|\)|-| )/', $order_info['telephone'])) {
            $data['telefone'] = preg_replace('/^([\d]{2})([\d]{4})(\d.*)$/', '($1) $2-$3', $order_info['telephone']);
        } else {
            $data['telefone'] = $order_info['telephone'];
        }

        /** CPF */
        if (isset($order_info['custom_field'][$this->config->get('payment_rakuten_cpf')])) {
            $data['cpf'] = $order_info['custom_field'][$this->config->get('payment_rakuten_cpf')];
        } else {
            $data['cpf'] = false;
        }

		/** Quantidade parcelas sem juros */
		$data['max_parcelas_sem_juros'] = (int)$this->config->get('payment_rakuten_parcelas_sem_juros');

        $environment = $rakuten->getEnvironment();

        $data['environment'] = $environment['place']; //Sandbox/Production
        $data['rpay_js'] = $environment['rpay_js']; //Sandbox/Production
        $data['validate_js'] = $rakuten->getValidateJs(); //Validate Js
        $data['api'] = $environment['api']; //API Key
        $data['continue'] = $this->url->link('checkout/rakuten_success', '', true); //Success Page
        $data['webhook'] = $this->url->link('extension/payment/rakuten/callback', '', true); //Webhook
        $data['installments'] = $installments; //Installments
        $data['years'] = $yearValues; //Years to the card validation

		return $this->load->view('extension/payment/rakuten_cartao', $data);

	}

    public function transition()
    {

        /** Load Models */
        $this->load->model('checkout/order');
        $this->load->model('extension/payment/rakuten');

        /** Variables */
        $order_info = $this->model_checkout_order->getOrder($this->session->data['order_id']); //Order informations on the client session
        $rakuten = $this->model_extension_payment_rakuten; //Rakuten Model to get all the methods
        $custom_field = $order_info['shipping_custom_field']; //District, complement and address number
        $shipping_method = $rakuten->getShippingMethod(); //shipping method withoud Rakuten Log
        $payment_method = $rakuten->getPaymentMethod(); //Payment Method of Rakuten (billet/credit_card)
        $posted = $_POST; // _POST received from the checkout form
        $totalamount = $rakuten->getTotalAmount() + $rakuten->getShippingAmount(); //Sum of cart total amount and the shipping amount

        /** Payload */
        $data = array(
            'reference'   => $rakuten->getOrderId($order_info),
            'amount'      => $totalamount,
            'currency'    => $rakuten->getCurrency($order_info),
            'webhook_url' => $rakuten->getWebhook() . 'index.php?route=extension/payment/rakuten/callback',
            'fingerprint' => $posted['fingerprint'],
            'payments'    => array(),
            'customer'    => [
                'document'      => $rakuten->getDocument($order_info),
                'name'          => $rakuten->getName($order_info),
                'business_name' => $rakuten->getName($order_info),
                'email'         => $rakuten->getEmail($order_info),
                'birth_date'    => '1999-01-01',
                'kind'          => $rakuten->getKind($order_info),
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
                                $rakuten->getPhone($order_info)
                            ),
                            'number' => preg_replace(
                                '/\((\d{2})\)\s(\d{4,5})-(\d{4})/',
                                '${2}${3}',
                                $rakuten->getPhone($order_info)
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
                                $rakuten->getPhone($order_info)
                            ),
                            'number' => preg_replace(
                                '/\((\d{2})\)\s(\d{4,5})-(\d{4})/',
                                '${2}${3}',
                                $rakuten->getPhone($order_info)
                            )
                        )
                    )
                )
            ],
            'order' => array(
                'reference'       => $rakuten->getOrderId($order_info),
                'payer_ip'        => $rakuten->getIp($order_info),
                'items_amount'    => $rakuten->getTotalAmount(),
                'shipping_amount' => (float) $rakuten->getShippingAmount(),
                'taxes_amount'    => (float) $rakuten->getTaxAmount(),
                'discount_amount' => (float) $rakuten->discount($rakuten->getTotalAmount()),
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
                'number' => $rakuten->getAddressNumber($custom_field),
                'complement' => $rakuten->getAddressComplement($custom_field),
                'city' => $rakuten->getCity($order_info),
                'district' => $rakuten->getAddressDistrict($custom_field),
                'state' => $rakuten->getState($order_info),
                'country' => $rakuten->getCountry($order_info),
                'zipcode' => $rakuten->getPostalCode($order_info),
            ];

            $data['customer']['addresses'][] = $billing_address;
        }

        if ( $payment_method == 'rakuten_cartao' ) {
            $payment = [
                'reference'                => $rakuten->getOrderId($order_info),
                'method'                   => 'credit_card',
                'amount'                   => $totalamount,
                'installments_quantity'    => (integer) $posted['quantity'],
                'brand'                    => strtolower( $posted['brand'] ),
                'token'                    => $posted['token'],
                'cvv'                      => $posted['cvv'],
                'holder_name'              => $posted['name'],
                'holder_document'          => $posted['document'],
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
                'reference' => $rakuten->getOrderId($order_info),
                'method' => 'billet',
                'amount' => (float) $totalamount,
            ];
        }

        $data['payments'][] = $payment;

        // Shipping Address
        if ( ! empty( $_POST['ship_to_different_address'] ) ) {
            $shipping_address = [
                'kind' => 'shipping',
                'contact' => $rakuten->getName($order_info),
                'street' => $rakuten->getShippingStreetAddress($order_info),
                'number' => $rakuten->getShippingAddressNumber($custom_field),
                'complement' => $rakuten->getShippingAddressComplement($custom_field),
                'city' => $rakuten->getShippingCity($order_info),
                'district' => $rakuten->getShippingDistrict($custom_field),
                'state' => $rakuten->getShippingState($order_info),
                'country' => $rakuten->getShippingCountry($order_info),
                'zipcode' => $rakuten->getShippingPostalCode($order_info),
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

        /** Captura o retorno da requisição */
        $result = $rakuten->chargeTransaction( $data );

        return $result;

    }

	public function confirm() {

        $this->load->model('checkout/order');

        $this->load->model('extension/payment/rakuten');
        $rakuten = $this->model_extension_payment_rakuten;
	    $response = $_POST;
        $result = json_decode($response['body'], true);
        $chargeUuid = $result['charge_uuid'];
        $payments = array_shift($result['payments']);
        $resultStatus = $payments['result'];
        $creditCard = $payments['credit_card']['number'];
        $paymentMessage = $payments['credit_card']['authorization_message'];
        $paymentCode = $payments['credit_card']['authorization_code'];
        $comment = "Cartão de crédito: " . $creditCard . "\n Código: " . $paymentCode . "\n Mensagem: " . $paymentMessage;
        $environment = $rakuten->getEnvironment()['place'];


		switch ($resultStatus) {
            case 'pending':
                $status = $this->config->get('payment_rakuten_aguardando_pagamento');
                $paymentStatus = 'pending';
                $this->log->write('Status: ' . $status);
                break;
            case 'success':
                $status = $this->config->get('payment_rakuten_aguardando_pagamento');
                $paymentStatus = 'pending';
                $this->log->write('Status: ' . $status);
                break;
            case 'declined':
                $status = $this->config->get('payment_rakuten_negada');
                $paymentStatus = 'declined';
                $this->log->write('Status: ' . $status);
                break;
            case 'failure':
                $status = $this->config->get('payment_rakuten_falha');
                $paymentStatus = 'failure';
                $this->log->write('Status: ' . $status);
                break;
            case 'refunded':
                $status = $this->config->get('payment_rakuten_devolvida');
                $paymentStatus = 'refunded';
                $this->log->write('Status: ' . $status);
                break;
            case 'cancelled':
                $status = $this->config->get('payment_rakuten_cancelada');
                $paymentStatus = 'cancelled';
                $this->log->write('Status: ' . $status);
                break;
            default:
                $status = $this->config->get('payment_rakuten_aguardando_pagamento');
                $paymentStatus = 'pending';
                $this->log->write('Status: ' . $status);
                break;
		}

		if (isset($this->session->data['order_id'])) {
            $order_id = $this->session->data['order_id'];
        } else {
            $order_id = $this->request->post["order_id"];
        }

		$this->model_checkout_order->addOrderHistory($order_id, $status, $comment, '1' );
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

		return $resultStatus;
	}
}
