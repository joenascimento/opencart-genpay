<div class="content">
  <div class="row-fluid">

        <div class="form-horizontal">

          <form action="#" class="col-sm-offset-3 col-sm-6" id="rakuten-credit-card">
            <input type="hidden" data-rkp="method" value="credit_card">

            <div class="form-group col-sm-12">
              <div class="col-sm-12" id="card-name">
                <label class="control-label" for="nome">Nome:</label>
                <input class="form-control" type="text" data-rkp="card-holder-name" placeholder="Ex: Nome Completo">
                <span id="card-name-error" class="hide">Campo nome do cartão está em branco ou contém números.</span>
              </div>
            </div>

            <div class="form-group col-sm-12 titular">
              <div class="col-sm-12">
                <label class="control-label" for="cpf">CPF/CNPJ:</label>
                <input class="form-control" type="text" data-rkp="card-holder-document" id="cpf" name="cpf" placeholder="Ex: 123.456.789-09 ou 12.345.678/0001-90" value="<?php echo $cpf ?>" />
                <span id="error-cpf" class="hide">Campo CPF/CNPJ do cartão inválido.</span>
              </div>
            </div>

            <div class="form-group col-sm-12">
              <div class="col-sm-6">
                <label class="control-label" for="numero-cartao">Número do Cartão:</label>
                <input type="hidden" data-rkp="card-brand">
                <input class="form-control" type="text" data-rkp="card-number" maxlength="20" placeholder="&bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull; &bull;&bull;&bull;&bull;">
                <span id="card-number-error" class="hide">Campo número do cartão está em branco ou contém letras.</span>
                <span id="card-number-error-digits" class="hide">Número de cartão inválido.</span>
              </div>

              <div class="col-sm-6">
                <label class="control-label" for="cvv">Código de Segurança (CVV):</label>
                <input class="form-control" type="text" data-rkp="card-cvv" maxlength="4" placeholder="Ex: 123">
                <span id="card-cvv" class="hide">Campo CVV do cartão está em branco ou contém letras.</span>
              </div>
            </div>

            <div class="form-group col-sm-12">
              <label class="col-sm-12" for="validade">Validade:</label>
              <div class="col-sm-6">
                <input class="form-control" type="hidden" data-rkp="card-expiration-month" maxlength="2" placeholder="01">
                <select class="form-control" id="card-expiration-month">
                  <option value="" disabled selected>Mês</option>
                  <option value="01">01</option>
                  <option value="02">02</option>
                  <option value="03">03</option>
                  <option value="04">04</option>
                  <option value="05">05</option>
                  <option value="06">06</option>
                  <option value="07">07</option>
                  <option value="08">08</option>
                  <option value="09">09</option>
                  <option value="10">10</option>
                  <option value="11">11</option>
                  <option value="12">12</option>
                </select>
                <span id="card-month-error" class="hide">Campo mês de validade do cartão está vazio ou contém números.</span>
              </div>

              <div class="col-sm-6">
                <input class="form-control" type="hidden" data-rkp="card-expiration-year" maxlength="4" placeholder="2020">
                <select class="form-control" id="card-expiration-year">
                  <option value="" disabled selected>Ano</option>
                  <?php foreach ($years as $year): ?>
                    <option value="<?php echo $year['year'] ?>"><?php echo $year['year'] ?></option>
                  <?php endforeach; ?>
                </select>
                <span id="card-year-error" class="hide">Campo número do cartão está em branco ou contém letras.</span>
              </div>
            </div>

            <div class="form-group col-sm-12 vhide">
              <div class="col-sm-12">
                <label class="control-label" for="parcelas">Parcelas:</label>
                <select class="form-control" id="parcelas" name="parcelas">
                  <option value="" disabled selected>Escolha a quantidade de parcelas</option>

                  <?php foreach ($installments as $installment): ?>
                    <option value="<?php echo $installment['quantity'] ?>"><?php echo $installment['text'] ?></option>
                  <?php endforeach; ?>

                </select>
                <span id="card-installment" class="hide">Escolha a quantidade de parcelas.</span>
              </div>
            </div>

            <div class="form-group col-sm-12 vhide">
              <div class="col-sm-5 col-sm-offset-4">
                <button type="button" id="button-confirm" class="btn btn-primary" data-loading-text="Aguarde...">
                  <i class="fa fa-credit-card-alt"></i>
                  Finalizar Pagamento
                </button>
              </div>
            </div>
          </form>

        </div>
  </div>
</div>

