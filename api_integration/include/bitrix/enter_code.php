<?
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");
$title = 'Введите код подтверждения';
$txtEnterCode = 'Введите код из SMS';
$txtSmsUserMessage = '<p>На указанный телефон вам в течение 5-20 секунд придет бесплатное SMS сообщение с номером.</p>
                          <p>Вам нужно этот номер ввести в нижестоящее поле. Это сделано для сокращения ложных вызовов.</p>
                          <p>Спасибо за понимание.</p>';
$txtSubmit = 'Отправить';
$txtCode = 'Код';
$APPLICATION->SetTitle($title);
?>
<script type="text/javascript" src="http://code.jquery.com/jquery-1.9.1.min.js"></script> 

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
<link href="/bitrix/templates/taxi_yellow/css/screen.css" type="text/css" rel="stylesheet">

<div style="display: block;" aria-hidden="false" aria-labelledby="myModalLabel" role="dialog" tabindex="-1" class="form_call_me popup" id="myModal">
    <div class="modal-header">
        <h1 id="myModalLabel"><?=$txtEnterCode?></h1>
    </div>
    <form enctype="multipart/form-data" method="post" action="/api_integration/include/bitrix/enter_code.php" name="iblock_add" class="form-horizontal" onsubmit="return false;">
    <div class="modal-body">
        

            <div style="" class="control-group">
                <?=$txtSmsUserMessage?>

                <label class="control-label"><?=$txtCode?></label>
                <div class="controls">

                    <input type="text" value="" id="smsCode" name="code" class="zak_input">

                    <div id="errorResult" style="color: #770000">
                    </div>
                </div>
            </div>
            
        
    </div>

    <div class="submit-field">
        <input type="submit" value="<?=$txtSubmit?>" name="iblock_submit" id="smsSubmit" class="button yellow">

    </div>
        </form>
</div>