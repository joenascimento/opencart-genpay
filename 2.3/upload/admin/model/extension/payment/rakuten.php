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

    public function install() {
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` (`type`, `code`) VALUES ('payment', 'rakuten_boleto') ");
        $this->db->query("INSERT INTO `" . DB_PREFIX . "extension` (`type`, `code`) VALUES ('payment', 'rakuten_cartao') ");
        $this->db->query("
          CREATE TABLE IF NOT EXISTS `rakutenpay_orders` ( 
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
