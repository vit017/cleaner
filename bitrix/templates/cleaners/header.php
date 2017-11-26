<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
IncludeTemplateLangFile(__FILE__);
if ( !CSite::InDir('/cleaners') ){
    localRedirect('/cleaners');
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
        $title = 'Получи бесплатные 30 минут эко уборки!';
        $desc = 'Закажи в несколько кликов и получи бесплатные 30 минут уборки твоей квартиры';
    }else{
        $title = 'Закажи эко уборку квартиры в несколько кликов';
        $desc = 'Свежий взгляд на уборку. Надежно, доступно и с вниманием к деталям';
    }?>
    <meta property="og:title" content="<?=$title?>" />
    <meta property="og:site_name" content="GetTidy"/>
    <meta property="og:type" content="website" />

    <meta property="og:image" content="https://<?=$_SERVER['SERVER_NAME']?>/sm-image.png" />
    <meta property="og:description" content="<?=$desc?>" />

<!--    <link rel="apple-touch-icon" sizes="57x57" href="/favicons/apple-touch-icon-57x57.png">-->
<!--    <link rel="apple-touch-icon" sizes="114x114" href="/favicons/apple-touch-icon-114x114.png">-->
<!--    <link rel="apple-touch-icon" sizes="72x72" href="/favicons/apple-touch-icon-72x72.png">-->
<!--    <link rel="apple-touch-icon" sizes="144x144" href="/favicons/apple-touch-icon-144x144.png">-->
<!--    <link rel="apple-touch-icon" sizes="60x60" href="/favicons/apple-touch-icon-60x60.png">-->
<!--    <link rel="apple-touch-icon" sizes="120x120" href="/favicons/apple-touch-icon-120x120.png">-->
<!--    <link rel="apple-touch-icon" sizes="76x76" href="/favicons/apple-touch-icon-76x76.png">-->
<!--    <link rel="apple-touch-icon" sizes="152x152" href="/favicons/apple-touch-icon-152x152.png">-->
<!--    <link rel="icon" type="image/png" href="/favicons/favicon-196x196.png" sizes="196x196">-->
<!--    <link rel="icon" type="image/png" href="/favicons/favicon-160x160.png" sizes="160x160">-->
<!--    <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png" sizes="96x96">-->
    <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png?newicon" sizes="16x16">
    <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png?newicon" sizes="32x32">
    <meta name="msapplication-TileColor" content="#ffc40d">
    <meta name="msapplication-TileImage" content="/favicons/mstile-144x144.png">

    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    <link rel="stylesheet" href="/layout/assets/css/normalize.css">
    <link rel="stylesheet" href="/layout/assets/css/style.css">
<!--    <link rel="stylesheet" href="--><?//=SITE_TEMPLATE_PATH;?><!--/styles.css">-->
    <link rel="stylesheet" href="/layout/assets/css/changes.css">

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


<?
unset($phoneDef);
global $USER;
$rsUser = CUser::GetByID($USER->GetID());
$arUser = $rsUser->Fetch();
if ($arUser["PERSONAL_CITY"]==617)
    $phoneDef=phonePurify(MANAGER_PHONE);
else
    $phoneDef=phonePurify(MANAGER_PHONE_MSK);


?>


<div class="page-container">
    <div class="page-wrapper <?if($APPLICATION->GetCurDir() == '/'){?>page-wrapper_background_none<?}?>">
        <header class="page-header">
            <div class="container clearfix">
                <a class="page-header__logo hide-text" href="/">Get Tidy</a>
                <?//$APPLICATION->IncludeComponent("altasib:altasib.geoip", "", array());?>
                <?//xmp($_SESSION["GEOIP"])?>
                <span class="page-header__city">
                    <span class="city-name" style="">Санкт-Петербург</span>
                </span>
                <nav class="page-header__nav">
                        <span class="page-header__control page-header__control_type_phone">
                          <a href="tel:<?=str_replace(array('(', ')', ' ', '-'),'',$phoneDef)?>" class="phone-block js-phone"><?=$phoneDef?></a>
                        </span>
                    <?if(!$USER->isAuthorized()){?>
                        <a class="page-header__control page-header__control_type_auth" href="/user/?backurl=<?=$APPLICATION->GetCurPage()?>">
                            <span class="page-header__control-title">Войти</span>
                        </a>
                    <?}?>
                    <span class="page-header__control page-header__control_type_menu">
                            <span class="page-header__control-title">Меню</span>
                        </span>
                </nav>
            </div>
        </header>
        <div class="page-content" style="<?=$APPLICATION->GetCurDir() == '/moscow/'?'padding: 0 !important':''?>">