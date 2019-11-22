<?php

class ModelExtensionPaymentRakuten extends Controller {

    private $environment;
    private $api;
    private $rpay_js;

    /**
     * PRODUCTION API URL.
     */
    const PRODUCTION_API_URL = 'https://api.gencomm.com.br/rpay/v1/';

    /**
     * SANDBOX API URL.
     */
    const SANDBOX_API_URL = 'https://oneapi-sandbox.genpay.com.br/rpay/v1/';

    /**
     * PRODUCTION_JS_URL
     */
    const PRODUCTION_JS_URL = 'https://static.genpay.com.br/rpayjs/rpay-latest.min.js';

    /**
     * SANDBOX_JS_URL
     */
    const SANDBOX_JS_URL = 'https://static.genpay.com.br/rpayjs/rpay-latest.dev.min.js';

    /**
     * Get API URL.
     *
     * @return string
     */
    private function getApiUrl() {

        $this->environment = $this->config->get('rakuten_environment');

        if ( 'production' === $this->environment ) {

            $this->setLog(self::PRODUCTION_API_URL);
            return self::PRODUCTION_API_URL;

        } else {
            $this->setLog(self::SANDBOX_API_URL);
            return self::SANDBOX_API_URL;
        }

    }

    /**
     * Get JS Library URL.
     *
     * @return string
     */
    private function getJsUrl() {

        $this->environment = $this->config->get('rakuten_environment');

        if ( 'production' === $this->environment ) {
            $this->setLog(self::PRODUCTION_API_URL);
            return self::PRODUCTION_JS_URL;

        } else {
            $this->setLog(self::SANDBOX_API_URL);
            return self::SANDBOX_JS_URL;

        }
    }

    /**
     * Get JS Library URL.
     *
     * @return string
     */
    public function getValidateJs() {
        $this->setLog(self::SANDBOX_JS_URL);
        return self::SANDBOX_JS_URL;
    }

    /**
     * Set Year from Credit card
     *
     * @return array
     */
    public function setYearValues()
    {
        $data = [];
        $year = idate("Y");
        $maxYear = $year + 30;
        for ($i = $year; $i < $maxYear; $i++) {
            $data[]['year'] = $i;
        }

        return $data;
    }

    /**
     * Get environment
     *
     * @return array   sandbox/production API/JS
     */
    public function getEnvironment()
    {
        $api = $this->getApiUrl();
        $js = $this->getJsUrl();

        $this->environment = $this->config->get('rakuten_environment');
        $this->api = $api;
        $this->rpay_js = $js;

        if ( 'production' === $this->environment ) {
            $this->setLog($this->environment);
            $this->setLog($this->api);
            $this->setLog($this->rpay_js);
            return [
                'place' => $this->environment,
                'api' => $this->api,
                'rpay_js' => $this->rpay_js,
            ];

        }
        $this->setLog($this->environment);
        $this->setLog($this->api);
        $this->setLog($this->rpay_js);
        return [
            'place' => $this->environment,
            'api' => $this->api,
            'rpay_js' => $this->rpay_js,
        ];
    }

    /**
     * Helper to get only numbers
     *
     * @param $string
     * @return string|string[]|null
     */
    protected function only_numbers( $string ) {

        return preg_replace( '([^0-9])', '', $string );

    }

    /**
     * Get Payment method, Billet or Credit Card
     *
     * @return array
     */
    public function getMethod() {
        return [];
    }

    /**
     * Get Payment Method
     *
     * @return mixed
     */
    public function getPaymentMethod()
    {
        $this->setLog($this->session->data['payment_method']['code']);
        return $this->session->data['payment_method']['code'];
    }

    /**
     * Get Document CPF/CNPJ
     *
     * @param $order
     * @return mixed
     */
    public function getDocument($order)
    {
        $document = $this->getOnlyNumbers($order['custom_field'][$this->config->get('rakuten_cpf')]);
        $this->setLog($document);

        return $document;
    }

    /**
     * Helper Only Numbers
     */
    public function getOnlyNumbers($value)
    {
        $this->setLog(preg_replace('/\D/', '', $value));
        return preg_replace('/\D/', '', $value);
    }

    /**
     * Get billing Fisrtname and Lastname of customer
     *
     * @param $order
     * @return string
     */
    public function getName($order)
    {
        try {
            $name = $order['payment_firstname'] . ' ' . $order['payment_lastname'];
            if (empty($name)) {
                throw new Exception('Nome ou Sobrenome está vazio');
            }

            $this->setLog($name);
            return $name;

        } catch (Exception $e) {
            $this->setException($e->getMessage());

            return false;
        }
    }

    /**
     * Get email account
     *
     * @param $order
     * @return mixed
     */
    public function getEmail($order)
    {
        $email = $order['email'];

        try {
            if (empty($email)) {
                throw new Exception('Email está vazio');
            }

            $this->setLog($email);
            return $email;

        } catch (Exception $e) {
            $this->setException($e->getMessage());

            return false;
        }

    }

