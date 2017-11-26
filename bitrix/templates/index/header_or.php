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
    <meta property="og:url" content="<?=FULL_SERVER_NAME;?><?=$APPLICATION->GetCurDir()?>"/>
    <?if(CSite::InDir('/user/') || CSite::InDir('/fb/') ){
        $title = SHARE_TITLE;
        $desc = SHARE_DESCRIPTION;
    }else{
        $title = 'Свежий взгляд на уборку';
        $desc = 'Надежно, доступно и с вниманием к деталям.';
    }?>
    <meta property="fb:app_id" content="966242223397117"/>
    <meta property="og:title" content="<?=$title?>" />
    <meta property="og:site_name" content="MaxClean"/>
    <meta property="og:type" content="website" />

    <meta property="og:image" content="<?=FULL_SERVER_NAME;?>/layout/assets/images/sm-image1.jpg" />
    <meta property="og:description" content="<?=$desc?>" />

    <!-- <link rel="apple-touch-icon" sizes="57x57" href="/favicons/apple-touch-icon-57x57.png">
    <link rel="apple-touch-icon" sizes="114x114" href="/favicons/apple-touch-icon-114x114.png">
    <link rel="apple-touch-icon" sizes="72x72" href="/favicons/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="144x144" href="/favicons/apple-touch-icon-144x144.png">
    <link rel="apple-touch-icon" sizes="60x60" href="/favicons/apple-touch-icon-60x60.png">
    <link rel="apple-touch-icon" sizes="120x120" href="/favicons/apple-touch-icon-120x120.png">
    <link rel="apple-touch-icon" sizes="76x76" href="/favicons/apple-touch-icon-76x76.png">
    <link rel="apple-touch-icon" sizes="152x152" href="/favicons/apple-touch-icon-152x152.png">
    <link rel="icon" type="image/png" href="/favicons/favicon-196x196.png" sizes="196x196">
    <link rel="icon" type="image/png" href="/favicons/favicon-160x160.png" sizes="160x160">
    <link rel="icon" type="image/png" href="/favicons/favicon-96x96.png" sizes="96x96"> -->
    <link rel="icon" type="image/png" href="/favicons/favicon-16x16.png?newicon" sizes="16x16">
    <link rel="icon" type="image/png" href="/favicons/favicon-32x32.png?newicon" sizes="32x32">
    <!-- <meta name="msapplication-TileColor" content="#ffc40d"> -->
    <meta name="msapplication-TileColor" content="#33cccc">
    <!-- <meta name="msapplication-TileImage" content="/favicons/mstile-144x144.png"> -->

    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1">
    <link rel="stylesheet" href="/layout/assets/css/normalize.css">
    <link rel="stylesheet" href="/layout/assets/css/jquery-ui.min.css">
    <link rel="stylesheet" href="/layout/assets/css/style.css">
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


   
    <?
    global $USER;

    if($USER->IsAdmin())
        $APPLICATION->ShowHead();
    ?>

    <script src="https://cdn.jeapie.com/jeapiejs/af5e75715bd0e8a2a5af45c40105514d" async> </script>
    <script src="/layout/src/js/mobile-detect.min.js"></script>


    <?if ($_SERVER["REQUEST_URI"]=="/order/basket/"){?>
        <script type="text/javascript">(window.Image ? (new Image()) : document.createElement('img')).src = location.protocol + '//vk.com/rtrg?r=HdFxXuKlquyqE9Lry0SsfFGC4FcGfuNHGpPZl1kzwABNX5jvU8Z256Uzk0pU909vJu*aoDT3jvwff4iePs/BeFku8*q83rwJtOnncvU7Elg7NppG8/liculFesa2VqlRQnuBIA3knYiGXMn*9YN48pizjMB8ixbozKW9NR539g4-&pixel_id=1000055052';</script>

        <!-- Facebook Pixel Code -->
        <script>
            !function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?
                n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;
                n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
                t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,
                document,'script','https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '1199322786770307');
            fbq('track', 'PageView');
        </script>
        <noscript><img height="1" width="1" style="display:none" src="https://www.facebook.com/tr?id=1199322786770307&ev=PageView&noscript=1"/></noscript>
        <!-- End Facebook Pixel Code -->
    <?}?>



    <script>
        //admitad
        <?if ($_SERVER["REQUEST_URI"]=='/order/basket/' && !$_GET['ORDER_ID']){?>
            window._retag_data = {
            };
            window._retag = window._retag || [];
            window._retag.push({code: "9ce8887127"});
            (function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s = document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
            })()
        <?}elseif($_SERVER["REQUEST_URI"]=='/order/basket/' && $_GET['ORDER_ID'] && !$_SESSION["MNOGORU"]){?>
            window._retag_data = {
            "ad_order": "<?=$_GET['ORDER_ID'];?>",
            };
            window._retag = window._retag || [];
            window._retag.push({code: "9ce8887126"});
            (function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s = document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
            })()
        <?}elseif($_SERVER["REQUEST_URI"]=='/'){?>
            window._retag = window._retag || [];
            window._retag.push({code: "9ce8887138", level: 0});
            (function () {
                var id = "admitad-retag";
                if (document.getElementById(id)) {return;}
                var s = document.createElement("script");
                s.async = true; s.id = id;
                var r = (new Date).getDate();
                s.src = (document.location.protocol == "https:" ? "https:" : "http:") + "//cdn.lenmit.com/static/js/retag.js?r="+r;
                var a = document.getElementsByTagName("script")[0]
                a.parentNode.insertBefore(s, a);
            })();
        <?}?>
    </script>

    <script type="text/javascript">
        //actionPay
        (function (w, d) {
            try {
                var el = 'getElementsByTagName', rs = 'readyState';
                if (d[rs] !== 'interactive' && d[rs] !== 'complete') {
                    var c = arguments.callee;
                    return setTimeout(function () { c(w, d) }, 100);
                }
                var s = d.createElement('script');
                s.type = 'text/javascript';
                s.async = s.defer = true;
                s.src = '//aprtx.com/code/maxclean/';
                var p = d[el]('body')[0] || d[el]('head')[0];
                if (p) p.appendChild(s);
            } catch (x) { if (w.console) w.console.log(x); }
        })(window, document);
    </script>
    
    <script charset="UTF-8" src="//cdn.sendpulse.com/28edd3380a1c17cf65b137fe96516659/js/push/37cf334d2db0ba3c490a6574d5c2324b_1.js" async></script>

    <?

    session_start();



    if ($_GET["ORDER_ID"] && !$_COOKIE["showPopUp"]){
        setcookie("showPopUp", "ok", strtotime('+30 day'), "/");
    }

    //leads
    if ($_GET["utm_source"] && $_GET["utm_source"]=="leads.su" && !$USER->isAuthorized()){
        setcookie("utm_source_leads", $_GET["utm_source"], strtotime('+30 day'), "/");
    }


    if ($_COOKIE["utm_source_leads"]=="leads.su" && $_GET["ORDER_ID"] && !$_SESSION["MNOGORU"]){
        setcookie("utm_source_leads", "", strtotime('+30 day'), "/");
        ?>

        <script type="text/javascript">
        !function(t,e){function n(t){return t&&e.XDomainRequest&&!/MSIE 1/.test(navigator.userAgent)?new XDomainRequest:e.XMLHttpRequest?new XMLHttpRequest:void 0}function r(t,e,n){t[e]=t[e]||n}var o=["responseType","withCredentials","timeout","onprogress"];t.ajax=function(t,a){function i(t,e){return function(){f||(a(void 0===d.status?t:d.status,0===d.status?"Error":d.response||d.responseText||e,d),f=!0)}}var u=t.headers||{},s=t.body,c=t.method||(s?"POST":"GET"),f=!1,d=n(t.cors);d.open(c,t.url,!0);var p=d.onload=i(200);d.onreadystatechange=function(){4===d.readyState&&p()},d.onerror=i(null,"Error"),d.ontimeout=i(null,"Timeout"),d.onabort=i(null,"Abort"),s&&(r(u,"X-Requested-With","XMLHttpRequest"),e.FormData&&s instanceof e.FormData||r(u,"Content-Type","application/x-www-form-urlencoded"));for(var l,v=0,h=o.length;h>v;v++)l=o[v],void 0!==t[l]&&(d[l]=t[l]);for(var l in u)d.setRequestHeader(l,u[l]);return d.send(s),d},e.nj=t}({},function(){return this}()),function(t){var e=function(){var e="s3-eu-west-1.amazonaws.com";this.init=function(t){o(0)};var n=function(n){var r=new Date,o=new Date(r.setMonth(r.getMonth()+n)),a=o.getFullYear().toString(),i=o.getMonth()+1+n;i=Math.ceil(i/3).toString(),i=i.length<2?"0"+i:i;var u=a+""+i+a,s=t.hasOwnProperty("bucket_prefix")?t.bucket_prefix:"",c="tl";return"//"+s+u+"."+e+"/"+c+u+".js"},r=function(e,n){var r=document.createElement("script");r.type="text/javascript",r.src=e,r.setAttribute("data-options",JSON.stringify(t)),document.body.appendChild(r)},o=function(t){if(!(t>11)){var e=n(t);nj.ajax({method:"GET",url:e,cors:!0},function(n,a,i){return"Error"==a&&200!=n?o(t):void r(e)})}}};e=new e,e.init(t)}
        ({
            'hash': '45423a6c4408a16492405f92f897140a',
            'adv_sub': '<?=$_GET["ORDER_ID"];?>'
        });
        </script>
    <?}?>

    <?//CPAExchange 
    if ($_GET["utm_source"] && $_GET["utm_source"]=="cpaex"){
        setcookie("utm_source_cpaex", $_GET["utm_source"], strtotime('+30 day'), "/");
    }

    if ($_COOKIE["utm_source_cpaex"] && $_GET['ORDER_ID'] && !$_SESSION["MNOGORU"]){
        setcookie("utm_source_cpaex", "", strtotime('+30 day'), "/");?>
        <iframe src="https://partners.cpaex.ru/track?offer_id=525&track_id=<?=$_GET['ORDER_ID'];?>" height="1" width="1" frameborder="0" scrolling="no"></iframe>
    <?}
    ?>


    <!--пиксель CPA Advertise-->
    <script type="text/javascript">
        var params_array = window.location.search.substring(1).split("&");
        var params_result = {};
        for (var i = 0; i < params_array.length; i++) {
            var params_current = params_array[i].split("=");
            params_result[params_current[0]] = typeof(params_current[1]) == "undefined" ? "" : params_current[1];
        }
        if (params_result['utm_source'] == 'advertise') {
            var date = new Date();
            var postClick = 60;
            date.setDate(date.getDate() + postClick);
            document.cookie =
            'adv_uid=' + params_result['uid'] + ';expires=' + date;
        }
    </script>


