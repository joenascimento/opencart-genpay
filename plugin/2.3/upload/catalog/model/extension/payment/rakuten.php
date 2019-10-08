<?php
class ModelExtensionPaymentRakuten extends Controller {

    private $environment;
    private $api;
    private $rpay_js;

    /**
     * PRODUCTION API URL.
     */
    const PRODUCTION_API_URL = 'https://api.rakuten.com.br/rpay/v1/';

    /**
     * SANDBOX API URL.
     */
    const SANDBOX_API_URL = 'https://oneapi-sandbox.rakutenpay.com.br/rpay/v1/';

    /**
     * PRODUCTION_JS_URL
     */
    const PRODUCTION_JS_URL = 'https://static.rakutenpay.com.br/rpayjs/rpay-latest.min.js';

    /**
     * SANDBOX_JS_URL
     */
    const SANDBOX_JS_URL = 'https://static.rakutenpay.com.br/rpayjs/rpay-latest.dev.min.js';

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
    private function getOnlyNumbers($value)
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
    public function getTotalAmount()
    {
        $this->setLog((float) $this->cart->getTotal());
        return (float) $this->cart->getTotal();

    }

    public function getSubTotalAmount()
    {
        $this->setLog((float) $this->cart->getSubTotal());
        return (float) $this->cart->getSubTotal();
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
        $this->setLog($order['payment_city']);
        return $order['payment_city'];

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
        $this->setLog($order['payment_zone_code']);
        return $order['payment_zone_code'];
    }

    /**
     * Get Country iso code 2
     *
     * @param $order
     * @return mixed
     */
    public function getCountry($order)
    {
        $this->setLog($order['payment_iso_code_2']);
        return $order['payment_iso_code_2'];
    }

    /**
     * Get the billing telephone
     *
     * @param $order
     * @return mixed
     */
    public function getPhone($order)
    {
        $this->setLog($order['telephone']);
        return $order['telephone'];
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
            $this->setLog(0);
            return 0;
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
                    $data['itemAmount' . $count] = number_format($this->currency->format($product['price'], $order['currency_code'], $order['currency_value'], false), 2, '.', '');
                    $data['itemQuantity' . $count] = $product['quantity'];
                    $data['itemTotalAmount' . $count] = $data['itemAmount' . $count] * $data['itemQuantity' . $count] ;
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
    public function chargeTransaction($data)
    {

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
            echo $response;
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
                    $value = ceil($value * 100) / 100;// rounds up to the nearest cent
                    $total = $value * $quantity;
                    $total = ceil($total * 100) / 100;
                    $installments[$quantity]['quantity'] = $quantity;
                    $installments[$quantity]['amount'] = $value;
                    $installments[$quantity]['total_amount'] = $total;
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
                $value = ceil($value * 100) / 100;// rounds up to the nearest cent
                $total = $value * $quantity;
                $total = ceil($total * 100) / 100;

                if ($value < $minimumInstallment) {
                    break;
                }

                $installments[$quantity]['quantity'] = $quantity;
                $installments[$quantity]['amount'] = $value;
                $installments[$quantity]['total_amount'] = $total;
                $installments[$quantity]['interest_amount'] = 0.0;
                $installments[$quantity]['interest_percent'] = 0.0;
                $installments[$quantity]['text'] = str_replace('.', ',', $this->getInstallmentText
                (
                    $value, $quantity, $amount,true)
                );
            }
        }
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

        $url = "https://oneapi-sandbox.rakutenpay.com.br/rpay/v1/checkout" . '?' . http_build_query($params);

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

        if ($err) {
            echo "cURL Error #:" . $err;
        }
        return $json_response['payments'][1]['installments'];
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
