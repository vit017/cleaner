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
    <meta property="og:url" content="https://<?=$_SERVER['SERVER_NAME']?><?=$APPLICATION->GetCurDir()?>"/>



    <meta charset="utf-8"> <!--кодировка utf-8-->
    <meta name="viewport" content="width=device-width, height=device-height, initial-scale=1.0, user-scalable=no, maximum-scale=1.0"/> <!--Убираем возможность масштабировать-->
    <meta http-equiv="Cache-Control" content="no-cache"/> <!--Запрещаем кэшировать документ-->
    <meta http-equiv="cleartype" content="on"/> <!--Активируем технологию ClearType для сглаживания шрифтов-->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"/> <!--Просим IE переключиться в последний режим-->
    <meta http-equiv="msthemecompatible" content="no"/> <!--Просим IE оформлять все в классическом стиле без учета текущей темы операционки-->
    <meta name="format-detection" content="telephone=no"/> <!--Запрещаем распознавать и выделять номера телефонов-->
    <meta name="format-detection" content="address=no"/> <!--Запрещаем распознавать и выделять адреса-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/reset.css" charset="utf-8" /> <!--Подключаем сброс стилей-->

    <link rel="icon" href="<?=SITE_TEMPLATE_PATH?>/img/favicon.ico" type="image/x-icon"> <!--Подключаем favicon для IE-->
    <link rel="shortcut icon" href="<?=SITE_TEMPLATE_PATH?>/img/favicon.ico" type="image/x-icon"> <!--Подключаем favicon для остальных-->


    <!-----------------
    <!--ПОДКЛЮЧАЕМ ТАБЛИЦЫ СТИЛЕЙ-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/bootstrap.min.css" charset="utf-8" /> <!--Подключаем стили Bootstrap-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/bootstrap-select.min.css" charset="utf-8" /> <!--Подключаем стили Bootstrap select-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/slick.css" charset="utf-8" /> <!--Подключаем стили Slick-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/bootstrap-datetimepicker.min.css" charset="utf-8" /> <!--Подключаем стили DateTimePicker-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/fonts/rubik.css" charset="utf-8" /> <!--Подключаем шрифт Rubik-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/manual.css" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/changes.css" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <link rel="stylesheet" type="text/css" href="/layout/mcstyle/css/flipclock.css" charset="utf-8" /> <!--Подключаем основные редактируемые стили-->
    <!---->
    <!--ПОДКЛЮЧАЕМ СКРИПТЫ-->
    <script type="text/javascript" src="https://code.jquery.com/jquery-latest.min.js"></script><!--Подключаем последнюю JQuery библиотеку-->
    <script type="text/javascript" src="/layout/mcstyle/js/respond.min.js"></script> <!--Подключаем адаптивность и ниже хак для градиентов в IE-->
    <script type="text/javascript" src="/layout/mcstyle/js/bootstrap.min.js"></script> <!--Подключаем Bootstrap-->
    <script type="text/javascript" src="/layout/mcstyle/js/bootstrap-select.min.js"></script> <!--Подключаем Bootstrap select-->
    <script type="text/javascript" src="/layout/mcstyle/js/slick.min.js"></script> <!--Подключаем Slick-->
    <script type="text/javascript" src="/layout/mcstyle/js/moment.js"></script> <!--Подключаем Moment-->
    <script type="text/javascript" src="/layout/mcstyle/js/ru.js"></script> <!--Подключаем локализацию календаря-->
    <script type="text/javascript" src="/layout/mcstyle/js/bootstrap-datetimepicker.min.js"></script> <!--Подключаем DateTimePicker-->
    <script type="text/javascript" src="/layout/mcstyle/js/inputmask.js"></script> <!--Подключаем InputMask-->
    <script type="text/javascript" src="/layout/mcstyle/js/jquery.inputmask.js"></script> <!--Подключаем InputMask-->
    <script type="text/javascript" src="/layout/mcstyle/js/flipclock.min.js"></script> <!--Подключаем InputMask-->
<!--    <script type="text/javascript" src="/layout/mcstyle/js/plugins.min.js"></script> -->

    <!--[if gte IE 9]>
    <style type="text/css">.gradient {filter: none;}</style>
    <![endif]-->
    <!---->

    <!-----------------


    <!--[if lte IE 8]>
    <link rel="stylesheet" href="/layout/assets/css/ie/ie-lt-9.css">
    <![endif]-->
    <!--[if lt IE 8]>
    <link rel="stylesheet" href="/layout/assets/css/ie/ie-lt-8.css">
    <![endif]-->

    <!--[if lt IE 9]>
    <script src="/layout/assets/js/ie/html5.js"></script>
    <script src="/layout/assets/js/ie/es5-shim.min.js"></script>
    <script src="/layout/assets/js/ie/selectivizr-min.js"></script>
    <script src="/layout/assets/js/ie/respond.min.js"></script>
    <![endif]-->

    <script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = location.protocol + '//vk.com/rtrg?r=mNOMhZYcOJnkLj*DCBw4BnGECAasW5Sa6i9mlc3pZyLDUr/t4k4e6UDARy90BBwY2LUeBam8rb7G2BpDUgCXu7Ku9BjK3InKl8OlHfz7qM60*1mWR8T0ua7X*dv6F01RKwBok*YbDJ07/IQWNblkxF1B6fc9pBzprm7Nl3pQ490-';</script>

    <?if($USER->IsAdmin()):?>
        <?$APPLICATION->ShowHead();?>
    <?endif?>

</head>
<body>
<? if (isset($_SESSION['LAZYLINK'])) echo 'Y';?>
<!-- saved from url=(0014)about:internet -->
<noscript class="no_script_message">
    У вас отключен JavaScript. Сайт может отображаться некорректно!
