<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
if ( CSite::InDir('/cleaners') ){
    localRedirect('/');
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <?$APPLICATION->ShowMeta("keywords")?>
    <?$APPLICATION->ShowMeta("description")?>
    <title><?$APPLICATION->ShowTitle()?> - MaxClean</title>

    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, maximum-scale=1.0"/> <!--Убираем возможность масштабировать-->
    <meta http-equiv="Cache-Control" content="no-cache"/> <!--Запрещаем кэшировать документ-->
    <meta http-equiv="cleartype" content="on"/> <!--Активируем технологию ClearType для сглаживания шрифтов-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/> <!--Просим IE переключиться в последний режим-->
    <meta http-equiv="msthemecompatible" content="no"/> <!--Просим IE оформлять все в классическом стиле без учета текущей темы операционки-->
    <meta name="format-detection" content="telephone=no"/> <!--Запрещаем распознавать и выделять номера телефонов-->
    <meta name="format-detection" content="address=no"/> <!--Запрещаем распознавать и выделять адреса-->


    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/reset.css" charset="utf-8" /> <!--Подключаем сброс стилей-->

    <link rel="icon" href="<?=SITE_TEMPLATE_PATH?>/img/favicon.ico" type="image/x-icon"> <!--Подключаем favicon для IE-->
    <link rel="shortcut icon" href="<?=SITE_TEMPLATE_PATH?>/img/favicon.ico" type="image/x-icon"> <!--Подключаем favicon для остальных-->

    <!--ПОДКЛЮЧАЕМ ТАБЛИЦЫ СТИЛЕЙ-->
    <link rel="stylesheet" type="text/css" href="https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"> <!--Подключаем иконочные шрифты-->
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/bootstrap.min.css" charset="utf-8" /> <!--Подключаем стили Bootstrap-->
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/bootstrap-select.min.css" charset="utf-8" /> <!--Подключаем стили Bootstrap select-->
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/slick.css" charset="utf-8" /> <!--Подключаем стили Slick-->
    <!--<link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/slick-theme.css" charset="utf-8" /> Подключаем стили Slick-theme-->
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/fonts/rubik.css" charset="utf-8" /> <!--Подключаем шрифт Rubik-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/flipclock.css" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/manual.css?v112" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <link rel="stylesheet" type="text/css" href="<?=SITE_TEMPLATE_PATH?>/css/custom.css" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/changes.css" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <!---->

    <!--ПОДКЛЮЧАЕМ СКРИПТЫ-->
    <script type="text/javascript" src="https://code.jquery.com/jquery-latest.min.js"></script><!--Подключаем последнюю JQuery библиотеку-->
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/respond.min.js"></script> <!--Подключаем адаптивность и ниже хак для градиентов в IE-->
    <script type="text/javascript" src="/layout/mcstyle/js/plugins.min.js"></script>
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/bootstrap.min.js"></script> <!--Подключаем Bootstrap-->
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/bootstrap-select.min.js"></script> <!--Подключаем Bootstrap select-->
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/slick.min.js"></script> <!--Подключаем Slick-->
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/inputmask.js"></script> <!--Подключаем InputMask-->
    <script type="text/javascript" src="<?=SITE_TEMPLATE_PATH?>/js/jquery.inputmask.js"></script> <!--Подключаем InputMask-->
    <script type="text/javascript" src="/layout/mcstyle/js/flipclock.min.js"></script> <!--Подключаем InputMask-->
    <!--[if gte IE 9]>
    <style type="text/css">.gradient {filter: none;}</style>
    <![endif]-->
    <!---->

    <?if($USER->IsAdmin()):?>
        <?$APPLICATION->ShowHead();?>
    <?endif?>
</head>

<body>
<? if (isset($_SESSION['LAZYLINK'])) echo 'Y';?>
<? //$_SESSION['NO_SMS_CONFIRM_2'];?>
    <?$APPLICATION->ShowPanel();?>

    <noscript class="no_script_message">
        У вас отключен JavaScript. Сайт может отображаться некорректно!
    </noscript>

    <header>
        <?// echo $_SESSION["TOWN"];?>
        <nav class="navbar navbar-default" id="fixed">
            <div class="container">
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                        <a class="logo" href="/">
                            <img class="hidden-xs" src="<?=SITE_TEMPLATE_PATH?>/img/logo_hdr.png">
                            <img class="visible-xs" src="<?=SITE_TEMPLATE_PATH?>/img/logo_hdr_mob.png">
                        </a>
                    </div>
                    <div class="col-lg-5 col-md-4 col-sm-9 col-xs-7">
                        <b class="visible-sm visible-xs text-center">8-800-222-83-30</b>
                        <form action="/ajax/setTown.php" name="townform" method="post" id="townform">
                        <div class="select_cont">
                            <i class="icon locate"></i>
                            <?
                                if($_SESSION["TOWN"]=='') {
                                    $_SESSION["TOWN"] = "spb";
                                }
                            ?>
                            <?
                               // if($_POST["TOWN"]=="Москва"){$_SESSION["TOWN"]="msk";};
                              //  if($_POST["TOWN"]=="Санкт-Петербург"){$_SESSION["TOWN"]="spb";};
                            ?>
                                <select class="selectpicker" id="town" name="TOWN">
                                    <? if($_SESSION["TOWN"]=="msk"){?>
                                        <option>Москва</option>
                                        <option>Санкт-Петербург</option>

                                    <?} else {?>

                                        <option>Санкт-Петербург</option>
                                        <option>Москва</option>
                                    <?}?>
                                </select>
                        </div>
                        </form>
                    </div>
                    <div class="col-lg-5 col-md-6 hidden-sm hidden-xs">
                        <ul class="nav navbar-nav">
                            <li><i class="icon phone"></i><b>8-800-222-83-30</b></li>
                            <?if(!$USER->isAuthorized()){?>
                                <li class="enter"><span><i class="icon log_in"></i><a href="/user/?backurl=<?=$APPLICATION->GetCurPage()?>">Войти</a></span></li>
                            <?}else{?>
                                <li class="enter"><span><i class="icon log_in"></i><a href="/user/">Профиль</a></span></li>
                            <?}?>
                            <li><button type="button" data-toggle="modal" data-target="#menu"><i class="icon menu"></i><span>Меню</span></button></li>
                        </ul>
                    </div>
                    <div class="col-xs-1 col-sm-1 visible-sm visible-xs">
                        <button type="button" data-toggle="modal" data-target="#menu"><i class="icon menu"></i></button>
                    </div>
                </div>
            </div>
        </nav>
        <nav class="navbar navbar-default navbar-fixed-top" id="floating">
            <div class="container">
                <div class="row">
                    <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                        <a class="logo" href="/">
                            <img class="hidden-xs" src="<?=SITE_TEMPLATE_PATH?>/img/logo_hdr.png">
                            <img class="visible-xs" src="<?=SITE_TEMPLATE_PATH?>/img/logo_hdr_mob.png">
                        </a>
                    </div>
                    <div class="col-lg-5 col-md-4 col-sm-9 col-xs-7">
<!--                        <a href="/order/basket/" type="button" class="btn btn-default" id="get_clean">Оформить заказ</a>-->
                        <button type="button" onclick="document.location='/order/basket/';" class="btn btn-default" id="get_clean">Оформить заказ</button>
                    </div>
                    <div class="col-lg-5 col-md-6 hidden-sm hidden-xs">
                        <ul class="nav navbar-nav">
                            <li><i class="icon phone"></i><b>8-800-222-83-30</b></li>
                            <?if(!$USER->isAuthorized()){?>
                                <li class="enter"><span><i class="icon log_in"></i><a onclick="yaCounter38469730.reachGoal('entertop'); return true;" href="/user/?backurl=<?=$APPLICATION->GetCurPage()?>">Войти</a></span></li>
                            <?}else{?>
                                <li class="enter"><span><i class="icon log_in"></i><a onclick="yaCounter38469730.reachGoal('entertop'); return true;" href="/user/">Профиль</a></span></li>
                            <?}?>

                            <li><button type="button" data-toggle="modal" data-target="#menu" onclick="yaCounter38469730.reachGoal('menutop'); return true;"><i class="icon menu"></i><span>Меню</span></button></li>
                        </ul>
                    </div>
                    <div class="col-xs-1 col-sm-1 visible-sm visible-xs">
                        <button type="button" data-toggle="modal" data-target="#menu"><i class="icon menu"></i></button>
                    </div>
                </div>
            </div>
        </nav>
    </header>