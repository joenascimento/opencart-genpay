<?php
class ControllerExtensionPaymentRakuten extends Controller {
	
	private $error = array();
	
	public function index() {

		/* Carrega linguagem */
		$data = $this->load->language('extension/payment/rakuten');

		$this->document->setTitle($this->language->get('heading_title'));
        
        $user_token = $this->session->data['user_token'];
        $rakuten = $this->model_extension_payment_rakuten;
        $data['user_token'] = $user_token;
		
		if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
			$this->load->model('setting/setting');
			
			$this->model_setting_setting->editSetting('payment_rakuten', $this->request->post);
                        
			$this->model_setting_setting->editSetting('total_rakuten', [
                'total_rakuten_acrescimo_status' => $this->request->post['payment_rakuten_status'],
                'total_rakuten_desconto_status'  => $this->request->post['payment_rakuten_status'],
            ]);
            
			$this->session->data['success'] = $this->language->get('text_success');
			
			$this->response->redirect($this->url->link('marketplace/extension', 'user_token=' . $user_token, true));
		}
		
		/* Load Models */
		$this->load->model('localisation/order_status');
		$this->load->model('localisation/geo_zone');
		$this->load->model('customer/custom_field');

		/* Warning */
		if (isset($this->error['warning'])) {
			$data['warning'] = $this->error['warning'];
		} else {
			$data['warning'] = false;
		}
		
		/* Error Email */
		if (isset($this->error['email'])) {
			$data['error_email'] = $this->error['email'];
		} else {
			$data['error_email'] = false;
		}

        /* Error Document */
        if (isset($this->error['document'])) {
            $data['error_document'] = $this->error['document'];
        } else {
            $data['error_document'] = false;
        }

        /* Error Api */
        if (isset($this->error['api'])) {
            $data['error_api'] = $this->error['api'];
        } else {
            $data['error_api'] = false;
        }

        /* Error Signature */
        if (isset($this->error['signature'])) {
            $data['error_signature'] = $this->error['signature'];
        } else {
            $data['error_signature'] = false;
        }

		/* Error Quantidade de Parcelas */
		if (isset($this->error['qnt_parcelas'])) {
			$data['error_qnt_parcela'] = $this->error['qnt_parcelas'];
		} else {
			$data['error_qnt_parcela'] = false;
		}

		/* Error Parcelas Sem Juros */
		if (isset($this->error['parcelas_sem_juros'])) {
			$data['error_parcelas_sem_juros'] = $this->error['parcelas_sem_juros'];
		} else {
			$data['error_parcelas_sem_juros'] = false;
		}