    /**
     * Get kind, personal or business
     *
     * @param $order
     * @return string
     */
    public function getKind($order)
    {
        $this->setLog('personal');
        return 'personal';
    }

    /**
     * Get Order ID (Reference)
     *
     * @param $order
     * @return mixed
     */
    public function getOrderId($order)
    {
        $this->setLog($order['order_id']);
        return (string) $order['order_id'];
    }

    /**
     * Get Currency, but Rakuten Pay just process BRL
     *
     * @param $order
     * @return string
     */
    public function getCurrency($order)
    {
//        return $order['currency_code'];
        return 'BRL';

    }

    /**
     * Get the order total amount
     *
     * @return mixed
     */
    public function getTotalAmount($order)
    {
//        $this->setLog((float) $this->cart->getTotal());
//        return (float) $this->cart->getTotal();
        $total = $this->getSubTotalAmount($order) + $this->getShippingAmount() + $this->getTaxAmount();
        $this->setLog($total);
        return round($total, 2);
    }

    /**
     * getSubTotalAmount
     *
     * @access public
     * @return float
     */
    public function getSubTotalAmount($order)
    {
        $items = $this->getItems($order);
        $total = 0;
        foreach($items as $item) {
            $total += (float) $item['total_amount'];
        }
        return (float) round($total, 2);
    }

    /**
     * @param $order
     * @return float
     */
    public function getDiscount($order)
    {
        $total = $this->getTotalAmount($order);
        $amount = round($order['total'], 2);
        $discount = $total - $amount;

        $this->setLog('total: ' . $total .  ' - amount: ' . $amount);
        if ($amount == $total) {
            $this->setLog((float)0.0);
            return (float) 0.0;
        }

        if ($discount < 0) {
            $this->setLog($discount . ' menor que zero retorna 0');
            return (float) 0.0;
        }
        $this->setLog($discount);
        return $discount;
    }

    /**
     * Get BirthDate Custom Field
     *
     * @param $order
     * @return mixed
     */
    public function getBirthDate($order)
    {
        $birthDate = $order['custom_field'][$this->config->get('rakuten_birthdate')];
        if (empty($birthDate)) {
            $this->setLog('1999-01-01 padrão');
            return '1999-01-01';
        }

        $this->setLog($birthDate);
        return $birthDate;
    }

    /**
     * Get custom field number address
     *
     * @param $custom_field
     * @return int
     */
    public function getAddressNumber($custom_field)
    {
        $key = $this->config->get('rakuten_number');
        if (array_key_exists($key, $custom_field)) {
            $this->setLog($custom_field[$key]);
            return $custom_field[$key];
        } else {
            $this->setLog('0');
            return '0';
        }
    }

    /**
     * Get custom field number address
     *
     * @param $custom_field
     * @return int
     */
    public function getAddressComplement($custom_field) {
        $key = $this->config->get('rakuten_complement');
        if (array_key_exists($key , $custom_field)) {
            $this->setLog($custom_field[$key]);
            return $custom_field[$key];
        } else {
            $this->setLog('_');
            return '_';
        }
    }

    /**
     * Get custom field district
     *
     * @param $custom_field
     * @return string
     */
    public function getAddressDistrict($custom_field) {
        $key = $this->config->get('rakuten_district');
        if (array_key_exists($key , $custom_field)) {
            $this->setLog($custom_field[$key]);
            return $custom_field[$key];
        } else {
            $this->setLog('_');
            return '_';
        }
    }

