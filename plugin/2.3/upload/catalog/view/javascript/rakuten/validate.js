function validateCardHolderName() {

    var cardHolderName = document.querySelector("[data-rkp='card-holder-name']");
    var error = document.getElementById('card-name-error');

    cardHolderName.addEventListener('blur', function () {
        if (cardHolderName.value.length == 0 || Number.isInteger(parseInt(cardHolderName.value)) == true) {

            error.classList.remove('hide');
            error.classList.add('show');

            cardHolderName.classList.add('border-error');

        } else {

            error.classList.remove('show');
            error.classList.add('hide');

            cardHolderName.classList.remove('border-error');
            cardHolderName.style.border = "initial";
            cardHolderName.style.border = "thin solid #6ae8a6";
        }
    })
}

function validateCardNumber() {

    var cardNumber = document.querySelector("[data-rkp='card-number']");
    var error = document.getElementById('card-number-error');
    var error_digits = document.getElementById('card-number-error-digits');

    cardNumber.addEventListener('blur', function () {
        if (cardNumber.value.length == 0 || Number.isInteger(parseInt(cardNumber.value)) !== true) {

            error.classList.remove('hide');
            error.classList.add('show');

            cardNumber.classList.add('border-error');

        } else {

            error.classList.remove('show');
            error.classList.add('hide');

            cardNumber.classList.remove('border-error');
            cardNumber.style.border = "initial";
            cardNumber.style.border = "thin solid #6ae8a6";

        }

        if (cardNumber.value.length < 14) {

            error_digits.classList.remove('hide');
            error_digits.classList.add('show');

            cardNumber.classList.add('border-error');
        } else {

            var rpay = new RPay();
            cardValidate = rpay.cardValidate(cardNumber.value);

            if (cardValidate.valid) {
                console.log('cardNumber valid');
            } else {
                error_digits.classList.remove('hide');
                error_digits.classList.add('show');

                cardNumber.classList.add('border-error');
                return false;
            }

            error_digits.classList.remove('show');
            error_digits.classList.add('hide');

            cardNumber.classList.remove('border-error');
            cardNumber.style.border = "initial";
            cardNumber.style.border = "thin solid #6ae8a6";

        }
    })
}

function validateCardMonthYear() {

    var cardMonthSelect = document.querySelector("#card-expiration-month");
    var cardMonth = document.querySelector('[data-rkp=card-expiration-month]');
    var cardYearSelect = document.querySelector("#card-expiration-year");
    var cardYear = document.querySelector("[data-rkp=card-expiration-year]");
    var error_month = document.getElementById('card-month-error');
    var error_year = document.getElementById('card-year-error');

    cardMonthSelect.addEventListener('blur', function () {
        if (cardMonthSelect.value.length == 0 || Number.isInteger(parseInt(cardMonthSelect.value)) !== true) {

            error_month.classList.remove('hide');
            error_month.classList.add('show');

            cardMonthSelect.classList.add('border-error');

        } else {

            error_month.classList.remove('show');
            error_month.classList.add('hide');

            cardMonthSelect.classList.remove('border-error');
            cardMonthSelect.style.border = "initial";
            cardMonthSelect.style.border = "thin solid #6ae8a6";
            cardMonth.setAttribute('value', cardMonthSelect.value);

        }
    });

    cardYearSelect.addEventListener('blur', function () {
        if (cardYearSelect.value.length == 0 || Number.isInteger(parseInt(cardYearSelect.value)) !== true) {

            error_year.classList.remove('hide');
            error_year.classList.add('show');

            cardYearSelect.classList.add('border-error');

        } else {

            error_year.classList.remove('show');
            error_year.classList.add('hide');

            cardYearSelect.classList.remove('border-error');
            cardYearSelect.style.border = "initial";
            cardYearSelect.style.border = "thin solid #6ae8a6";
            cardYear.setAttribute('value', cardYearSelect.value);

        }
    })
}

function validateBilletFields() {
    console.log('start validateBilletFields...')
    let cpf_cnpj = document.querySelector('#cpf');
    let error_cpf = document.querySelector('#error-cpf')

    if (valida_cpf_cnpj(cpf_cnpj.value) == false) {
        cpf_cnpj.classList.add('border-error');
        error_cpf.classList.remove('hide');
        error_cpf.classList.add('show');
        console.log('error document');
        return false;
    } else {
        error_cpf.classList.remove('show');
        error_cpf.classList.add('hide');
        cpf_cnpj.classList.remove('border-error');
        cpf_cnpj.style.border = "initial";
        cpf_cnpj.style.border = "thin solid #6ae8a6";
        console.log('valid document');
    }
}

