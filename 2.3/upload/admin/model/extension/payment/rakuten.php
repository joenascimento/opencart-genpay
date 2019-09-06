<?php
class ModelExtensionPaymentRakuten extends Model {

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
     * Get Configuration Document
     *
     * @return string  CPF/CNPJ
     */
    public function getConfDocument()
    {
        $document = $this->config->get('payment_rakuten_document');

        return $document;
    }

    /**
     * Check if Rakuten Pay response is valid.
     *
     * @param  string $body  IPN body.
     * @param  string $token IPN signature token
     *
     * @return bool
     */
    public function verifyCredentials() {

        $data = $_POST;
        $document = $data['cnpj'];
        $apiKey = $data['apiKey'];
        $environment = $data['environment'];
        $auth = $document . ':' . $apiKey;
        $endpoint = 'charges';
        $url = $this->getApiUrl($environment) . $endpoint;

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPGET => true,
            CURLOPT_USERPWD => $auth,
            CURLOPT_FAILONERROR => true
        ]);

        $response = json_decode(curl_exec($curl));
        $err = curl_error($curl);
        $info = curl_getinfo($curl);

        if (curl_errno($curl)) {
            return print_r($info['http_code']);
        } else {

            return print_r($info['http_code']);
        }
        curl_close($curl);
    }

    /**
     * Get environment
     *
     * @return array   sandbox/production API/JS
     */
    public function getEnvironment($environment)
    {

        $api = $this->getApiUrl($environment);
        $js = $this->getJsUrl();

        $this->environment = $environment;
        $this->api = $api;
        $this->rpay_js = $js;

        if ( 'production' === $this->environment ) {

            return [
                'place' => $this->environment,
                'api' => $this->api,
                'rpay_js' => $this->rpay_js,
            ];

        }

        return [
            'place' => $this->environment,
            'api' => $this->api,
            'rpay_js' => $this->rpay_js,
        ];

    }

    /**
     * Get API URL.
     *
     * @return string
     */
    private function getApiUrl($environment) {

        $this->environment = $environment;

        if ( 'production' === $this->environment ) {

            return self::PRODUCTION_API_URL;

        } else {

            return self::SANDBOX_API_URL;

        }
    }

    /**
     * Get JS Library URL.
     *
     * @return string
     */
    private function getJsUrl() {

        $this->environment = $this->config->get('payment_rakuten_environment');

        if ( 'production' === $this->environment ) {

            return self::PRODUCTION_JS_URL;

        } else {

            return self::SANDBOX_JS_URL;

        }
    }

    public function install() {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` (`type`, `code`) VALUES ('payment', 'rakuten_boleto') ");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` (`type`, `code`) VALUES ('payment', 'rakuten_cartao') ");
        $this->db->query("
          CREATE TABLE IF NOT EXISTS `" . DB_PREFIX . "rakutenpay_orders` ( 
              `id` INT(20) NOT NULL AUTO_INCREMENT , 
              `order_id` INT(20) NOT NULL , 
              `charge_uuid` VARCHAR(80) NOT NULL , 
              `status` VARCHAR(20) NOT NULL , 
              `environment` VARCHAR(15) NOT NULL , 
              `created_at` TIMESTAMP NOT NULL , 
              `updated_at` TIMESTAMP NOT NULL , 
            PRIMARY KEY (`id`)
          ) ENGINE = InnoDB COLLATE=utf8_general_ci;");
    }

    public function uninstall() {
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `code` = 'rakuten_boleto';");
        $this->db->query("DELETE FROM `" . DB_PREFIX . "extension` WHERE `code` = 'rakuten_cartao';");
    }
}
