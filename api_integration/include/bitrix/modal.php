<?
//require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
//$APPLICATION->SetTitle("");
?>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script>

<?php
/*
 * Используем загрузчик и минимизатор JS
 */
//require_once $_SERVER['DOCUMENT_ROOT'] . '/api_integration/protected/utils/PackageLoader.php';
//$loader = new PackageLoader();
//$loader->jsMap = array(
//    /*
//     * Для работы с АПИ используем путь относительно корня сайта,
//     * для других же будет использовать путь относительно темы
//     */
//    '<SITE_TEMPLATE_PATH>/js_taxi_api/taxi/enter_code.js',
//);
//echo $loader->createLoaderTags();
?>

<link type="text/css" rel="stylesheet" href="http://fonts.googleapis.com/css?family=PT+Sans:400,700|PT+Sans+Narrow:400,700&amp;subset=latin,cyrillic">
<link href="/bitrix/templates/taxi_yellow/css/style.css" type="text/css" rel="stylesheet">

<style type="text/css">
    body {
        background: #E4E4E4;
        margin: 0;
    }
    body font.notetext {
        color: #000000;
    }
</style>

<div style="display: block;" aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="form_call_me" id="myModal">
    <div class="modal-header">
        <button aria-hidden="true" data-dismiss="modal" class="close" type="button" onclick="$.colorbox.close();
                return false;">?</button>
        <h1 id="myModalLabel">
        </h1>
    </div>

    <div class="modal-body">
        <form enctype="multipart/form-data" method="post" action="/include/modal.php" name="iblock_add" class="form-horizontal">

            <div style="" class="control-group">
                <div id="modal_html"></div>
            </div>
            <input type="submit" value="1" name="iblock_submit1" class="btn rel call_me" style="display: none;">
            <input type="submit" value="2" name="iblock_submit2" class="btn rel call_me" style="display: none;">
            <input type="submit" value="3" name="iblock_submit2" class="btn rel call_me" style="display: none;">
        </form>
    </div>

    <script>
        $(document).ready(function() {
            //заполняем контент окна
            $('#myModalLabel').html(
                    parent.$('#modalColorbox').data('modal_label')
                    );
            $('#modal_html').html(
                    parent.$('#modalColorbox').data('modal_html')
                    );
            for (var i = 1; i <= 3; i++) {
                var buttonHtml = parent.$('#modalColorbox').data('modal_button_' + i.toString());
                if (buttonHtml) {
                    $('button[name=iblock_submit_' + i.toString() + ']').html(buttonHtml).show();
                }
            }
        });
        /**
         * Функция для овтета на события закрытия
         * @param {integer} varIndex
         * @returns {undefined}
         */
        function AfterClose(varIndex) {
            var f = parent.taxi.modal.callbacks[varIndex];
            parent.taxi.modal.onClose = function() {
                f();
                $(window).off('cbox_closed.modalColorbox');
                if (parent) {
                    parent.taxi.modal.onClose = function() {
                    };
                } else if (typeof (taxi) !== 'undefined') {
                    taxi.modal.onClose = function() {
                    };
                }
            };
            parent.taxi.ordering.unlock();
            $.colorbox.close();
        }
    </script>

    <div class="modal-footer">
        <button name="iblock_submit_1" type="button" onclick="AfterClose(1);"
                style="float:left; margin-top:20px" class="" style="display: none"></button>

        <button name="iblock_submit_2" type="button" onclick="AfterClose(2)"
                class="btn rel call_me" style="display: none"></button>

        <button name="iblock_submit_3" type="button" onclick="AfterClose(3)"
                class="btn rel call_me" style="display: none"></button>
    </div>

</div>