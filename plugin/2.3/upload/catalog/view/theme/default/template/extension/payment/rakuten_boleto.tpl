<div id="warning" class="alert alert-danger" role="alert" style="display:none"></div>

<div id="info" class="alert alert-info" role="alert" style="display:none">Aguarde....</div>

  <div class="form-horizontal col-sm-offset-3">
  <div class="form-group">
    <label class="col-sm-2 control-label">CPF</label>
    <div class="col-sm-3">
        <form action="<?php echo $continue; ?>" data-rkp="form" id="rakuten-billet">
            <fieldset>
                <input type="hidden" data-rkp="method" value="billet">
            </fieldset>
        </form>
      <input type="text" name="cpf" id="cpf" value="<?php echo $cpf ?>" class="form-control" />
      <span id="error-cpf" class="hide">Campo CPF/CNPJ do cartão inválido.</span>
    </div>
  </div>

  <div class="form-group">
    <div class="col-sm-10 col-sm-offset-2">
      <button type="button" id="button-confirm" class="btn btn-primary" data-loading-text="Aguarde...">
        <i class="fa fa-barcode"></i>
        Finalizar compra
      </button>
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

<script type="text/javascript" src="catalog/view/javascript/rakuten/colorbox/jquery.colorbox-min.js"></script>
<script type="text/javascript" src="<?php echo $rpay_js ?>"></script>
<link href="catalog/view/javascript/rakuten/colorbox/colorbox.css" rel="stylesheet" media="all" />
<script type="text/javascript" src="catalog/view/javascript/rakuten/validate.js"></script>
<script type="text/javascript" src="catalog/view/javascript/rakuten/sweetalert2.js"></script>

<script type="text/javascript">

    var form = document.getElementById('rakuten-billet');
    var button = document.querySelector('#button-confirm');

    button.addEventListener("click", function(e) {

        var rpay = new RPay();
        var headers = {
            'Accept': 'application/json'
        };

        console.log('Rakuten Boleto Event');

        rpay.listeners = {
            "result:success": function() {
                console.log('result: Success');
                getData();
                // form.submit();
            },
            "result:error":   function(errors){
                console.log(errors);
            }
        };
        // Previna a execução da submissão do formulário
        e.preventDefault();
        // Execute a função generate() passando o form como argumento
        rpay.generate(form);

        function getData() {

            $.ajax({
                url: 'index.php?route=extension/payment/rakuten_boleto/transition',
                type: 'POST',
                headers: headers,
                data: { fingerprint: $('#rakuten-billet > input').val() },
                beforeSend: function() {
                    $('#button-confirm').button('loading');
                },
                success: function (billet_url) {
                    console.log('success transition...');
                },
                complete: function(){
                    $('#button-confirm').button('reset');
                    console.log('completo');
                    location.href = '<?php echo $continue ?>';
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