function validateBlankFields() {

    var cardNumberField = document.querySelector("[data-rkp='card-number']");
    var cardHolderNameField = document.querySelector("[data-rkp='card-holder-name']");
    var cardMonthField = document.querySelector("#card-expiration-month");
    var cardYearField = document.querySelector("#card-expiration-year");
    var cardDocument = document.getElementById("cpf");
    var cardCVV = document.querySelector("[data-rkp='card-cvv']");
    var cardInstallment = document.getElementById("parcelas");

    if (cardHolderNameField.value == 0) {

        cardHolderNameField.classList.add('border-error');

    }

    if (cardNumberField.value == 0) {

        cardNumberField.classList.add('border-error');

    }

    if (cardMonthField.value == 0) {

        cardMonthField.classList.add('border-error');

    }

    if (cardYearField.value == 0) {

        cardYearField.classList.add('border-error');

    }

    if (cardDocument.value == 0) {

        cardDocument.classList.add('border-error');

    }

    if (cardCVV.value == 0) {

        cardCVV.classList.add('border-error');

    }

    if (cardInstallment.value == 0) {

        cardInstallment.classList.add('border-error');

    }

}

function validateCardCVV() {

    var cardCVV = document.querySelector("[data-rkp='card-cvv']");
    var error = document.getElementById('card-cvv');

    cardCVV.addEventListener('blur', function () {
        if (cardCVV.value.length == 0 || cardCVV.value.length < 3|| Number.isInteger(parseInt(cardCVV.value)) !== true) {

            error.classList.remove('hide');
            error.classList.add('show');

            cardCVV.classList.add('border-error');

        } else {

            error.classList.remove('show');
            error.classList.add('hide');

            cardCVV.classList.remove('border-error');
            cardCVV.style.border = "initial";
            cardCVV.style.border = "thin solid #6ae8a6";
        }
    })
}

function validateCardInstallment() {

    var cardInstallment = document.getElementById("parcelas");
    var error = document.getElementById('card-installment');

    cardInstallment.addEventListener('change', function () {
        if (cardInstallment.value.length == 0) {

            error.classList.remove('hide');
            error.classList.add('show');

            cardInstallment.classList.add('border-error');

        } else {

            error.classList.remove('show');
            error.classList.add('hide');

            cardInstallment.classList.remove('border-error');
            cardInstallment.style.border = "initial";
            cardInstallment.style.border = "thin solid #6ae8a6";

        }
    })
}

/**
 * Remove special characters, spaces
 * @param {type} el
 * @returns {unresolved}
 */
function unmask(el) {
    return el.replace(/[/ -. ]+/g, '').trim();
}

/**
 * verifica cpf/cnpj
 *
 * @return cpf/cnpj validos
 */
function verifica_cpf_cnpj ( valor ) {

    // Garante que o valor é uma string
    valor = valor.toString();

    // Remove caracteres inválidos do valor
    valor = valor.replace(/[^0-9]/g, '');

    // Verifica CPF
    if ( valor.length === 11 ) {
        return 'CPF';
    }

    // Verifica CNPJ
    else if ( valor.length === 14 ) {
        return 'CNPJ';
    }

    // Não retorna nada
    else {
        return false;
    }

} // verifica_cpf_cnpj

/**
 *
 * calc_digitos_posicoes
 * Multiplica dígitos vezes posições
 *
 * @param string digitos Os digitos desejados
 * @param string posicoes A posição que vai iniciar a regressão
 * @param string soma_digitos A soma das multiplicações entre posições e dígitos
 *
 * @return string Os dígitos enviados concatenados com o último dígito
 */
function calc_digitos_posicoes( digitos, posicoes = 10, soma_digitos = 0 ) {

    // Garante que o valor é uma string
    digitos = digitos.toString();

    // Faz a soma dos dígitos com a posição
    // Ex. para 10 posições:
    //   0    2    5    4    6    2    8    8   4
    // x10   x9   x8   x7   x6   x5   x4   x3  x2
    //   0 + 18 + 40 + 28 + 36 + 10 + 32 + 24 + 8 = 196
    for ( var i = 0; i < digitos.length; i++  ) {
        // Preenche a soma com o dígito vezes a posição
        soma_digitos = soma_digitos + ( digitos[i] * posicoes );

        // Subtrai 1 da posição
        posicoes--;

        // Parte específica para CNPJ
        // Ex.: 5-4-3-2-9-8-7-6-5-4-3-2
        if ( posicoes < 2 ) {
            // Retorno a posição para 9
            posicoes = 9;
        }
    }

    // Captura o resto da divisão entre soma_digitos dividido por 11
    // Ex.: 196 % 11 = 9
    soma_digitos = soma_digitos % 11;

    // Verifica se soma_digitos é menor que 2
    if ( soma_digitos < 2 ) {
        // soma_digitos agora será zero
        soma_digitos = 0;
    } else {
        // Se for maior que 2, o resultado é 11 menos soma_digitos
        // Ex.: 11 - 9 = 2
        // Nosso dígito procurado é 2
        soma_digitos = 11 - soma_digitos;
    }

    // Concatena mais um dígito aos primeiro nove dígitos
    // Ex.: 025462884 + 2 = 0254628842
    var cpf = digitos + soma_digitos;

    // Retorna
    return cpf;

} // calc_digitos_posicoes

