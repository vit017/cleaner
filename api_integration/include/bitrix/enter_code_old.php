<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$APPLICATION->SetTitle("Введите код подтверждения");
?>
<!--<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script> -->

<?php
/*
 * Используем загрузчик и минимизатор JS
 */
require_once $_SERVER['DOCUMENT_ROOT'] . '/api_integration/utils/PackageLoader.php';
$loader = new PackageLoader();
$loader->jsMap = array(
    /*
     * Для работы с АПИ используем путь относительно корня сайта,
     * для других же будет использовать путь относительно темы
     */
    '/api_integration/assets/js/bitrix/taxi/enter_code.js',
);
echo $loader->createLoaderTags();
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
        <!--<button aria-hidden="true" data-dismiss="modal" class="close" type="button" onclick="smsCodeWindow.close();
                return false;">X</button>-->
        <h1 id="myModalLabel">Введите код из SMS</h1>
    </div>

    <div class="modal-body">
        <form enctype="multipart/form-data" method="post" action="/api_integration/include/bitrix/enter_code.php" name="iblock_add" class="form-horizontal">

            <div style="" class="control-group">
                <p>На указанный телефон вам в течение 5-20 секунд придет бесплатное SMS сообщение с номером.</p>
                <p>Вам нужно этот номер ввести в нижестоящее поле. Это сделано для сокращения ложных вызовов.</p>
                <p>Спасибо за понимание.</p>

                <label class="control-label">Код</label>
                <div class="controls">

                    <input type="text" value="" id="smsCode" name="code" class="zak_input">

                    <div id="errorResult" style="color: #770000">
                    </div>
                </div>
            </div>
            <input type="submit" value="Отправить" name="iblock_submit" class="btn rel call_me" style="display: none;">
        </form>
    </div>

    <div class="modal-footer">
        <button name="iblock_submit" type="button" class="btn rel call_me" style="">Отправить</button>
    </div>

</div>