		$data['breadcrumbs'] = array();
		
		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('common/home', 'user_token=' . $user_token, true),
			'name' => $this->language->get('text_home')
		);

		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/extension', 'user_token=' . $user_token, true),
			'name' => $this->language->get('text_payment')
		);
		
		$data['breadcrumbs'][] = array(
			'href' => $this->url->link('extension/payment/rakuten', 'user_token=' . $user_token, true),
			'name' => $this->language->get('heading_title')
		);
		
		/* Status */
		if (isset($this->request->post['payment_rakuten_status'])) {
			$data['payment_rakuten_status'] = $this->request->post['payment_rakuten_status'];
		} else {
			$data['payment_rakuten_status'] = $this->config->get('payment_rakuten_status');
		}
		
		/* Email */
		if (isset($this->request->post['payment_rakuten_email'])) {
			$data['payment_rakuten_email'] = $this->request->post['payment_rakuten_email'];
		} else {
			$data['payment_rakuten_email'] = $this->config->get('payment_rakuten_email');
		}

        /* Document */
        if (isset($this->request->post['payment_rakuten_document'])) {
            $data['payment_rakuten_document'] = $this->request->post['payment_rakuten_document'];
        } else {
            $data['payment_rakuten_document'] = $this->config->get('payment_rakuten_document');
        }

		/* Api */
		if (isset($this->request->post['payment_rakuten_api'])) {
			$data['payment_rakuten_api'] = $this->request->post['payment_rakuten_api'];
		} else {
			$data['payment_rakuten_api'] = $this->config->get('payment_rakuten_api');
		}

		/* Signature */
		if (isset($this->request->post['payment_rakuten_signature'])) {
			$data['payment_rakuten_signature'] = $this->request->post['payment_rakuten_signature'];
		} else {
			$data['payment_rakuten_signature'] = $this->config->get('payment_rakuten_signature');
		}
		
		/* Environment */
		if (isset($this->request->post['payment_rakuten_environment'])) {
			$data['payment_rakuten_environment'] = $this->request->post['payment_rakuten_environment'];
		} else {
			$data['payment_rakuten_environment'] = $this->config->get('payment_rakuten_environment');
		}
		
		/* Debug */
		if (isset($this->request->post['payment_rakuten_debug'])) {
			$data['payment_rakuten_debug'] = $this->request->post['payment_rakuten_debug'];
		} else {
			$data['payment_rakuten_debug'] = $this->config->get('payment_rakuten_debug');
		}
		
		/* Notificar Cliente */
		if (isset($this->request->post['payment_rakuten_notificar_cliente'])) {
			$data['payment_rakuten_notificar_cliente'] = $this->request->post['payment_rakuten_notificar_cliente'];
		} else {
			$data['payment_rakuten_notificar_cliente'] = $this->config->get('payment_rakuten_notificar_cliente');
		}

		/* Custom Field Número */
		if (isset($this->request->post['payment_rakuten_number'])) {
			$data['payment_rakuten_number'] = $this->request->post['payment_rakuten_number'];
		} else {
			$data['payment_rakuten_number'] = $this->config->get('payment_rakuten_number');
		}

		/* Custom Field Data de Nascimento */
		if (isset($this->request->post['payment_rakuten_complement'])) {
			$data['payment_rakuten_complement'] = $this->request->post['payment_rakuten_complement'];
		} else {
			$data['payment_rakuten_complement'] = $this->config->get('payment_rakuten_complement');
		}

		/* Custom Field CPF */
		if (isset($this->request->post['payment_rakuten_cpf'])) {
			$data['payment_rakuten_cpf'] = $this->request->post['payment_rakuten_cpf'];
		} else {
			$data['payment_rakuten_cpf'] = $this->config->get('payment_rakuten_cpf');
		}

		
		/* Aguardando Pagamento */
		if (isset($this->request->post['payment_rakuten_aguardando_pagamento'])) {
			$data['payment_rakuten_aguardando_pagamento'] = $this->request->post['payment_rakuten_aguardando_pagamento'];
		} else {
			$data['payment_rakuten_aguardando_pagamento'] = $this->config->get('payment_rakuten_aguardando_pagamento');
		}

		/* Paga (Pago|Completo) */
		if (isset($this->request->post['payment_rakuten_paga'])) {
			$data['payment_rakuten_paga'] = $this->request->post['payment_rakuten_paga'];
		} else {
			$data['payment_rakuten_paga'] = $this->config->get('payment_rakuten_paga');
		}

		/* Falha */
		if (isset($this->request->post['payment_rakuten_devolvida'])) {
			$data['payment_rakuten_falha'] = $this->request->post['payment_rakuten_falha'];
		} else {
			$data['payment_rakuten_falha'] = $this->config->get('payment_rakuten_falha');
		}

		/* Negada */
		if (isset($this->request->post['payment_rakuten_negada'])) {
			$data['payment_rakuten_negada'] = $this->request->post['payment_rakuten_negada'];
		} else {
			$data['payment_rakuten_negada'] = $this->config->get('payment_rakuten_negada');
		}

		/* Devolvido (Reembolsado) */
		if (isset($this->request->post['payment_rakuten_devolvida'])) {
			$data['payment_rakuten_devolvida'] = $this->request->post['payment_rakuten_devolvida'];
		} else {
			$data['payment_rakuten_devolvida'] = $this->config->get('payment_rakuten_devolvida');
		}

		/* Cancelado */
		if (isset($this->request->post['payment_rakuten_cancelada'])) {
			$data['payment_rakuten_cancelada'] = $this->request->post['payment_rakuten_cancelada'];
		} else {
			$data['payment_rakuten_cancelada'] = $this->config->get('payment_rakuten_cancelada');
		}

		/* Zona Geográfica */
		if (isset($this->request->post['payment_rakuten_geo_zone'])) {
			$data['payment_rakuten_geo_zone'] = $this->request->post['payment_rakuten_geo_zone'];
		} else {
			$data['payment_rakuten_geo_zone'] = $this->config->get('payment_rakuten_geo_zone');
		}
		
		/* Ordem */
		if (isset($this->request->post['payment_rakuten_sort_order'])) {
			$data['payment_rakuten_sort_order'] = $this->request->post['payment_rakuten_sort_order'];
		} else {
			$data['payment_rakuten_sort_order'] = $this->config->get('payment_rakuten_sort_order');
		}

        /* Juros comprador */
        if (isset($this->request->post['payment_rakuten_juros'])) {
            $data['payment_rakuten_juros'] = $this->request->post['payment_rakuten_juros'];
        } else {
            $data['payment_rakuten_juros'] = $this->config->get('payment_rakuten_juros');
        }

        /* Valor mínimo de parcelas */
        if (isset($this->request->post['payment_rakuten_minimo_parcelas'])) {
            $data['payment_rakuten_minimo_parcelas'] = $this->request->post['payment_rakuten_minimo_parcelas'];
        } else {
            $data['payment_rakuten_minimo_parcelas'] = $this->config->get('payment_rakuten_minimo_parcelas');
        }

        /* Quantidade máxima de parcela */
        if (isset($this->request->post['payment_rakuten_max_parcelas'])) {
            $data['payment_rakuten_max_parcelas'] = $this->request->post['payment_rakuten_max_parcelas'];
        } else {
            $data['payment_rakuten_max_parcelas'] = $this->config->get('payment_rakuten_max_parcelas');
        }

		/* Quantidade de parcelas */
		if (isset($this->request->post['payment_rakuten_qnt_parcelas'])) {
			$data['payment_rakuten_qnt_parcelas'] = $this->request->post['payment_rakuten_qnt_parcelas'];
		} else {
			$data['payment_rakuten_qnt_parcelas'] = $this->config->get('payment_rakuten_qnt_parcelas');
		}

		/* Parcelas sem juros */
		if (isset($this->request->post['payment_rakuten_parcelas_sem_juros'])) {
			$data['payment_rakuten_parcelas_sem_juros'] = $this->request->post['payment_rakuten_parcelas_sem_juros'];
		} else {
			$data['payment_rakuten_parcelas_sem_juros'] = $this->config->get('payment_rakuten_parcelas_sem_juros');
		}

		/* Boleto */
		if (isset($this->request->post['payment_rakuten_boleto_status'])) {
			$data['payment_rakuten_boleto_status'] = $this->request->post['payment_rakuten_boleto_status'];
		} else {
			$data['payment_rakuten_boleto_status'] = $this->config->get('payment_rakuten_boleto_status');
		}

		/* Valor minimo boleto */
		if (isset($this->request->post['payment_rakuten_valor_minimo_boleto'])) {
			$data['payment_rakuten_valor_minimo_boleto'] = $this->request->post['payment_rakuten_valor_minimo_boleto'];
		} else {
			$data['payment_rakuten_valor_minimo_boleto'] = $this->config->get('payment_rakuten_valor_minimo_boleto');
		}

		/* Cartão de Crédito */
		if (isset($this->request->post['payment_rakuten_cartao_status'])) {
			$data['payment_rakuten_cartao_status'] = $this->request->post['payment_rakuten_cartao_status'];
		} else {
			$data['payment_rakuten_cartao_status'] = $this->config->get('payment_rakuten_cartao_status');
		}

		/* Valor minimo cartão */
		if (isset($this->request->post['payment_rakuten_valor_minimo_cartao'])) {
			$data['payment_rakuten_valor_minimo_cartao'] = $this->request->post['payment_rakuten_valor_minimo_cartao'];
		} else {
			$data['payment_rakuten_valor_minimo_cartao'] = $this->config->get('payment_rakuten_valor_minimo_cartao');
		}

		/* Validar credenciais */
		if (isset($this->request->post['payment_rakuten_valor_minimo_cartao'])) {
			$data['payment_rakuten_valor_minimo_cartao'] = $this->model_extension_payment_rakuten->verifyCredentials();
		} else {
			$data['payment_rakuten_valor_minimo_cartao'] = $this->config->get('payment_rakuten_valor_minimo_cartao');
		}


		/* Status de Pagamento */
		$data['statuses'] = $this->model_localisation_order_status->getOrderStatuses();
		
		/* Zonas Geográficas */
		$data['zones'] = $this->model_localisation_geo_zone->getGeoZones();
		
		/* Custom Field */
		$data['custom_fields'] = $this->model_customer_custom_field->getCustomFields();

		/* Debug */
		if (file_exists(DIR_LOGS . 'rakuten.log')) {
			if ((isset($this->request->post['payment_rakuten_debug']) && $this->request->post['payment_rakuten_debug'])) {
				$data['debug'] = file(DIR_LOGS . 'rakuten.log');
			} elseif ($this->config->get('payment_rakuten_debug')) {
				$data['debug'] = file(DIR_LOGS . 'rakuten.log');
			} else {
				$data['debug'] = array();
			}
		} else {
			$data['debug'] = array();
		}
		
		/* Links */
		$data['action'] = $this->url->link('extension/payment/rakuten', 'user_token=' . $user_token, true);
		$data['cancel'] = $this->url->link('extension/extension', 'user_token=' . $user_token, true);
		
        $data['link_custom_field'] = $this->url->link('customer/custom_field', 'user_token=' . $user_token, true);

		$data['header'] = $this->load->controller('common/header');
		$data['column_left'] = $this->load->controller('common/column_left');
		$data['footer'] = $this->load->controller('common/footer');
		$data['webhook'] = HTTPS_SERVER;
		
		$this->response->setOutput($this->load->view('extension/payment/rakuten', $data));
	}

	public function credentials()
    {
        $this->load->model('extension/payment/rakuten');
        $rakuten = $this->model_extension_payment_rakuten;
        $resultCredentials = $rakuten->verifyCredentials();

        return $resultCredentials;
    }

	public function validate() {

		/* Error Permission */
		if (!$this->user->hasPermission('modify', 'extension/payment/rakuten')) {
			$this->error['warning'] = $this->language->get('warning');
		}
		
		/* Error Email */
		if (!filter_var($this->request->post['payment_rakuten_email'], FILTER_VALIDATE_EMAIL)) {
			$this->error['email'] = $this->language->get('error_email');
		}

        /* Error Document */
        if ((int)($this->request->post['payment_rakuten_document']) < 12) {
            $this->error['document'] = $this->language->get('error_document');
        }

		/* Error Api */
		if (strlen($this->request->post['payment_rakuten_api']) < 32) {
			$this->error['api'] = $this->language->get('error_api');
		}

        /* Error Signature */
        if (strlen($this->request->post['payment_rakuten_signature']) < 32) {
            $this->error['signature'] = $this->language->get('error_signature');
        }

		/* Error Quantidade de Parcelas */
		if (!filter_var($this->request->post['payment_rakuten_qnt_parcelas'], FILTER_VALIDATE_INT)) {
			$this->error['qnt_parcelas'] = $this->language->get('error_qnt_parcela');
		} elseif ($this->request->post['payment_rakuten_qnt_parcelas'] > 18) {
			$this->error['qnt_parcelas'] = $this->language->get('error_qnt_parcela_invalido');
		}
		
		/* Error Quantidade Parcelas sem Juros */
		if (!filter_var($this->request->post['payment_rakuten_parcelas_sem_juros'], FILTER_VALIDATE_INT)) {
			$this->error['parcelas_sem_juros'] = $this->language->get('error_parcelas_sem_juros');
		} elseif ($this->request->post['payment_rakuten_parcelas_sem_juros'] > 18) {
			$this->error['parcelas_sem_juros'] = $this->language->get('error_parcelas_sem_juros_invalido');
		}
		
		/* Error Boleto */
		if ($this->request->post['payment_rakuten_boleto_status']) {
			if (!filter_var($this->request->post['payment_rakuten_valor_minimo_boleto'], FILTER_VALIDATE_FLOAT)) {
				$this->request->post['payment_rakuten_valor_minimo_boleto'] = 1.00;
			}
		}

		/* Error Cartão de Crédito */
		if ($this->request->post['payment_rakuten_cartao_status']) {
			if (!filter_var($this->request->post['payment_rakuten_valor_minimo_cartao'], FILTER_VALIDATE_FLOAT)) {
				$this->request->post['payment_rakuten_valor_minimo_cartao'] = 1.00;
			}
		}

		return !$this->error;
	}

    public function install() {
        $this->load->model('extension/payment/rakuten');
        $this->model_extension_payment_rakuten->install();
	}
	
    public function uninstall() {
        $this->load->model('extension/payment/rakuten');
        $this->model_extension_payment_rakuten->uninstall();
	}
}
