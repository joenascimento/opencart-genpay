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

		if ( empty( $rawResponse ) ) {

			print_r($rawResponse);

			return $rawResponse;

		}

		if ( empty($signatureHeader) || $signatureHeader !== $signatureBase64 ) {

			echo "<h3>ERRO:</h3>";
			echo "<h4>pedido: ". print_r($payments['reference'], true) . "</h4>";
			echo "<h4>As chaves de assinatura são diferentes</h4>";
			$rakuten->setLog('As chaves não batem: ' . print_r($signatureHeader, true) . ' x ' . $signatureBase64);

			return $signatureHeader;
		}

		header( 'HTTP/1.1 200 OK' );

		switch ($status) {
			case 'pending':
				$status = $this->config->get('rakuten_aguardando_pagamento');
				$paymentStatus = 'pending';
				$this->log->write('Status: ' . $status);
				break;
			case 'approved':
				$status = $this->config->get('rakuten_paga');
				$paymentStatus = 'approved';
				$this->log->write('Status: ' . $status);
				break;
			case 'declined':
				$status = $this->config->get('rakuten_negada');
				$paymentStatus = 'declined';
				$this->log->write('Status: ' . $status);
				break;
			case 'failure':
				$status = $this->config->get('rakuten_falha');
				$paymentStatus = 'failure';
				$this->log->write('Status: ' . $status);
				break;
			case 'refunded':
				$status = $this->config->get('rakuten_devolvida');
				$paymentStatus = 'refunded';
				$this->log->write('Status: ' . $status);
				break;
			case 'cancelled':
				$status = $this->config->get('rakuten_cancelada');
				$paymentStatus = 'cancelled';
				$this->log->write('Status: ' . $status);
				break;
			default: 
				$status = $this->config->get('rakuten_aguardando_pagamento');
				$paymentStatus = 'pending';
				$this->log->write('Status: ' . $status);
				break;
		}

		if ($payment_method == 'billet') {

			$this->model_checkout_order->addOrderHistory($orderId, $status, '', '1');
			$this->db->query("UPDATE `rakutenpay_orders` SET `status` = '$paymentStatus', `created_at` = '$createdAt', `updated_at` = CURRENT_TIME WHERE `order_id` = '$orderId'");

		} else {

			$creditCard = $payments['credit_card']['number'];
			$paymentMessage = $payments['credit_card']['authorization_message'];
			$paymentCode = $payments['credit_card']['authorization_code'];
			$comment = "Cartão de crédito: " . $creditCard . "\n Código: " . $paymentCode . "\n Mensagem: " . $paymentMessage;

			$this->model_checkout_order->addOrderHistory($orderId, $status, $comment, '1');
			$this->db->query("UPDATE `rakutenpay_orders` SET `status` = '$paymentStatus', `created_at` = '$createdAt', `updated_at` = CURRENT_TIME WHERE `order_id` = '$orderId'");

		}

	}

}
?>