<style>
  .border-error {
    border: 1px solid #ff0000 !important;
  }
  .show { display: block; color: #ff0000; font-weight: 500; }
  .hide { display: none;}
</style>

<script type="text/javascript" src="<?php echo $rpay_js ?>"></script>
<script type="text/javascript" src="catalog/view/javascript/rakuten/validate.js"></script>
<script type="text/javascript" src="catalog/view/javascript/rakuten/sweetalert2.js"></script>

<script type="text/javascript">
  var form = document.getElementById('rakuten-credit-card');
  var button = document.querySelector('#button-confirm');
  var paymentMethod = document.querySelector("[data-rkp='method']").value;
  var cardNumberField = document.querySelector("[data-rkp='card-number']");
  var cardBrandField = document.querySelector("[data-rkp='card-brand']");

  validateCardHolderName();
  validateCardNumber();
  validateCardMonthYear();
  validateCardCVV();
  validateCardInstallment();

  button.addEventListener("click", function(e) {
    // Crie um instância do tipo RPay
    var rpay = new RPay();
    var headers = {
      'Accept': 'application/json'
    };

    validateBlankFields();

    var blankfields = document.querySelector('.border-error');

    if (blankfields !== null) {

      Swal.fire({
        title: 'Erro! Preencha todos os campos necessários',
        text: '',
        type: 'error',
        confirmButtonText: 'Fechar'
      });
      return false;
    }

    $('#button-confirm').button('loading');
    console.log('Credit Card Event');

    // Execute a extração da bandeira do cartão.
    if (paymentMethod === "credit_card")
      cardBrandField.value = rpay.cardBrand(cardNumberField.value);
    // Defina as funções de callback para cada tipo de resultado
    rpay.listeners = {
      "result:success": function() {
        // defina aqui o que fazer quando fingerprint e token
        // forem gerados e adicionados ao form com successo
        // Neste exemplo abaixo seria a submissão do formulário
        // Descomente essa linha para submeter o formulário
        // form.submit();
        getData();
      },
      "result:error":   function(errors){
        // defina aqui o que fazer caso ocorra algum erro durante
        // o processo de geração de fingerprint e token
        // Exemplo:
        console.log(errors[0]['message']);
        Swal.fire({
          title: 'Erro ao gerar o Token do cartão de crédito',
          text: 'Verifique os dados: Número do cartão, mês e ano',
          type: 'error',
          confirmButtonText: 'Fechar'
        });
      }
    };
    // Previna a execução da submissão do formulário
    e.preventDefault();
    // Execute a função generate() passando o form como argumento
    rpay.generate(form);

    function getData() {

      $.ajax({
        url: 'index.php?route=extension/payment/rakuten_cartao/transition',
        type: 'POST',
        headers: headers,
        data: {
          token: function () {
            var elem = document.querySelector('#rakuten-credit-card').elements;
            var token_element = elem[0].value;

            return token_element;
          },
          fingerprint: function () {
            var elem = document.querySelector('#rakuten-credit-card').elements;
            var fingerprint_element = elem[1].value;

            return fingerprint_element;
          },
          quantity: function () {
            var installment_quantity = document.getElementById('parcelas').value;

            return installment_quantity;
          },
          brand: function () {
            var cardBrandField = document.querySelector("[data-rkp='card-brand']").value;

            return cardBrandField;
          },
          cvv: function () {
            var cardCVV = document.querySelector("[data-rkp='card-cvv']").value;

            return cardCVV;
          },
          name: function () {
            var cardHolderName = document.querySelector("[data-rkp='card-holder-name']").value;

            return cardHolderName;
          },
          document: function () {
            var cardHolderDocument = document.querySelector("[data-rkp='card-holder-document']").value;

            return cardHolderDocument;
          }
        },
        beforeSend: function() {
          $('#button-confirm').button('loading');
        },
        success: function (response) {
          console.log('success transition...');

          $.ajax({
            url: 'index.php?route=extension/payment/rakuten_cartao/confirm',
            type: 'POST',
            data: { body: response },
            success: function () {
              console.log('success confirm: ');
              console.log(response);
              // return false;
              setTimeout(function () {
                location.href = '<?php echo $continue ?>';
              }, 1000);
            },
          })
        },
        complete: function(){
          $('#button-confirm').button('reset');
          console.log('completo');
        }
      })
    }
  });

  $( '#cpf' ).blur(function(){

    // O CPF ou CNPJ
    var cpf_cnpj = $(this).val();

    // Testa a validação e formata se estiver OK
    if ( valida_cpf_cnpj( cpf_cnpj ) ) {

      $(this).val( formata_cpf_cnpj( cpf_cnpj ) );
      $(this).addClass('validate_cpf_cnpj');
      $(this).removeClass('border-error');

      document.getElementById('error-cpf').classList.remove('show');
      document.getElementById('error-cpf').classList.add('hide');
      document.getElementById('cpf').style.border = "thin solid #6ae8a6";

    } else {

      $(this).addClass('border-error');

      document.getElementById('error-cpf').classList.remove('hide');
      document.getElementById('error-cpf').classList.add('show');
      document.getElementById('cpf').focus();

    }

  });

</script>