/**
 * Valida CPF
 *
 * Valida se for CPF
 *
 * @param  string cpf O CPF com ou sem pontos e traço
 *
 * @return bool True para CPF correto - False para CPF incorreto
 */
function valida_cpf( valor ) {

    // Garante que o valor é uma string
    valor = valor.toString();

    // Remove caracteres inválidos do valor
    valor = valor.replace(/[^0-9]/g, '');


    // Captura os 9 primeiros dígitos do CPF
    // Ex.: 02546288423 = 025462884
    var digitos = valor.substr(0, 9);

    // Faz o cálculo dos 9 primeiros dígitos do CPF para obter o primeiro dígito
    var novo_cpf = calc_digitos_posicoes( digitos );

    // Faz o cálculo dos 10 dígitos do CPF para obter o último dígito
    var novo_cpf = calc_digitos_posicoes( novo_cpf, 11 );

    // Verifica se o novo CPF gerado é idêntico ao CPF enviado
    if ( novo_cpf === valor ) {
        // CPF válido
        return true;
    } else {
        // CPF inválido
        return false;
    }

} // valida_cpf

/**
 * valida_cnpj
 *
 * Valida se for um CNPJ
 *
 * @param string cnpj
 *
 * @return bool true para CNPJ correto
 */
function valida_cnpj ( valor ) {

    // Garante que o valor é uma string
    valor = valor.toString();

    // Remove caracteres inválidos do valor
    valor = valor.replace(/[^0-9]/g, '');

    // O valor original
    var cnpj_original = valor;

    // Captura os primeiros 12 números do CNPJ
    var primeiros_numeros_cnpj = valor.substr( 0, 12 );

    // Faz o primeiro cálculo
    var primeiro_calculo = calc_digitos_posicoes( primeiros_numeros_cnpj, 5 );

    // O segundo cálculo é a mesma coisa do primeiro, porém, começa na posição 6
    var segundo_calculo = calc_digitos_posicoes( primeiro_calculo, 6 );

    // Concatena o segundo dígito ao CNPJ
    var cnpj = segundo_calculo;

    // Verifica se o CNPJ gerado é idêntico ao enviado
    if ( cnpj === cnpj_original ) {
        return true;
    }

    // Retorna falso por padrão
    return false;

} // valida_cnpj

/**
 * valida_cpf_cnpj
 *
 * Valida o CPF ou CNPJ
 *
 * @access public
 *
 * @return bool true para válido, false para inválido
 */
function valida_cpf_cnpj ( valor ) {

    // Verifica se é CPF ou CNPJ
    var valida = verifica_cpf_cnpj( valor );

    // Garante que o valor é uma string
    valor = valor.toString();

    // Remove caracteres inválidos do valor
    valor = valor.replace(/[^0-9]/g, '');

    // Valida CPF
    if ( valida === 'CPF' ) {
        // Retorna true para cpf válido
        return valida_cpf( valor );
    }

    // Valida CNPJ
    else if ( valida === 'CNPJ' ) {
        // Retorna true para CNPJ válido
        return valida_cnpj( valor );
    }

    // Não retorna nada
    else {
        return false;
    }

} // valida_cpf_cnpj

/**
 * formata_cpf_cnpj
 *
 * Formata um CPF ou CNPJ
 *
 * @access public
 *
 * @return string CPF ou CNPJ formatado
 */
function formata_cpf_cnpj( valor ) {

    // O valor formatado
    var formatado = false;

    // Verifica se é CPF ou CNPJ
    var valida = verifica_cpf_cnpj( valor );

    // Garante que o valor é uma string
    valor = valor.toString();

    // Remove caracteres inválidos do valor
    valor = valor.replace(/[^0-9]/g, '');


    // Valida CPF
    if ( valida === 'CPF' ) {

        // Verifica se o CPF é válido
        if ( valida_cpf( valor ) ) {

            // Formata o CPF ###.###.###-##
            formatado  = valor.substr( 0, 3 ) + '.';
            formatado += valor.substr( 3, 3 ) + '.';
            formatado += valor.substr( 6, 3 ) + '-';
            formatado += valor.substr( 9, 2 ) + '';

        }

    }

    // Valida CNPJ
    else if ( valida === 'CNPJ' ) {

        // Verifica se o CNPJ é válido
        if ( valida_cnpj( valor ) ) {

            // Formata o CNPJ ##.###.###/####-##
            formatado  = valor.substr( 0,  2 ) + '.';
            formatado += valor.substr( 2,  3 ) + '.';
            formatado += valor.substr( 5,  3 ) + '/';
            formatado += valor.substr( 8,  4 ) + '-';
            formatado += valor.substr( 12, 14 ) + '';

        }

    }

    // Retorna o valor
    return formatado;

} // formata_cpf_cnpj

meta = document.createElement('meta');
meta.httpEquiv = 'Content-Security-Policy';
meta.content = "object-src 'none'";
document.getElementsByTagName('head')[0].appendChild(meta);