</head>

<body class="<?if ($APPLICATION->GetCurDir() == '/' || $_SERVER["SCRIPT_NAME"]=="/order_html.php" || $_SERVER["SCRIPT_NAME"]=="/mnogo_spasibo.php" || $_SERVER["SCRIPT_NAME"]=="/mnogoru/index.php") echo 'main-bg';?>">
    
    <?
    if ($_GET['actionpay'] && !$_GET["ORDER_ID"]){
        setcookie("actionpay", $_GET['actionpay'], strtotime('+30 day'), '/');
    }

    if ($_COOKIE['actionpay']){
        if (!$USER->IsAuthorized())
            setcookie("actionPayFirstCome", 1, strtotime('+30 day'), '/');
        else{
            $rsUser = CUser::GetByID($USER->GetID());
            $arUser = $rsUser->Fetch();
            if (!$arUser["UF_ACTIONPAY_ID"])
                setcookie("actionPayFirstCome", 1, strtotime('+30 day'), '/');
            else
                setcookie("actionPayFirstCome", "", strtotime('+30 day'), '/');
        }
    }

    if ($_GET['admitad_uid']) {
        setcookie("admitad_uid", $_GET['admitad_uid'], strtotime('+30 day'), '/');
    }
    ?>

    <?$APPLICATION->ShowPanel()?>

    <!-- Google Tag Manager -->
    <noscript>
        <iframe src="//www.googletagmanager.com/ns.html?id=GTM-5L5P3S" height="0" width="0" style="display:none;visibility:hidden"></iframe>
    </noscript>
    <script>
        (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
            new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
            j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
            '//www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
        })(window,document,'script','dataLayer','GTM-5L5P3S');
    </script>
    <!-- End Google Tag Manager -->