</noscript>

<?$APPLICATION->ShowPanel()?>
<!-- page -->
<?// echo $_SESSION['NO_SMS_CONFIRM'];?>
<?// echo $_SESSION['NO_SMS_CONFIRM_2'];?>
<!--
<div class="page-container">
    <div class="page-wrapper <?if($APPLICATION->GetCurDir() == '/'){?>page-wrapper_background_none<?}?>">
       <header class="page-header">
            <div class="container clearfix">
                <a class="page-header__logo hide-text" href="/">Get Tidy</a>
                <?//$APPLICATION->IncludeComponent("altasib:altasib.geoip", "", array());?>
                <?//xmp($_SESSION["GEOIP"])?>
                <span class="page-header__city">
                      <span class="city-dropdown">
                        <span class="city-dropdown__title">Санкт-Петербург</span>
                        <span class="city-dropdown__content">
                          Сейчас мы&nbsp;работаем только в&nbsp;Санкт-Петербурге. <a href="/moscow/">Узнать&nbsp;о&nbsp;запуске</a> в&nbsp;Москве
                        </span>
                      </span>
                    </span>
                <nav class="page-header__nav">
                        <span class="page-header__control page-header__control_type_phone">
                          <a href="tel:<?=str_replace(array('(', ')', ' ', '-'),'',$_SESSION['PHONE'])?>" class="phone-block js-phone"><?=$_SESSION['PHONE']?></a>
                        </span>
                    <?if(!$USER->isAuthorized()){?>
                        <a class="page-header__control page-header__control_type_auth" href="/user/?backurl=<?=$APPLICATION->GetCurPage()?>">
                            <span class="page-header__control-title">Войти</span>
                        </a>
                    <?}else{?>
                        <a class="page-header__control page-header__control_type_auth" href="/user/">
                            <span class="page-header__control-title">Профиль</span>
                        </a>
                    <?}?>
                    <span class="page-header__control page-header__control_type_menu">
                            <span class="page-header__control-title">Меню</span>
                        </span>
                </nav>
            </div>
        </header> -->
      <!--  <div class="page-content" style="<?=$APPLICATION->GetCurDir() == '/moscow/'?'padding: 0 !important':''?>">-->


<header> <?// echo $_SESSION["TOWN"];?>
    <nav class="navbar navbar-default" id="fixed">
        <div class="container">
            <div class="row">
                <div class="col-lg-2 col-md-2 col-sm-2 col-xs-3">
                    <a class="logo" href="http://<?=$_SERVER['SERVER_NAME']?>">
                        <img class="hidden-xs" src="../../img/logo_hdr.png">
                        <img class="visible-xs" src="../../img/logo_hdr_mob.png">
                    </a>
                </div> 
                <div class="col-lg-5 col-md-4 col-sm-9 col-xs-7">
                    <b class="visible-sm visible-xs text-center"><a href="tel:88002228330">8-800-222-83-30</a></b>
                    <div class="select_cont">
                        <i class="icon locate"></i>
                        <?
                            if($_SESSION["TOWN"]=='') {
                                $_SESSION["TOWN"] = "spb";
                            }
                        ?>
                        <?
                            if($_POST["TOWN"]=="Москва"){$_SESSION["TOWN"]="msk";};
                            if($_POST["TOWN"]=="Санкт-Петербург"){$_SESSION["TOWN"]="spb";};
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
                </div>
                <div class="col-lg-5 col-md-6 hidden-sm hidden-xs">
                    <ul class="nav navbar-nav">
                        <li><i class="icon phone"></i><b><a href="tel:88002228330">8-800-222-83-30</a></b></li>
                        <li class="enter">

                                <? global $USER;
                                if ($USER->IsAuthorized()) {?>
                                <button type="button" onclick="yaCounter38469730.reachGoal('entertop'); location.href='/user/';">
                                <i class="icon log_in"></i>
                                   Профиль
                                </button>
                                <?} else {?>
                                <button type="button" onclick="yaCounter38469730.reachGoal('entertop'); location.href='/user/?backurl=/';">
                                <i class="icon log_in"></i>
                                  Войти
                                </button>
                                <?}?>
                            </li>
                        <li><button onclick="yaCounter38469730.reachGoal('menutop');" type="button" data-toggle="modal" data-target="#menu"><i class="icon menu"></i><span>Меню</span></button></li>
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
                    <a class="logo" href="/">
                        <img class="hidden-xs" src="../../img/logo_hdr.png">
                        <img class="visible-xs" src="../../img/logo_hdr_mob.png">
                    </a>
                </div>
                <div class="col-lg-5 col-md-4 col-sm-9 col-xs-7">
<!--                    <a href="/order/basket/" type="button" class="btn btn-default" id="get_clean">Оформить заказ</a>-->
                    <button type="button" onclick="document.location='/order/basket/';" class="btn btn-default" id="get_clean">Оформить заказ</button>
                </div>
                <div class="col-lg-5 col-md-6 hidden-sm hidden-xs">
                    <ul class="nav navbar-nav">
                        <li><i class="icon phone"></i><b><a href="tel:88002228330">8-800-222-83-30</a></b></li>
                        <li class="enter">
                            <? global $USER;
                            if ($USER->IsAuthorized()) {?>
                                <button type="button" onclick="location.href='/user/';">
                                    <i class="icon log_in"></i>
                                    Профиль
                                </button>
                            <?} else {?>
                                <button type="button" onclick="location.href='/user/?backurl=/';">
                                    <i class="icon log_in"></i>
                                    Войти
                                </button>
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
</header>

<section class="order">
    <div class="container">
       
  