    /**
     * Get City Billing Address
     *
     * @param $order
     * @return mixed
     */
    public function getCity($order)
    {
        try {
            $this->setLog($order['payment_city']);
            return $order['payment_city'];
        } catch (Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get Postal Code and replace just to number
     *
     * @param $order
     * @return string|string[]|null
     */
    public function getPostalCode($order)
    {
        $this->setLog($this->only_numbers(preg_replace('/[^\d]/', '', $order['payment_postcode'])));
        return $this->only_numbers(preg_replace('/[^\d]/', '', $order['payment_postcode']));

    }

    /**
     * Get State
     *
     * @param $order
     * @return mixed
     */
    public function getState($order)
    {
        try {
            $this->setLog($order['payment_zone_code']);
            return $order['payment_zone_code'];
        } catch (Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get Country iso code 2
     *
     * @param $order
     * @return mixed
     */
    public function getCountry($order)
    {
        try {
            $this->setLog($order['payment_iso_code_2']);
            return $order['payment_iso_code_2'];
        } catch(Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get the billing telephone
     *
     * @param $order
     * @return mixed
     */
    public function getPhone($order)
    {
        try {
            if (!empty($order['telephone'])) {

                $phone = trim($this->getOnlyNumbers($order['telephone']));
                $ddd = substr($phone, 0,2);
                $number = substr($phone, 2);

                $this->setLog($ddd.' '.$number);
                return [
                    'ddd' => $ddd,
                    'number' => $number,
                ];
            }
            $this->setLog('11 999999999 padrao');
            return [
                'ddd' => '11',
                'number' => '999999999',
            ];
        } catch(Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get the buyer IP address
     *
     * @param $order
     * @return mixed
     */
    public function getIp($order)
    {
        $this->setLog($order['ip']);
        return $order['ip'];
    }

    /**
     * Get billing Fisrtname and Lastname of customer
     *
     * @param $order
     * @return string
     */
    public function getShippingName($order)
    {
        try {
            $name = $order['shipping_firstname'] . ' ' . $order['shipping_lastname'];
            if (empty($name)) {
                throw new Exception('Nome ou Sobrenome está vazio');
            }

            $this->setLog($name);
            return $name;

        } catch (Exception $e) {
            $this->setException($e->getMessage());

            return false;
        }
    }

    /**
     * Get the shipping method code
     *
     * @return mixed
     */
    public function getShippingMethod()
    {

        try {
            if (isset($this->session->data['shipping_method'])) {
                $this->setLog($this->session->data['shipping_method']['code']);
                return $this->session->data['shipping_method']['code'];
            } else {
                $this->setLog('');
                return '';
            }

        } catch (Exception $e) {
            $this->setException($e->getMessage());

            return $e->getMessage();
        }

    }

    /**
     * Get the Shipping Amount
     *
     * @return string
     */
    public function getShippingAmount()
    {

        if (isset($this->session->data['shipping_method'])) {

            $shipping_amount = number_format($this->session->data['shipping_method']['cost'], 2, '.', '.');

            $this->setLog((float) $shipping_amount);
            return (float) $shipping_amount;
        } else {
            $this->setLog((float) 0);
            return (float) 0;
        }

    }

    /**
     * Get Street Address
     *
     * @param $order
     * @return string
     */
    public function getStreetAddress($order)
    {
        $this->setLog($order['payment_address_1']);
        return $order['payment_address_1'];
    }

    /**
     * Get Shipping Street Address
     *
     * @param $order
     * @return string
     */
    public function getShippingStreetAddress($order)
    {
        $this->setLog($order['shipping_address_1']);
        return $order['shipping_address_1'];
    }

    /**
     * Get custom field number address
     *
     * @param $custom_field
     * @return int
     */
    public function getShippingAddressNumber($custom_field) {
        $key = $this->config->get('rakuten_number');
        if (array_key_exists($key , $custom_field)) {
            $this->setLog($custom_field[$key]);
            return $custom_field[$key];
        } else {
            $this->setLog('0');
            return '0';
        }
    }

    /**
     * Get custom field complement
     *
     * @param $custom_field
     * @return int
     */
    public function getShippingAddressComplement($custom_field) {
        $key = $this->config->get('rakuten_complement');
        if (array_key_exists($key , $custom_field)) {
            $this->setLog($custom_field[$key]);
            return $custom_field[$key];
        } else {
            $this->setLog('_');
            return '_';
        }
    }

    /**
     * Get custom field district
     *
     * @param $custom_field
     * @return string
     */
    public function getShippingAddressDistrict($custom_field) {
        $key = $this->config->get('rakuten_district');

        if (array_key_exists($key , $custom_field)) {
            $this->setLog($custom_field[$key]);
            return $custom_field[$key];
        } else {
            $this->setLog('_');
            return '_';
        }
    }

    /**
     * Get Postal Code and replace just to number
     *
     * @param $order
     * @return string|string[]|null
     */
    public function getShippingPostalCode($order)
    {
        $this->setLog($this->only_numbers(preg_replace('/[^\d]/', '', $order['shipping_postcode'])));
        return $this->only_numbers(preg_replace('/[^\d]/', '', $order['shipping_postcode']));
    }

    /**
     * Get City Billing Address
     *
     * @param $order
     * @return mixed
     */
    public function getShippingCity($order)
    {
        $this->setLog($order['shipping_city']);
        return $order['shipping_city'];

    }

    /**
     * Get State
     *
     * @param $order
     * @return mixed
     */
    public function getShippingState($order)
    {
        $this->setLog($order['shipping_zone_code']);
        return $order['shipping_zone_code'];

    }

    /**
     * Get Shipping Country iso code 2
     *
     * @param $order
     * @return mixed
     */
    public function getShippingCountry($order)
    {
        $this->setLog($order['shipping_iso_code_2']);
        return $order['shipping_iso_code_2'];
    }

    /**
     * Shipping Free
     *
     * @return mixed
     **/
    public function discount($total) {
        $discount_total = 0;

        if (isset($this->session->data['coupon'])) {
            $this->load->language('total/coupon');

            $this->load->model('extension/total/coupon');

            $coupon_info = $this->model_extension_total_coupon->getCoupon($this->session->data['coupon']);

            if ($coupon_info) {

                if (!$coupon_info['product']) {
                    $sub_total = $this->cart->getSubTotal();
                } else {
                    $sub_total = 0;

                    foreach ($this->cart->getProducts() as $product) {
                        if (in_array($product['product_id'], $coupon_info['product'])) {
                            $sub_total += $product['total'];
                        }
                    }
                }

                if ($coupon_info['type'] == 'F') {
                    $coupon_info['discount'] = min($coupon_info['discount'], $sub_total);
                }

                foreach ($this->cart->getProducts() as $product) {
                    $discount = 0;

                    if (!$coupon_info['product']) {
                        $status = true;
                    } else {
                        if (in_array($product['product_id'], $coupon_info['product'])) {
                            $status = true;
                        } else {
                            $status = false;
                        }
                    }

                    if ($status) {
                        if ($coupon_info['type'] == 'F') {
                            $discount = $coupon_info['discount'] * ($product['total'] / $sub_total);
                        } elseif ($coupon_info['type'] == 'P') {
                            $discount = $product['total'] / 100 * $coupon_info['discount'];
                        }
                    }

                    $discount_total += $discount;
                }

                // If discount greater than total
                if ($discount_total > $total) {
                    $discount_total = $total;
                }
            }
        }

        return $discount_total;
    }

    /**
     * Get Items from Cart
     *
     * @param order_info
     *
     * @return array items and categories.
     */
    public function getItems($order)
    {
        $count = 0;
        $items = [];

        try {
            foreach($this->cart->getProducts() as $product) {

                if ($product['price'] > 0) {

                    $data['itemId' . $count] = $product['product_id'];
                    $data['itemDescription' . $count] = $product['name'] . ' | ' . $product['model'];
                    $data['itemAmount' . $count] = round($product['price'], 2);
                    $data['itemQuantity' . $count] = $product['quantity'];
                    $data['itemTotalAmount' . $count] = round($data['itemAmount' . $count] * $data['itemQuantity' . $count], 2);
                }

                $items[] = [
                    'reference' => $data['itemId' . $count],
                    'description' => $data['itemDescription' . $count],
                    'amount' => (float) $data['itemAmount' . $count],
                    'quantity' => (integer) $data['itemQuantity' . $count],
                    'total_amount' => (float) $data['itemTotalAmount' . $count],
                    'categories' => $this->getCategories($data['itemId' . $count]),
                ];

                $count++;
            }

            $this->setLog(print_r($items, true));
            return $items;
        } catch (Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get the categories IDs and title.
     *
     * @param $product_id
     *
     * @return array
     */
    private function getCategories($product_id) {

        // Load Model Catalog Product
        $this->load->model('catalog/product');
        $this->load->model('catalog/category');

        $categories = $this->model_catalog_product->getCategories($product_id);

        $category_id = [];

        try {
            foreach ($categories as $category) {
                $product = $this->model_catalog_category->getCategory($category['category_id']);

                $category_id[] = [
                    'name' => (string) $product['name'],
                    'id' => (string) $category['category_id']
                ];
            }

            #$this->setLog(print_r($category_id, true));
            return $category_id;
        } catch (Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get Taxes Amount
     *
     * @return mixed
     */
    public function getTaxAmount()
    {
        $this->setLog(array_sum($this->cart->getTaxes()));
        return array_sum($this->cart->getTaxes());
    }

    /**
     * Send the payload to make a charge on Rakuten Pay
     *
     * @param $data
     */
    public function chargeTransaction($data, $quantity = null, $interest = null, $brand = null)
    {
        $order_id = $data['reference'];
        $endpoint = 'charges';
        $url = $this->getApiUrl() . $endpoint;
        $body = $this->getJson($data);

        $headers  = [
            'Authorization: ' . $this->setAuthorizationHeader(),
            'Signature: ' . $this->getSignature( $body ),
            'Content-Type: application/json',
            'Cache-Control: no-cache',
        ];

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HEADER => false,
            CURLOPT_TIMEOUT => 70,
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
            echo "cURL Error #:" . $err;
        } else {
            $normalized = $this->normalizeReponse($response);
            $status = $this->updateStatus($normalized, $order_id, $quantity, $interest, $brand);
            $this->setLog(print_r($status, true));
            return $status;
        }
    }

    /**
     * normalizeReponse parse the response from chargeTransaction
     *
     * @param mixed $response
     * @access private
     *
     * @return void
     */
    private function normalizeReponse($response)
    {

        $this->setLog(print_r($response, true));
        $result = json_decode($response, true);
        $payments = array_shift($result['payments']);
        $paymentMethod = $payments['method'];
        $paymentAmount = $payments['amount'];
        $status = $result['result'];
        $resultMessages = implode(",\n", $result['result_messages']);
        $resultStatus = $payments['result'];
        $chargeUuid = $result['charge_uuid'];


        $normalized = [
            'status' => $status,
            'result_status' => $resultStatus,
            'result_messages' => $resultMessages,
            'charge_uuid' => $chargeUuid,
            'payment_method' => $paymentMethod,
            'payment_amount' => $paymentAmount,
            'payment' => $payments,
        ];
        $this->setLog(print_r($normalized, true));
        return $normalized;
    }

    /**
     * updatStatus after charge transaction
     *
     * @param mixed $normalized
     * @param mixed $order_id
     * @access private
     * @return void
     */
    private function updateStatus($normalized, $order_id, $quantity = null, $interest = null, $brand = null)
    {
        $additional_information = null;
        try {
            /** Load Model order */
            $this->load->model('checkout/order');

            $environment = $this->getEnvironment()['place'];

            if ($normalized['status'] !== 'failure') {
                switch ($normalized['result_status']) {
                    case 'pending':
                        $status = $this->config->get('rakuten_aguardando_pagamento');
                        $this->setLog($status . ' - ' . $normalized['result_status']);
                        break;
                    case 'success':
                        $status = $this->config->get('rakuten_aguardando_pagamento');
                        $this->setLog($status . ' - ' . $normalized['result_status']);
                        break;
                    case 'declined':
                        $status = $this->config->get('rakuten_negada');
                        $this->setLog($status . ' - ' . $normalized['result_status']);
                        break;
                    case 'failure':
                        $status = $this->config->get('rakuten_falha');
                        $this->setLog($status . ' - ' . $normalized['result_status']);
                        break;
                    case 'cancelled':
                        $status = $this->config->get('rakuten_cancelada');
                        $this->setLog($status . ' - ' . $normalized['result_status']);
                        break;
                    default:
                        $status = $this->config->get('rakuten_aguardando_pagamento');
                        $this->setLog($status . ' - ' . $normalized['result_status']);
                        break;
                }

                if (isset($this->session->data['order_id'])) {
                    $this->cart->clear();
                    unset($this->session->data['shipping_method']);
                    unset($this->session->data['shipping_methods']);
                    unset($this->session->data['payment_method']);
                    unset($this->session->data['payment_methods']);
                    unset($this->session->data['comment']);
                    unset($this->session->data['coupon']);
                }

                if ($this->isBillet($normalized)) {
                    $billetArray = [
                        'payment_method' => $normalized['payment_method'],
                        'billet_url' => $normalized['payment']['billet']['url'],
                        'billet_download' => $normalized['payment']['billet']['download_url'],
                    ];

                    $additional_information = serialize($billetArray);
                    $billet_url = '<a href="'.$normalized['payment']['billet']['url'].'" target="_blank">Visualizar Boleto</a>';
                    $paymentMethod = 'Rakuten Boleto';
                    $this->model_checkout_order->addOrderHistory($order_id, $status, $billet_url, '1');
                    $this->setLog('Adicionando Order History: ' . $order_id . ' ' . $normalized['charge_uuid'] . ' ' . $normalized['result_status'] . ' ' . $environment);
                } else {
                    $paymentMethod = 'Rakuten Cartão '.$quantity.'x '.strtoupper($brand);
                    $creditCard = $normalized['payment']['credit_card']['number'];
                    $paymentMessage = $normalized['payment']['credit_card']['authorization_message'];
                    $paymentCode = $normalized['payment']['credit_card']['authorization_code'];
                    $comment = "Cartão de crédito: " . $creditCard . "\n Código: " . $paymentCode . "\n Mensagem: " . $paymentMessage;
                    $commentArray = [
                        'payment_method' => $normalized['payment_method'],
                        'credit_card_number' => $creditCard,
                        'payment_message' => $paymentMessage,
                        'payment_code' => $paymentCode,
                        'comment' => $comment,
                    ];
                    $additional_information = serialize($commentArray);

                    $this->model_checkout_order->addOrderHistory($order_id, $status, $comment, '1');
                    $this->setLog('Adicionando Order History: ' . $order_id . ' ' . $normalized['charge_uuid'] . ' ' . $normalized['result_status'] . ' ' . $environment);
                }

                $this->db->query("INSERT INTO `rakutenpay_orders` (`order_id`, `charge_uuid`, `status`, `additional_information`, `environment`, `created_at`, `updated_at`) VALUES ('$order_id', '{$normalized['charge_uuid']}', '{$normalized['result_status']}', '$additional_information' , '$environment', CURRENT_TIME, CURRENT_TIME)");
                $this->setLog('Aditional Information: ' . $additional_information);
                $this->db->query("INSERT INTO `" . DB_PREFIX . "order_total` (`order_id`, `code`, `title`, `value`, `sort_order`) VALUES ('$order_id', 'juros', 'Juros', '$interest' , 6)");
                $this->setLog('INSERT interest INTO order_total: ' . $interest);
                $this->db->query("UPDATE `". DB_PREFIX . "order_total` SET `value` = '".$normalized['payment_amount']."' WHERE `order_id` = " .$order_id. " AND `code` = 'total'");
                $this->setLog('UPDATE order_total: ' . $normalized['payment_amount']);
                $this->db->query("UPDATE `". DB_PREFIX . "order` SET `payment_method` = '" . $paymentMethod . "' WHERE `order_id` = " . $order_id);
                $this->setLog('UPDATE order: ' . $paymentMethod);
                $this->db->query("UPDATE `". DB_PREFIX . "order` SET `total` = '".$normalized['payment_amount']."' WHERE `order_id` = " . $order_id);
                $this->setLog("UPDATE order: " . $normalized['payment_amount']);
                return true;
            } else {
                $status = $this->config->get('rakuten_falha');
                $this->setLog($status . ' - ' . print_r($normalized['result_messages'], true));
                $this->model_checkout_order->addOrderHistory($order_id, $status, $normalized['result_messages'], '1');

                return false;
            }
        } catch (Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * updateStatusWebhook after the GenPay Webhook
     *
     * @param mixed $orderId
     * @param mixed $paymentMethod
     * @param mixed $status
     * @param mixed $paymentStatus
     * @param mixed $createdAt
     * @access public
     * @return void
     */
    public function updateStatusWebhook($orderId, $paymentMethod, $status, $paymentStatus, $createdAt)
    {
        try {
            $query_order = $this->db->query("SELECT `order_status_id` FROM `". DB_PREFIX ."order` WHERE `order_id` = " . $orderId);
            $updated_order = array_shift($query_order->row);
            $query_order_history = $this->db->query("SELECT `order_status_id` FROM `". DB_PREFIX ."order_history` WHERE `order_id` = " . $orderId);
            $updated_order_history = end($query_order_history->rows);

            $this->setLog($orderId);
            $this->setLog($paymentMethod);

            if ($updated_order_history['order_status_id'] == $status) {
                $this->setLog('Order history status já atualizado');
            } else {
                $this->db->query("INSERT INTO `". DB_PREFIX ."order_history` (`order_id`, `order_status_id`, `notify`, `comment`, `date_added`, `external`) VALUES ('$orderId', '$status', 1, '', CURRENT_TIME, 0)");
                $this->setLog('Atualizado order history');
            }

            if ($updated_order == $status) {
                $this->setLog('Order status já atualizado.');
                return false;
            }

		    $this->db->query("UPDATE `". DB_PREFIX . "order` SET `order_status_id` = '" . $status . "' WHERE `order_id` = " . $orderId);
		    $this->db->query("UPDATE `rakutenpay_orders` SET `status` = '$paymentStatus', `created_at` = '$createdAt', `updated_at` = CURRENT_TIME WHERE `order_id` = '$orderId'");
            $this->setLog('Atualizado order history');

            $this->setLog(date("Y-m-d H:i:s"));
		    $this->setLog($paymentStatus);
            return true;
        } catch (Exception $e) {
            $this->setException($e->getMessage());

            return false;
        }
    }

    /**
     * isBillet
     *
     * @param array $data
     * @access private
     * @return void
     */
    private function isBillet(array $data)
    {
        return $data['payment_method'] == 'billet' ? true : false;
    }

    /**
     * getAdditionInformation
     *
     * @param mixed $key
     * @access public
     * @return void
     */
    public function getAdditionInformation($key)
    {
        try {
            if (empty($key)) {
                return null;
            }

            $order_id = $this->session->data['success_order_id'];
            $sql = $this->db->query("SELECT `additional_information` FROM rakutenpay_orders WHERE `order_id` = {$order_id}");
            $additional_information = array_shift($sql->row);
            $data = unserialize($additional_information);

            if (empty($additional_information)) {
                return null;
            }

            $this->setLog(print_r($data, true));
            return isset($data[$key]) ? $data[$key] : null;

        } catch (\Exception $e) {
            $this->setException($e->getMessage());
            return false;
        }
    }

    /**
     *
     * @return mixed
     */
    public function getWebhook()
    {
        $this->setLog($this->config->get('config_ssl'));
        return $this->config->get('config_ssl');
    }

    /**
     * get signature of requested data.
     *
     * @param   string  $data  Data.
     * @return  string  base64 signature.
     */
    private function getSignature( $data ) {
        try {
            $signature = hash_hmac(
                'sha256',
                $data,
                $this->config->get('rakuten_signature'),
                true
            );

            $this->setLog(base64_encode( $signature ));
            return base64_encode( $signature );
        } catch (Exception $e) {
            $this->setException($e->getMessage());
            return false;
        }
    }

    /**
     * Set authorixzation for the header request.
     *
     * @return string
     */
    private function setAuthorizationHeader() {
        try {
            $document = $this->config->get('rakuten_document');
            $api_key = $this->config->get('rakuten_api');

            $user_pass = $document . ':' . $api_key;
            return 'Basic ' . base64_encode( $user_pass );
        } catch (Exception $e) {
            $this->setException($e->getMessage());
            return false;
        }
    }

    /**
     * Get Configuration Document
     *
     * @return string  CPF/CNPJ
     */
    public function getConfDocument()
    {
        try {
            $document = $this->config->get('rakuten_document');

            return $document;
        } catch (Exception $e) {
            $this->setException($e->getMessage());
            return false;
        }
    }

    /**
     * Get signature of admin extension payment configuration.
     *
     * @return  string  base64 signature.
     */
    public function getConfSignature() {

        $signature = $this->config->get('rakuten_signature');

        return $signature;
    }

    /**
     * Json encode and Check if const exists based from PHP Version
     *
     * @param array $data
     * @return mixed|string
     */
    public static function getJson(array $data)
    {
        if (defined('JSON_PRESERVE_ZERO_FRACTION')) {

            return  json_encode($data, JSON_PRESERVE_ZERO_FRACTION);
        }

        /** For PHP Version < 5.6 */
        return self::preserveZeroFractionInstallments($data);
    }

    /**
     * @param array $data
     * @return mixed|string
     */
    private static function preserveZeroFractionInstallments(array $data)
    {
        $jsonData = json_encode($data);
        try {
            $payments = $data['payments'];
            foreach ($payments as $item) {
                if (!array_key_exists('installments', $item)) {
                    break;
                }
                $jsonData = self::installmentsToFloat($item['installments'], $jsonData);
            }

            return $jsonData;
        } catch (\Exception $e) {

            return $jsonData;
        }
    }

    /**
     * @param array $installments
     * @param $jsonData
     * @return mixed|string
     */
    private static function installmentsToFloat(array $installments, $jsonData)
    {
        foreach (self::$installmentsToFloat as $field) {
            if (array_key_exists($field, $installments)) {
                $jsonData = str_replace('"' . $field . '":'. $installments[$field], '"' . $field . '":'. number_format($installments[$field], 2, ".", "") . '', $jsonData);
            }
        }

        return $jsonData;
    }

    /**
     * Get buyer interest configuration
     *
     * @return mixed
     */
    public function getBuyerInterest()
    {
        try {
            if (!empty($this->config->get('rakuten_juros'))) {
                $this->setLog($this->config->get('rakuten_juros'));
                return $this->config->get('rakuten_juros');
            }
        } catch (Exception $e) {
            $this->setException($e->getMessage());
        }
    }

    /**
     * Get Installments
     *
     * @param $amount
     * @return array
     */
    public function getInstallments($amount)
    {
        $buyerInterest = $this->config->get('rakuten_juros'); //Get buyer interest at the database
        $installments = [];

        if ($buyerInterest == "1") {
            $freeInstallment = (int) $this->config->get('rakuten_parcelas_sem_juros');

            $customerInterestInstallments = $this->getInterestInstallments($amount);
            foreach($customerInterestInstallments as $installment) {
                $quantity = $installment['quantity'];
                if ($quantity > $freeInstallment) {
                    $installments[$quantity]['quantity'] = $quantity;
                    $installments[$quantity]['amount'] = $installment['installment_amount'];
                    $installments[$quantity]['total_amount'] = $installment['total'];
                    $installments[$quantity]['interest_amount'] = $installment['interest_amount'];
                    $installments[$quantity]['interest_percent'] = $installment['interest_percent'];
                    $installments[$quantity]['text'] = str_replace('.', ',', $this->getInstallmentText
                    (
                        $installment['installment_amount'], $quantity, $installment['total'], false)
                    );
                } else {
                    $value = $amount / $quantity;
                    $value = ($value * 100) / 100;
                    $total = $value * $quantity;
                    $installments[$quantity]['quantity'] = $quantity;
                    $installments[$quantity]['amount'] = number_format($value, 2, '.', '');
                    $installments[$quantity]['total_amount'] = number_format($total, 2, '.', '');
                    $installments[$quantity]['interest_amount'] = 0.0;
                    $installments[$quantity]['interest_percent'] = 0.0;
                    $installments[$quantity]['text'] = str_replace('.', ',', $this->getInstallmentText
                    (
                        $value, $quantity, $amount, true)
                    );
                }
            }
        } else {
            $maxNoInstallments = (int) $this->config->get('rakuten_qnt_parcelas');
            $minimumInstallment = (int) $this->config->get('rakuten_minimo_parcelas');

            for ($quantity = 1; $quantity <= $maxNoInstallments; $quantity++) {
                $value = $amount / $quantity;
                $value = ($value * 100) / 100;
                $total = $value * $quantity;

                if ($value < $minimumInstallment) {
                    break;
                }

                $installments[$quantity]['quantity'] = $quantity;
                $installments[$quantity]['amount'] = $value;
                $installments[$quantity]['total_amount'] = number_format($total, 2, '.', '.');
                $installments[$quantity]['interest_amount'] = 0.0;
                $installments[$quantity]['interest_percent'] = 0.0;
                $installments[$quantity]['text'] = str_replace('.', ',', $this->getInstallmentText
                (
                    $value, $quantity, $amount,true)
                );
            }
        }
        $this->setLog(print_r($installments, true));
        return $installments;
    }

    /**
     * Mount the text message of the installment
     *
     * @param $amount
     * @param $quantity
     * * @param $total
     * @param $interestFree
     * @return string
     */
    private function getInstallmentText($amount, $quantity, $total, $interestFree)
    {
        return sprintf(
            "%s x de R$ %.2f %s juros %s",
            $quantity,
            $amount,
            $this->getInterestFreeText($interestFree),
            $this->getTotalText($total)
        );
    }

    /**
     * Get the string relative to if it is an interest free or not
     *
     * @param string $interestFree
     *
     * @return string
     */
    private function getInterestFreeText($interestFree)
    {
        return ($interestFree === true) ? 'sem' : 'com';
    }
    /**
     * @param $total
     * @return string
     */
    private function getTotalText($total)
    {
        return sprintf('- Valor Total R$ %.2f', $total);
    }

    /**
     * Get Interest Installments
     *
     * @param $amount
     * @return mixed
     */
    public function getInterestInstallments($amount)
    {
        $curl = curl_init();

        $params = [
            'customer_document' => $this->getConfDocument(),
            'amount' => $amount,
        ];

        $endpoint = 'checkout';
        $url = $this->getEnvironment()['api'] . $endpoint . '?' . http_build_query($params);

        curl_setopt_array($curl, array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => [
                "Accept: application/json",
                'Authorization: ' . $this->setAuthorizationHeader(),
                'Content-Type: ' . 'application/json'
            ],
        ));

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        $json_response = json_decode($response, true);
        $parseInstallment = array_column($json_response['payments'], 'installments');
        $installments = array_shift($parseInstallment);

        if ($err) {
            echo "cURL Error #:" . $err;
        }
        return $installments;
    }

    /**
     * Get Order Status at rakutenpay_orders table
     *
     * @param $order_id
     * @return mixed
     */
    public function getOrderStatus($order_id)
    {

        try {
            $sql = $this->db->query("SELECT `order_id`,`status` FROM rakutenpay_orders");
            $orders = $sql->rows;

            foreach ($orders as $order) {

                if ($order_id == $order['order_id']) {

                    $this->setLog($order['status']);
                    return $order['status'];
                }

            }
        } catch (\Exception $e) {

            $this->setException($e->getMessage());
            return false;

        }

    }

    /**
     * Get Order Status at rakutenpay_orders table
     *
     * @param $order_id
     * @return mixed
     */
    public function getCreatedAt($order_id)
    {
        try {
            $sql = $this->db->query("SELECT `order_id`,`created_at` FROM rakutenpay_orders WHERE order_id = '$order_id' ");
            $orders = $sql->rows;

            foreach ($orders as $order) {

                if ($order_id == $order['order_id']) {

                    return $order['created_at'];
                }

                throw new Exception('Verifique se a tabela rakutenpay_orders existe no banco de dados ou se o parâmetro está correto.');
            }
        } catch (Exception $e) {

            $this->setException($e->getMessage());
            return false;

        }
    }

    /**
     * Get the Log and create/append messages at the file rakuten.log
     *
     * @param $message
     * @param $method
     *
     * @return true
     */
    public function setLog($message)
    {
        if ($this->config->get('rakuten_debug') == '1') {

            $prefix = date(DATE_RFC822);
            $method = debug_backtrace();
            $function = $method;
            file_put_contents('rakuten.log', '[ ' . $prefix . '][ ' . $function[1]['function'] . ' ]' . '[info] ' . print_r($message . PHP_EOL, true), FILE_APPEND);

        }
        return true;
    }

    /**
     * Get the Log and create/append messages at the file rakuten.log
     *
     * @param $message
     * @param $method
     *
     * @return true
     */
    public function setException($message)
    {

        $prefix = date(DATE_RFC822);
        $method = debug_backtrace();
        $function = $method;
        file_put_contents('rakuten.log', '[ ' . $prefix . '][ ' . $function[1]['function'] . ' ]' . '[erro] ' . print_r($message . PHP_EOL, true), FILE_APPEND);

        return true;
    }
}
