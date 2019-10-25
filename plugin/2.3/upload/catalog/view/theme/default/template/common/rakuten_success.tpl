<?php echo $header ?>
<div id="common-success" class="container">
    <ul class="breadcrumb">
        <?php foreach ($breadcrumbs as $breadcrumb): ?>
            <li><a href="<?php echo $breadcrumb['href'] ?>"><?php echo $breadcrumb['text'] ?></a></li>
        <?php endforeach; ?>
    </ul>
    <div class="row"><?php echo $column_left ?>
        <?php if ($column_left && $column_right) { ?>
            <?php $class = 'col-sm-6'; ?>
        <?php } elseif ($column_left || $column_right) { ?>
            <?php $class = 'col-sm-9'; ?>
        <?php } else { ?>
            <?php $class = 'col-sm-12'; ?>
        <?php } ?>
        <div id="content" class="<?php echo $class ?>"><?php echo $content_top ?>
            <h1><?php echo $title_rakuten_message ?></h1>

            <?php if ($payment_method == 'billet') { ?>
                <?php echo $text_rakuten_message ?>
                <div class="buttons">
                    <!-- Visualizar boleto -->
                    <a class="modal-btn btn btn-primary col-md-2 col-sm-6 col-xs-12 margin" href="<?php echo $billet_url ?>" target="_blank"><i class="fa fa-barcode"></i> Visualizar Boleto</a>
                    <!--<button class="modal-btn btn btn-primary col-md-2 col-sm-6 col-xs-12 margin" id="botao" ><i class="fa fa-barcode"></i> Visualizar Boleto</button>-->

                    <!-- Modal -->
                    <div id="myModal" class="modal hide">
                        <span class="close" id="close">&times;</span>

                        <div class="modal-content">
                            <iframe src="<?php echo $billet_url; ?>" width="900" height="800"></iframe>
                        </div>
                    </div>
                    <!-- Copiar link para clipboard -->
                    <div><button class="btn btn-primary col-md-2 col-sm-6 col-xs-12 margin" onclick="copyTextToClipboard('<?php echo $billet_url; ?>')">Copiar Link</button></div>
                </div>
            <?php } else { ?>
                <?php echo $text_rakuten_message ?>
            <?php } ?>

            <div class="buttons">
                <div class="pull-right"><a href="<?php echo $continue ?>" class="btn btn-primary"><?php echo $button_continue ?></a></div>
            </div>
            <?php echo $content_bottom ?></div>
        <?php echo $column_right ?></div>
</div>
<?php echo $footer ?>

<script>
    function copyTextToClipboard(text) {
        var textArea = document.createElement("textarea");

        textArea.style.position = 'fixed';
        textArea.style.top = 0;
        textArea.style.left = 0;

        // Ensure it has a small width and height. Setting to 1px / 1em
        // doesn't work as this gives a negative w/h on some browsers.
        textArea.style.width = '2em';
        textArea.style.height = '2em';

        // We don't need padding, reducing the size if it does flash render.
        textArea.style.padding = 0;

        // Clean up any borders.
        textArea.style.border = 'none';
        textArea.style.outline = 'none';
        textArea.style.boxShadow = 'none';

        // Avoid flash of white box if rendered for any reason.
        textArea.style.background = 'transparent';


        textArea.value = text;

        document.body.appendChild(textArea);

        textArea.select();

        try {
            var successful = document.execCommand('copy');
            var msg = successful ? '*Link copiado com sucesso.' : '*Erro ao copiar link.';

        } catch (err) {
            console.log(msg);
        }

        document.body.removeChild(textArea);
    }
    window.addEventListener('load', function() {

        function modal() {
            show();
        }

        function show() {
            var modal = document.querySelector('.modal');

            modal.classList.remove('hide');
            modal.classList.add('show-modal');

        }

        function hide() {
            var modal = document.querySelector('.modal');

            modal.classList.remove('show-modal');
            modal.classList.add('hide');

        }

        var content = document.querySelector('.modal');
        var button = document.getElementById('botao');
        var close = document.getElementById('close');

        content.onclick = function() {
            hide();
        };

        button.onclick = function() {
            modal();
        };

        close.onclick = function() {
            hide();
        }

    });
    </script>

    <style>
        .margin {
            margin: 5px 10px;
        }
        .modal {
            visibility: hidden;
            position: fixed;
            z-index: 99;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
        }
        .modal-content {
            position: absolute;
            display: flex;
            justify-content: center;
            width: 80rem;
            height: 100%;
            -webkit-border-radius: 10px;
            -moz-border-radius: 10px;
            border-radius: 10px;
            left: 0;
            right: 0;
            margin: 0 auto;
            background: #fff;
            box-shadow: 2px 2px 20px #333;
        }
        .show-modal {
            visibility: visible;
            opacity: 1;
            z-index: 999;
            display: block;
            -webkit-transition: visibility 0s, opacity 0.1s linear;
            -moz-transition: visibility 0s, opacity 0.1s linear;
            -ms-transition: visibility 0s, opacity 0.1s linear;
            -o-transition: visibility 0s, opacity 0.1s linear;
            transition: visibility 0s, opacity 0.1s linear;
        }
        iframe {
            border: none;
            margin-left: 30px;
        }
        .close {
            position: relative;
            float: right;
            width: 3.5rem;
            line-height: 3.5rem;
            text-align: center;
            cursor: pointer;
            font-size: 40px;
            font-weight: bolder;
            color: #fff;
            z-index: 100;
        }
        .hide {
            visibility: hidden;
            opacity: 0;
            -webkit-transition: visibility 0s, opacity 0.1s linear;
            -moz-transition: visibility 0s, opacity 0.1s linear;
            -ms-transition: visibility 0s, opacity 0.1s linear;
            -o-transition: visibility 0s, opacity 0.1s linear;
            transition: visibility 0s, opacity 0.1s linear;
        }

</style>
