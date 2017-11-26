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
    <title><?$APPLICATION->ShowTitle()?> - GetTidy</title>
    <meta property="og:url" content="https://<?=$_SERVER['SERVER_NAME']?><?=$APPLICATION->GetCurDir()?>"/>
    <?if(CSite::InDir('/user/') || CSite::InDir('/fb/') ){
        $title = 'Получи бесплатные 30 минут эко-уборки!';
        $desc = 'Закажи в несколько кликов и получи бесплатные 30 минут уборки твоей квартиры';
    }else{
        $title = 'Свежий взгляд на уборку';
        $desc = 'Надежно, доступно и с вниманием к деталям.';
    }?>
    <meta property="og:title" content="<?=$title?>" />
    <meta property="og:site_name" content="GetTidy"/>
    <meta property="og:type" content="website" />

    <meta property="og:image" content="https://<?=$_SERVER['SERVER_NAME']?>/sm-image.png" />
    <meta property="og:description" content="<?=$desc?>" />

    <link rel="apple-touch-icon" sizes="57x57" href="/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/favicons/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" href="/favicons/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="/favicons/favicon-160x160.png" sizes="160x160">
    <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png" sizes="96x96">
    <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png" sizes="16x16">
    <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png" sizes="32x32">
    <meta name="msapplication-TileColor" content="#ffc40d">
    <meta name="msapplication-TileImage" content="/favicons/mstile-144x144.png">

    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    <link rel="stylesheet" href="/layout/assets/css/normalize.css">
    <link rel="stylesheet" href="/layout/assets/css/style.css">

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
    <?if ( CSite::InDir('/order/') && $_REQUEST['CurrentStep'] == 7 && isset($_REQUEST['ORDER_ID']) ){?>
        <!-- Facebook Conversion Code for Заказ уборки -->
        <script>(function() {
                var _fbq = window._fbq || (window._fbq = []);
                if (!_fbq.loaded) {
                    var fbds = document.createElement('script');
                    fbds.async = true;
                    fbds.src = '//connect.facebook.net/en_US/fbds.js';
                    var s = document.getElementsByTagName('script')[0];
                    s.parentNode.insertBefore(fbds, s);
                    _fbq.loaded = true;
                }
            })();
            window._fbq = window._fbq || [];
            window._fbq.push(['track', '6023679014764', {'value':'0.00','currency':'RUB'}]);
        </script>
        <noscript><img height="1" width="1" alt="" style="display:none" src="https://www.facebook.com/tr?ev=6023679014764&amp;cd[value]=0.00&amp;cd[currency]=RUB&amp;noscript=1" /></noscript>
    <?}?>
    <script src="https://cdn.jeapie.com/jeapiejs/af5e75715bd0e8a2a5af45c40105514d" async> </script>

</head>
<body class="<?=$APPLICATION->GetCurDir() == '/'?'main-bg':''?>">

<?$APPLICATION->ShowPanel()?>
<!-- page -->

<!-- Google Tag Manager -->
<noscript><iframe src="//www.googletagmanager.com/ns.html?id=GTM-5L5P3S"
                  height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
        new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
        j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
        '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
    })(window,document,'script','dataLayer','GTM-5L5P3S');</script>
<!-- End Google Tag Manager -->
<?// echo $_SESSION['NO_SMS_CONFIRM'];?>
<? echo $_SESSION['NO_SMS_CONFIRM_2'];?>
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
                        <a class="page-header__control page-header__control_type_auth" href="/user/"   onclick="yaCounter38469730.reachGoal('entertop'); return true;">
                            <span class="page-header__control-title">Профиль</span>
                        </a>
                    <?}?>
                    <span class="page-header__control page-header__control_type_menu"  onclick="yaCounter38469730.reachGoal('menutop'); return true;">
                            <span class="page-header__control-title">Меню</span>
                        </span>
                </nav>
            </div>
        </header>
        <div class="page-content" style="<?=$APPLICATION->GetCurDir() == '/moscow/'?'padding: 0 !important':''?>">


