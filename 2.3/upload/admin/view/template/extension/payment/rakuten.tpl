<?php echo $header ?> <?php echo $column_left ?>

<div id="content">

  <!-- Page Header -->
  <div class="page-header">
    <div class="container-fluid">

      <div class="pull-right">
        <button type="submit" form="form-moip" data-toggle="tooltip" title="<?php echo $button_save ?>" class="btn btn-primary"><i class="fa fa-save"></i></button>
        <a href="#" data-toggle="tooltip" title="<?php echo $button_cancel ?>" class="btn btn-default"><i class="fa fa-reply"></i></a>	
      </div>

      <h1><?php echo $heading_title ?></h1>
      
      <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
        <li><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['name'] ?></a></li>
        <?php endforeach ?>
      </ul>
    </div>
  </div>

  <!-- Container -->
  <div class="container-fluid">

    <!-- Error -->
    <?php if ($warning) { ?>
    <div class="alert alert-danger">
      <i class="fa fa-exclamation-circle"></i> <?php echo $warning ?>
      <button type="button" class="close" data-dismiss="alert">&times;</button>
    </div>
    <?php } ?>

    <!-- Panel -->
    <div class="panel panel-default">

      <!-- Title -->
      <div class="panel-heading">
        <h3 class="panel-title"><i class="fa fa-pencil"></i> <?php echo $heading_title ?></h3>
      </div>

      <!-- Body -->
      <div class="panel-body">

        <!-- Nav -->
        <ul class="nav nav-tabs">
          <li><a data-toggle="tab" href="#config"><?php echo $tab_config ?></a></li>
          <li><a data-toggle="tab" href="#payment-status"><?php echo $tab_status_pagamento ?></a></li>
          <li><a data-toggle="tab" href="#area"><?php echo $tab_geo_zone ?></a></li>
          <li><a data-toggle="tab" href="#payment-method"><?php echo $tab_formas_de_pagamento ?></a></li>
          <li><a data-toggle="tab" href="#plots"><?php echo $tab_parcelas ?></a></li>
        </ul>

        <!-- Form -->
        <form action="<?php echo $action ?>" method="post" enctype="multipart/form-data" id="form-moip" class="form-horizontal">
          <div class="tab-content">

            <!-- Tab Config -->
            <div class="tab-pane" id="config">

              <!-- Status -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_status ?>"><?php echo $entry_status ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_status" class="form-control">
                  <?php if ($payment_rakuten_status == '1') { ?>
                  <option value="1" selected><?php echo $text_enabled ?></option>
                  <?php } else { ?>
                  <option value="1"><?php echo $text_enabled ?></option>
                  <?php } ?>

                  <?php if ($payment_rakuten_status == '0') { ?>
                  <option value="0" selected><?php echo $text_disabled ?></option>
                  <?php } else { ?>
                  <option value="0"><?php echo $text_disabled ?></option>
                  <?php } ?>
                  </select>
                </div>
              </div>

              <!-- Email -->
              <div class="form-group required">
               <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_email ?>"><?php echo $entry_email ?></span></label>
                <div class="col-sm-10">
                  <input name="payment_rakuten_email" type="text" class="form-control" value="<?php echo $payment_rakuten_email ?>" />
                  <?php if ($error_email) { ?>
                  <div class="text-danger"><?php echo $error_email ?></div>
                  <?php } ?>
                </div>
              </div>

              <!-- Document -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_document ?>"><?php echo $entry_document ?></span></label>
                <div class="col-sm-10">
                  <input name="payment_rakuten_document" id="payment_rakuten_document" type="text" class="form-control" value="<?php echo $payment_rakuten_document ?>" />
                  <?php if ($error_document) { ?>
                    <div class="text-danger"><?php echo $error_document ?></div>
                  <?php } ?>
                </div>
              </div>

              <!-- Api -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_api ?>"><?php echo $entry_api ?></span></label>
                <div class="col-sm-10">
                  <input name="payment_rakuten_api" id="payment_rakuten_api" type="text" class="form-control" value="<?php echo $payment_rakuten_api ?>" />
                   <?php if ($error_api) { ?>
                  <div class="text-danger"><?php echo $error_api ?></div>
                  <?php } ?>
                </div>
              </div>

              <!-- Signature -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_signature ?>"><?php echo $entry_signature ?></span></label>
                <div class="col-sm-10">
                  <input name="payment_rakuten_signature" id="payment_rakuten_signature" type="text" class="form-control" value="<?php echo $payment_rakuten_signature ?>" />
                   <?php if ($error_signature) { ?>
                  <div class="text-danger"><?php echo $error_signature ?></div>
                  <?php } ?>
                </div>
              </div>

              <!-- Environment -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_environment ?>"><?php echo $entry_environment ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_environment" class="form-control" id="payment_rakuten_environment">
                     <?php if ($payment_rakuten_environment == 'sandbox') { ?>
                    <option value="sandbox" selected><?php echo $text_sandbox ?></option>
                    <?php } else { ?>
                    <option value="sandbox"><?php echo $text_sandbox ?></option>
                    <?php } ?>


                    <?php if ($payment_rakuten_environment == 'production') { ?>
                    <option value="production" selected><?php echo $text_production ?></option>
                    <?php } else { ?>
                    <option value="production"><?php echo $text_production ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <!-- Debug -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_debug ?>"><?php echo $entry_debug ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_debug" class="form-control">
                     <?php if ($payment_rakuten_debug == '1') { ?>
                    <option value="1" selected><?php echo $text_yes ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_yes ?></option>
                    <?php } ?>


                    <?php if ($payment_rakuten_debug == '0') { ?>
                    <option value="0" selected><?php echo $text_no ?></option>
                    <?php } else { ?>
                    <option value="0"><?php echo $text_no ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <!-- Validar credentiais -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_validate_credentials ?>"><?php echo $entry_validate_credentials ?></span></label>
                <div class="col-sm-10">
                  <span class="input-group-btn">
                    <p class="btn btn-primary" id="botao_validar"><?php echo $text_validate_credentials ?></p>
                  </span>
                </div>
              </div>

              <!-- Notificar Cliente -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_notificar_cliente ?>"><?php echo $entry_notificar_cliente ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_notificar_cliente" class="form-control">
                     <?php if ($payment_rakuten_notificar_cliente == '1') { ?>
                    <option value="1" selected><?php echo $text_yes ?></option>
                    <?php } else { ?>
                    <option value="1"><?php echo $text_yes ?></option>
                    <?php } ?>


                    <?php if ($payment_rakuten_notificar_cliente == '0') { ?>
                    <option value="0" selected><?php echo $text_no ?></option>
                    <?php } else { ?>
                    <option value="0"><?php echo $text_no ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <!-- Custom Field Number -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_number ?>"><?php echo $entry_number ?></span></label>
                <div class="col-sm-10">
                  <span class="input-group">
                    <select name="payment_rakuten_number" class="form-control">
                    <?php foreach ($custom_fields as $custom_field): ?>
                     <?php if ($custom_field['custom_field_id'] == $payment_rakuten_number) { ?>
                    <option value="<?php echo $custom_field['custom_field_id'] ?>" selected><?php echo $custom_field['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $custom_field['custom_field_id'] ?>"><?php echo $custom_field['name'] ?></option>
                    <?php } ?>
                     <?php endforeach; ?>
                    </select>

                    <span class="input-group-btn">
                      <a href="<?php echo $link_custom_field ?>" class="btn btn-primary"><?php echo $text_custom_field ?></a>
                    </span>
                  </span>
                </div>
              </div>

              <!-- Custom Field Complement -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_complement ?>"><?php echo $entry_complement ?></span></label>
                <div class="col-sm-10">
                  <span class="input-group">
                    <select name="payment_rakuten_complement" class="form-control">
                    <?php foreach ($custom_fields as $custom_field): ?>
                     <?php if ($custom_field['custom_field_id'] == $payment_rakuten_complement) { ?>
                    <option value="<?php echo $custom_field['custom_field_id'] ?>" selected><?php echo $custom_field['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $custom_field['custom_field_id'] ?>"><?php echo $custom_field['name'] ?></option>
                    <?php } ?>
                     <?php endforeach; ?>
                    </select>

                    <span class="input-group-btn">
                      <a href="<?php echo $link_custom_field ?>" class="btn btn-primary"><?php echo $text_custom_field ?></a>
                    </span>
                  </span>
                </div>
              </div>

              <!-- Custom Field (CPF) -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_cpf ?>"><?php echo $entry_cpf ?></span></label>
                <div class="col-sm-10">
                  <span class="input-group">
                    <select name="payment_rakuten_cpf" class="form-control">
                      <?php foreach ($custom_fields as $custom_field): ?>
                         <?php if ($custom_field['custom_field_id'] == $payment_rakuten_cpf) { ?>
                          <option value="<?php echo $custom_field['custom_field_id'] ?>" selected><?php echo $custom_field['name'] ?></option>
                        <?php } else { ?>
                          <option value="<?php echo $custom_field['custom_field_id'] ?>"><?php echo $custom_field['name'] ?></option>
                        <?php } ?>
                      <?php endforeach; ?>
                    </select>

                    <span class="input-group-btn">
                      <a href="<?php echo $link_custom_field ?>" class="btn btn-primary"><?php echo $text_custom_field ?></a>
                    </span>
                  </span>
                </div>
              </div>

              <!-- URL de Retorno -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_url_retorno ?></label>
                <div class="col-sm-10">
                  <input type="text" disabled value="<?php echo $webhook ?>index.php?route=extension/payment/rakuten/callback" class="form-control" />
                </div>
              </div>
            </div>

            <!-- Tab Status de Pagamento -->
            <div class="tab-pane" id="payment-status">

              <!-- Aguardando Pagamento -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_aguardando_pagamento ?>"><?php echo $entry_aguardando_pagamento ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_aguardando_pagamento" class="form-control">
                  <?php foreach ($statuses as $status): ?>
                   <?php if ($payment_rakuten_aguardando_pagamento == $status['order_status_id']) { ?>
                  <option value="<?php echo $status['order_status_id'] ?>" selected><?php echo $status['name'] ?></option>
                  <?php } else { ?>
                  <option value="<?php echo $status['order_status_id'] ?>"><?php echo $status['name'] ?></option>
                  <?php } ?>
                   <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Pago -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_pago ?>"><?php echo $entry_pago ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_paga" class="form-control">
                    <?php foreach ($statuses as $status): ?>
                     <?php if ($payment_rakuten_paga == $status['order_status_id']) { ?>
                    <option value="<?php echo $status['order_status_id'] ?>" selected><?php echo $status['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $status['order_status_id'] ?>"><?php echo $status['name'] ?></option>
                    <?php } ?>
                     <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Devolvida -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_devolvida ?>"><?php echo $entry_devolvido ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_devolvida" class="form-control">
                    <?php foreach ($statuses as $status): ?>
                     <?php if ($payment_rakuten_devolvida == $status['order_status_id']) { ?>
                    <option value="<?php echo $status['order_status_id'] ?>" selected><?php echo $status['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $status['order_status_id'] ?>"><?php echo $status['name'] ?></option>
                    <?php } ?>
                     <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Falha -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_falha ?>"><?php echo $entry_falha ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_falha" class="form-control">
                    <?php foreach ($statuses as $status): ?>
                     <?php if ($payment_rakuten_falha == $status['order_status_id']) { ?>
                    <option value="<?php echo $status['order_status_id'] ?>" selected><?php echo $status['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $status['order_status_id'] ?>"><?php echo $status['name'] ?></option>
                    <?php } ?>
                     <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Cancelada -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $entry_cancelada ?>"><?php echo $entry_cancelada ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_cancelada" class="form-control">
                   <?php foreach ($statuses as $status): ?>
                    <?php if ($payment_rakuten_cancelada == $status['order_status_id']) { ?>
                   <option value="<?php echo $status['order_status_id'] ?>" selected><?php echo $status['name'] ?></option>
                   <?php } else { ?>
                   <option value="<?php echo $status['order_status_id'] ?>"><?php echo $status['name'] ?></option>
                   <?php } ?>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Negada -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $entry_negada ?>"><?php echo $entry_negada ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_negada" class="form-control">
                   <?php foreach ($statuses as $status): ?>
                    <?php if ($payment_rakuten_negada == $status['order_status_id']) { ?>
                   <option value="<?php echo $status['order_status_id'] ?>" selected><?php echo $status['name'] ?></option>
                   <?php } else { ?>
                   <option value="<?php echo $status['order_status_id'] ?>"><?php echo $status['name'] ?></option>
                   <?php } ?>
                    <?php endforeach; ?>
                  </select>
                </div>
              </div>
            </div>

            <!-- Tab Área Geográfica e Ordem -->
            <div class="tab-pane" id="area">

              <!-- Zona Geográfica -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_geo_zone ?></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_geo_zone" class="form-control">
                    <option value="0"><?php echo $text_all_zones ?></option>
                    <?php foreach ($zones as $zone): ?>
                     <?php if ($payment_rakuten_geo_zone == $zone['geo_zone_id']) { ?>
                    <option value="<?php echo $zone['geo_zone_id'] ?>" selected><?php echo $zone['name'] ?></option>
                    <?php } else { ?>
                    <option value="<?php echo $zone['geo_zone_id'] ?>"><?php echo $zone['name'] ?></option>
                    <?php } ?>
                     <?php endforeach; ?>
                  </select>
                </div>
              </div>

              <!-- Sort Order -->
              <div class="form-group">
                <label class="col-sm-2 control-label"><?php echo $entry_sort_order ?></label>
                <div class="col-sm-10">
                  <input type="text" name="payment_rakuten_sort_order" value="<?php echo $payment_rakuten_sort_order ?>" class="form-control" />
                </div>
              </div>
            </div>

            <!-- Tab Parcelas -->
            <div class="tab-pane" id="plots">

              <!-- Juros comprador -->
              <div class="form-group required">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_juros ?>"><?php echo $entry_juros ?></span></label>
                <div class="col-sm-10">
                  <select name="payment_rakuten_juros" class="form-control">
                     <?php if ($payment_rakuten_juros == '1') { ?>
                      <option value="1" selected><?php echo $text_yes ?></option>
                    <?php } else { ?>
                      <option value="1"><?php echo $text_yes ?></option>
                    <?php } ?>

                    <?php if ($payment_rakuten_juros == '0') { ?>
                      <option value="0" selected><?php echo $text_no ?></option>
                    <?php } else { ?>
                      <option value="0"><?php echo $text_no ?></option>
                    <?php } ?>
                  </select>
                </div>
              </div>

              <!-- Quantidade de Parcelas sem Juros -->
              <div class="form-group required" id="sem_juros">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_parcela_sem_juros ?>"><?php echo $entry_parcelas_sem_juros ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="payment_rakuten_parcelas_sem_juros" value="<?php echo $payment_rakuten_parcelas_sem_juros ?>" class="form-control" />
                   <?php if ($error_parcelas_sem_juros) { ?>
                    <div class="text-danger"><?php echo $error_parcelas_sem_juros ?></div>
                  <?php } ?>
                </div>
              </div>

              <!-- Valor mínimo de parcela -->
              <div class="form-group required" id="payment_rakuten_minimo_parcelas">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_exemplo_parcela ?>"><?php echo $entry_minimo_parcelas ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="payment_rakuten_minimo_parcelas" value="<?php echo $payment_rakuten_minimo_parcelas ?>" class="form-control" />
                   <?php if ($error_qnt_parcela) { ?>
                    <div class="text-danger"><?php echo $error_qnt_parcela ?></div>
                  <?php } ?>
                </div>
              </div>

              <!-- Quantidade Máxima de Parcelas -->
              <div class="form-group required" id="payment_rakuten_qnt_parcelas">
                <label class="col-sm-2 control-label"><span data-toggle="tooltip" data-html="true" title="<?php echo $help_exemplo_parcela ?>"><?php echo $entry_qnt_parcelas ?></span></label>
                <div class="col-sm-10">
                  <input type="text" name="payment_rakuten_qnt_parcelas" value="<?php echo $payment_rakuten_qnt_parcelas ?>" class="form-control" />
                   <?php if ($error_qnt_parcela) { ?>
                  <div class="text-danger"><?php echo $error_qnt_parcela ?></div>
                  <?php } ?>
                </div>
              </div>


            </div>

            <!-- Tab Métodos de Pagamento -->
            <div class="tab-pane" id="payment-method">

              <!-- Boleto -->
              <fieldset>
                <legend><?php echo $text_boleto ?></legend>


                <!-- Status Boleto -->
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_status ?></label>
                  <div class="col-sm-10">
                    <select name="payment_rakuten_boleto_status" class="form-control">
                       <?php if ($payment_rakuten_boleto_status == '1') { ?>
                      <option value="1" selected><?php echo $text_enabled ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled ?></option>
                      <?php } ?>


                      <?php if ($payment_rakuten_boleto_status == '0') { ?>
                      <option value="0" selected><?php echo $text_disabled ?></option>
                      <?php } else { ?>
                      <option value="0"><?php echo $text_disabled ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <!-- Valor Mínimo para Boleto -->
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_valor_minimo ?></label>
                  <div class="col-sm-10">
                    <input type="text" name="payment_rakuten_valor_minimo_boleto" value="<?php echo $payment_rakuten_valor_minimo_boleto ?>" class="form-control" />
                  </div>
                </div>
              </fieldset>

              <!-- Cartão de Crédito -->
              <fieldset>
                <legend><?php echo $text_cartao ?></legend>

                <!-- Status Cartão de Crédito -->
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_status ?></label>
                  <div class="col-sm-10">
                    <select name="payment_rakuten_cartao_status" class="form-control">
                       <?php if ($payment_rakuten_cartao_status == '1') { ?>
                      <option value="1" selected><?php echo $text_enabled ?></option>
                      <?php } else { ?>
                      <option value="1"><?php echo $text_enabled ?></option>
                      <?php } ?>


                      <?php if ($payment_rakuten_cartao_status == '0') { ?>
                      <option value="0" selected><?php echo $text_disabled ?></option>
                      <?php } else { ?>
                      <option value="0"><?php echo $text_disabled ?></option>
                      <?php } ?>
                    </select>
                  </div>
                </div>

                <!-- Valor mínimo para cartão de crédito -->
                <div class="form-group">
                  <label class="col-sm-2 control-label"><?php echo $entry_valor_minimo ?></label>
                  <div class="col-sm-10">
                    <input type="text" name="payment_rakuten_valor_minimo_cartao" value="<?php echo $payment_rakuten_valor_minimo_cartao ?>" class="form-control" />
                  </div>
                </div>
              </fieldset>

            </div> <!-- /#payment-method -->
          </div>
        </form> <!-- /Form -->
      </div><!-- /.panel-body -->
    </div><!-- /.panel.panel-default -->
  </div><!-- /.container-fluid -->
</div><!-- /#content -->

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@8"></script>

<script type="text/javascript">
  $('fieldset legend').css('cursor', 'pointer');
  $('fieldset').css('margin-bottom', 30);

  $('.nav-tabs li:first').addClass('active');
  $('.tab-content div:first').addClass('active');

  $('fieldset legend').click(function(){
      $(this).parent().find('div').slideToggle('slow');
  });

  var buyer = document.querySelector("[name='payment_rakuten_juros']");
  var no_instalments = document.getElementById('sem_juros');
  var min_installment = document.getElementById('payment_rakuten_minimo_parcelas');
  var max_installment = document.getElementById('payment_rakuten_qnt_parcelas');
  var button = document.getElementById('botao_validar');

  if (buyer.value === "0") {
    no_instalments.style.display = "none";
    min_installment.style.display = "block";
    max_installment.style.display = "block";
  } else {
    no_instalments.style.display = "block";
    min_installment.style.display = "none";
    max_installment.style.display = "none";
  }

  buyer.addEventListener('change', function() {
    if (buyer.value === "0") {
      no_instalments.style.display = "none";
      min_installment.style.display = "block";
      max_installment.style.display = "block";
    } else {
      no_instalments.style.display = "block";
      min_installment.style.display = "none";
      max_installment.style.display = "none";
    }
  });

  button.addEventListener('click', function() {

    var rakuten_environment = document.getElementById('payment_rakuten_environment').value;
    var cnpj = document.getElementById('payment_rakuten_document').value;
    var api = document.getElementById('payment_rakuten_api').value;

    console.log('Validate Credentials start');
    $.ajax({
      url: 'index.php?route=extension/payment/rakuten/credentials&token=<?php echo $token ?>',
      type: 'POST',
      data: {
        cnpj: function () {
          return cnpj;
        },
        apiKey: function () {
          return api;
        },
        environment: function () {
          return rakuten_environment;
        },
      },
      beforeSend: function() {
        $('#botao_validar').button('loading');
      },
      success: function (response) {

        if (response == 200) {

          Swal.fire({
            title: response + ' OK: Parabéns',
            html: '<p>Suas credenciais estão corretas.' + '</p>' +
                  '<p>Ambiente: ' + '<strong>' + rakuten_environment + '</strong></p>',
            type: 'success',
            confirmButtonText: '<i class="fa fa-thumbs-up"></i> Fechar'
          });

        } else {

          Swal.fire({
            title: response + ' Erro',
            html: '<p>Verifique suas credenciais com o atendimento Rakuten Pay</p>' +
                  '<p>Ambiente: ' + '<strong>' + rakuten_environment + '</strong></p>',
            type: 'error',
            cancelButtonText: '<i class="fa fa-thumbs-down"></i> Fechar'

          });
        }

      },
      error: function(response) {
        console.log(response);
        Swal.fire({
          title: response + ' Erro',
          html: '<p>Ocorreu problema na integração</p>' +
                '<p>Ambiente: ' + '<strong>' + rakuten_environment + '</strong></p>',
          type: 'error',
          cancelButtonText: '<i class="fa fa-thumbs-down"></i> Fechar'

        });
        return false;
      },
      complete: function(){
        $('#botao_validar').button('reset');
      }
    })
  })
</script>

<?php echo $footer ?>
