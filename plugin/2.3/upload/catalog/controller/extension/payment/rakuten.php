<?php
class ControllerExtensionPaymentRakuten extends Controller {

	public function callback() {
		@ob_clean();

		/** Load Models */
		$this->load->model('extension/payment/rakuten');
		$this->load->model('checkout/order');

		/** Variables */
		$rakuten = $this->model_extension_payment_rakuten;
		$rawResponse = file_get_contents( 'php://input' );
		$response = json_decode($rawResponse, true);
		$status = $response['status'];
		$payments = array_shift($response['payments']);
		$orderId = $payments['reference'];
		$payment_method = $payments['method'];
		$createdAt = $rakuten->getCreatedAt($orderId);

		$signatureKey = $rakuten->getConfSignature();
		$signature = hash_hmac('sha256', $rawResponse, $signatureKey, true);
		$signatureBase64 = base64_encode($signature);

        $rakuten->setLog('Começando webhook');

		if (!function_exists('apache_request_headers')) {

			$headers = [];

			foreach ($_SERVER as $name => $value)
			{
				if (substr($name, 0, 5) == 'HTTP_')
				{
					$headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
				}
			}
			$entityHeaders = $headers;
			$signatureHeader = $entityHeaders['Signature'];

		} else {
			$entityHeaders = apache_request_headers();
			$signatureHeader = $entityHeaders['Signature'];
		}

		if ( empty($signatureHeader) || $signatureHeader !== $signatureBase64 ) {
            $rakuten->setLog('As chaves não batem: ' . print_r($signatureHeader, true) . ' x ' . $signatureBase64);
            header('HTTP/1.0 403 Forbidden');

			return $signatureHeader;
		}

		header( 'HTTP/1.1 200 OK' );

		switch ($status) {
			case 'pending':
				$status = $this->config->get('rakuten_aguardando_pagamento');
				$paymentStatus = 'pending';
				$rakuten->setLog('Status: ' . $status);
				break;
			case 'approved':
				$status = $this->config->get('rakuten_paga');
				$paymentStatus = 'approved';
				$rakuten->setLog('Status: ' . $status);
				break;
			case 'declined':
				$status = $this->config->get('rakuten_negada');
				$paymentStatus = 'declined';
				$rakuten->setLog('Status: ' . $status);
				break;
			case 'failure':
				$status = $this->config->get('rakuten_falha');
				$paymentStatus = 'failure';
				$rakuten->setLog('Status: ' . $status);
				break;
			case 'refunded':
				$status = $this->config->get('rakuten_devolvida');
				$paymentStatus = 'refunded';
				$rakuten->setLog('Status: ' . $status);
                break;
            case 'partial_refunded':
				$status = $this->config->get('rakuten_devolvida_parcial');
				$paymentStatus = 'partial_refunded';
				$rakuten->setLog('Status: ' . $status);
				break;
			case 'cancelled':
				$status = $this->config->get('rakuten_cancelada');
				$paymentStatus = 'cancelled';
				$rakuten->setLog('Status: ' . $status);
				break;
			default:
				$status = $this->config->get('rakuten_aguardando_pagamento');
				$paymentStatus = 'pending';
				$rakuten->setLog('Status: ' . $status);
				break;
		}

		//$this->model_checkout_order->addOrderHistory($orderId, $status, '', '1');

        $rakuten->setLog($orderId);
        $rakuten->setLog($payment_method);
		$this->db->query("UPDATE `". DB_PREFIX . "order` SET `order_status_id` = '" . $status . "' WHERE `order_id` = " . $orderId);
		$this->db->query("UPDATE `rakutenpay_orders` SET `status` = '$paymentStatus', `created_at` = '$createdAt', `updated_at` = CURRENT_TIME WHERE `order_id` = '$orderId'");
        $rakuten->setLog(date("Y-m-d H:i:s"));
		$rakuten->setLog($paymentStatus);
	}

}
?>