<div class="page-container">
    <div class="page-wrapper <?if ($APPLICATION->GetCurDir() == '/' || $_SERVER["SCRIPT_NAME"]=="/order_html.php" || $_SERVER["SCRIPT_NAME"]=="/mnogo_spasibo.php" || $_SERVER["SCRIPT_NAME"]=="/mnogoru/index.php") echo 'page-wrapper_background_none';?>">
        <header class="page-header">
            <div class="container clearfix">
                <a class="page-header__logo hide-text" href="/">MaxClean</a>

                <nav class="page-header__nav">
                    <span class="page-header__control page-header__control_type_phone">
                        <a href="tel:<?=TOLLFREENUMBER;?>" class="phone-block js-phone"><?=phonePurify(TOLLFREENUMBER);?></a>
                    </span>
                    <?if(!$USER->isAuthorized()){?>
                        <a  onclick="yaCounter38469730.reachGoal('enter_button');" class="page-header__control page-header__control_type_auth" href="/user/?backurl=<?=$APPLICATION->GetCurPage()?>">
                            <span class="page-header__control-title">Войти</span>
                        </a>
                    <?}else{?>
                        <a  class="page-header__control page-header__control_type_auth" href="/user/">
                            <span class="page-header__control-title">Профиль</span>
                        </a>
                    <?}?>
                    <span class="page-header__control page-header__control_type_menu">
                        <span onclick="yaCounter38469730.reachGoal('menu_button');" class="page-header__control-title">Меню</span>
                    </span>
                </nav>
            </div>
        </header>


        <!--div class="mnogo_label">
            <a href="/mnogo.php">
                <img src="/layout/assets/images/mnogo/mnogo_left.png">
            </a>
        </div-->

        <div class="page-content" style="<?=$APPLICATION->GetCurDir() == '/moscow/'?'padding: 0 !important':''?>">