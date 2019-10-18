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
		$data['qntParcelas'] = (int)$this->config->get('rakuten_qnt_parcelas');

        /** Telefone do titular */
        if (!preg_match('/(\(|\)|-| )/', $order_info['telephone'])) {
            $data['telefone'] = preg_replace('/^([\d]{2})([\d]{4})(\d.*)$/', '($1) $2-$3', $order_info['telephone']);
        } else {
            $data['telefone'] = $order_info['telephone'];
        }

        /** CPF */
        if (isset($order_info['custom_field'][$this->config->get('rakuten_cpf')])) {
            $data['cpf'] = $order_info['custom_field'][$this->config->get('rakuten_cpf')];
        } else {
            $data['cpf'] = false;
        }

		/** Quantidade parcelas sem juros */
		$data['max_parcelas_sem_juros'] = (int)$this->config->get('rakuten_parcelas_sem_juros');

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
        $custom_payment_fields = $order_info['payment_custom_field']; //District, complement and address number
        $custom_shipping_fields = $order_info['shipping_custom_field']; //District, complement and address number
        $shipping_method = $rakuten->getShippingMethod(); //shipping method without Rakuten Log
        $payment_method = $rakuten->getPaymentMethod(); //Payment Method of Rakuten (billet/credit_card)
        $posted = $_POST; // _POST received from the checkout form
        $buyerInterest = $rakuten->getBuyerInterest();
        $total = $rakuten->getTotalAmount() + $rakuten->getShippingAmount() + $posted['amount'] ; //Sum of cart total amount, shipping amount and rakuten interest amount.
        $total_amount = number_format($total, 2, '.', '.');

        /** Payload */
        $data = array(
            'reference'   => $rakuten->getOrderId($order_info),
            'amount'      => $total_amount,
            'currency'    => $rakuten->getCurrency($order_info),
            'webhook_url' => $rakuten->getWebhook() . 'index.php?route=extension/payment/rakuten/callback',
            'fingerprint' => $posted['fingerprint'],
            'payments'    => array(),
            'customer'    => [
                'document'      => $rakuten->getDocument($order_info),
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
                        ]
                    ),
                    array(
                        'kind' => 'shipping',
                        'reference' => 'others',
                        'number' => [
                            'country_code' => '55',
                            'area_code' => $rakuten->getPhone($order_info)['ddd'],
                            'number' => $rakuten->getPhone($order_info)['number'],
                            ]
                    )
                )
            ],
            'order' => array(
                'reference'       => $rakuten->getOrderId($order_info),
                'payer_ip'        => $rakuten->getIp($order_info),
                'items_amount'    => $rakuten->getSubTotalAmount(),
                'shipping_amount' => (float) $rakuten->getShippingAmount(),
                'taxes_amount'    => (float) $rakuten->getTaxAmount() + $posted['amount'],
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
                'number' => $rakuten->getAddressNumber($custom_payment_fields),
                'complement' => $rakuten->getAddressComplement($custom_payment_fields),
                'city' => $rakuten->getCity($order_info),
                'district' => $rakuten->getAddressDistrict($custom_payment_fields),
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
                'district' => $rakuten->getShippingAddressDistrict($custom_shipping_fields),
                'state' => $rakuten->getShippingState($order_info),
                'country' => $rakuten->getShippingCountry($order_info),
                'zipcode' => $rakuten->getShippingPostalCode($order_info),
            ];

            $data['customer']['addresses'][] = $shipping_address;
        }

        if ( $payment_method == 'rakuten_cartao' ) {
            $payment = [
                'reference'                => $rakuten->getOrderId($order_info),
                'method'                   => 'credit_card',
                'amount'                   => $total_amount,
                'installments_quantity'    => (integer) $posted['quantity'],
                'brand'                    => strtolower( $posted['brand'] ),
                'token'                    => $posted['token'],
                'cvv'                      => $posted['cvv'],
                'holder_name'              => $posted['name'],
                'holder_document'          => $posted['document'],
                'options'                  => [
                    'save_card'   => false,
                    'new_card'    => false,
                    'recurrency'  => false,
                ]
            ];

            if ($buyerInterest == '1') {
                $payment['installments'] = [
                    'quantity' => (int) $posted['quantity'],
                    'interest_percent' => (float) $posted['percent'],
                    'interest_amount' => (float) $posted['amount'],
                    'installment_amount' => (float) $posted['installment'],
                    'total' => (float) $posted['total'],
                ];
            }
        } else {
            $payment = [
                'reference' => $rakuten->getOrderId($order_info),
                'method' => 'billet',
                'amount' => (float) $total_amount,
            ];
        }

        $data['payments'][] = $payment;

        try {
            $result = $rakuten->chargeTransaction( $data );
        } catch (Exception $e) {
            $rakuten->setException($e->getMessage());
        }

        return $result;
    }
}
